package com.academicpulse.desktop.api;

import com.fasterxml.jackson.databind.JsonNode;
import com.fasterxml.jackson.databind.ObjectMapper;

import java.io.IOException;
import java.net.URI;
import java.net.http.HttpClient;
import java.net.http.HttpRequest;
import java.net.http.HttpResponse;
import java.net.http.WebSocket;
import java.time.Duration;
import java.util.Map;
import java.util.concurrent.CompletionStage;
import java.util.concurrent.Executors;
import java.util.concurrent.ScheduledExecutorService;
import java.util.concurrent.ScheduledFuture;
import java.util.concurrent.TimeUnit;
import java.util.function.Consumer;
import java.util.function.Supplier;

/**
 * Speaks the Pusher/Reverb WebSocket wire protocol directly — no external
 * library, since {@link HttpClient#newWebSocketBuilder()} already covers it
 * — so a chat message arrives the instant it's sent instead of waiting for
 * the next 5s poll. Every existing poll/offline-cache path in
 * {@link ApiClient} and the message controllers is untouched: if this never
 * connects, or drops and can't reconnect, the app behaves exactly as it did
 * before this class existed.
 *
 * <p>Wire-protocol notes (verified against Laravel's broadcasting internals
 * during design, not just public docs):
 * <ul>
 *   <li>Server→client frames (the {@code connection_established} system
 *   frame and every application broadcast) send {@code data} as a
 *   JSON-encoded <em>string</em> — it needs a second decode pass, never
 *   treat it as a nested object.</li>
 *   <li>Client→server control frames ({@code pusher:subscribe},
 *   {@code pusher:pong}) send {@code data} as a real nested object instead —
 *   the encoding is asymmetric by direction.</li>
 *   <li>The chat message event's wire name is the fixed string
 *   {@code chat.message} — set via {@code broadcastAs()} on the server's
 *   {@code App\Events\ChatMessageSent}, which deliberately implements
 *   {@code ShouldBroadcastNow} rather than going through a Notification's
 *   'broadcast' channel (that path silently queues via
 *   {@code Illuminate\Broadcasting\BroadcastEvent} regardless of the
 *   notification's own queueing, which stranded every broadcast with no
 *   queue worker running during development).</li>
 * </ul>
 */
public class RealtimeClient {
    private static final String CHAT_MESSAGE_EVENT = "chat.message";
    private static final long RECONNECT_DELAY_SECONDS = 5;
    private static final long DEFAULT_ACTIVITY_TIMEOUT_SECONDS = 30;

    private final String wsHost;
    private final int wsPort;
    private final String appKey;
    private final String authUrl;
    private final HttpClient httpClient;
    private final ObjectMapper mapper = new ObjectMapper();
    private final Supplier<String> tokenSupplier;
    private final Supplier<Long> currentUserIdSupplier;
    private final Consumer<JsonNode> onChatMessage;

    private ScheduledExecutorService scheduler;
    private volatile WebSocket webSocket;
    private volatile String socketId;
    private volatile long activityTimeoutSeconds = DEFAULT_ACTIVITY_TIMEOUT_SECONDS;
    private volatile boolean stopped = true;
    private ScheduledFuture<?> idlePingTask;

    /**
     * @param apiBaseUrl same base URL as {@link ApiClient} (e.g.
     *                    "http://127.0.0.1:8000/api") — the WebSocket host
     *                    and the /broadcasting/auth URL are both derived
     *                    from it rather than configured a second time.
     * @param wsPort      Reverb's client-facing port (REVERB_PORT).
     * @param appKey      Reverb's REVERB_APP_KEY.
     */
    public RealtimeClient(String apiBaseUrl, int wsPort, String appKey,
                           Supplier<String> tokenSupplier, Supplier<Long> currentUserIdSupplier,
                           Consumer<JsonNode> onChatMessage) {
        this.wsHost = URI.create(apiBaseUrl).getHost();
        this.wsPort = wsPort;
        this.appKey = appKey;
        this.authUrl = apiBaseUrl + "/broadcasting/auth";
        this.tokenSupplier = tokenSupplier;
        this.currentUserIdSupplier = currentUserIdSupplier;
        this.onChatMessage = onChatMessage;
        this.httpClient = HttpClient.newBuilder().connectTimeout(Duration.ofSeconds(10)).build();
    }

    public synchronized void start() {
        if (!stopped) {
            return;
        }
        stopped = false;
        scheduler = Executors.newSingleThreadScheduledExecutor(r -> {
            Thread t = new Thread(r, "realtime-client");
            t.setDaemon(true);
            return t;
        });
        connect();
    }

    public synchronized void stop() {
        stopped = true;
        if (idlePingTask != null) {
            idlePingTask.cancel(true);
            idlePingTask = null;
        }
        if (webSocket != null) {
            webSocket.abort();
            webSocket = null;
        }
        if (scheduler != null) {
            scheduler.shutdownNow();
            scheduler = null;
        }
        socketId = null;
    }

    private void connect() {
        if (stopped || tokenSupplier.get() == null) {
            return;
        }

        String uri = "ws://" + wsHost + ":" + wsPort + "/app/" + appKey
                + "?protocol=7&client=academic-pulse-desktop&version=1.0";
        StringBuilder buffer = new StringBuilder();

        httpClient.newWebSocketBuilder()
                .buildAsync(URI.create(uri), new WebSocket.Listener() {
                    @Override
                    public void onOpen(WebSocket ws) {
                        ws.request(1);
                    }

                    @Override
                    public CompletionStage<?> onText(WebSocket ws, CharSequence data, boolean last) {
                        buffer.append(data);
                        ws.request(1);
                        if (last) {
                            String frame = buffer.toString();
                            buffer.setLength(0);
                            handleFrame(ws, frame);
                        }
                        return null;
                    }

                    @Override
                    public CompletionStage<?> onClose(WebSocket ws, int statusCode, String reason) {
                        scheduleReconnect();
                        return null;
                    }

                    @Override
                    public void onError(WebSocket ws, Throwable error) {
                        scheduleReconnect();
                    }
                })
                .whenComplete((ws, error) -> {
                    if (error != null) {
                        scheduleReconnect();
                        return;
                    }
                    webSocket = ws;
                });
    }

    private void handleFrame(WebSocket ws, String frame) {
        // Any traffic at all resets the idle timer — not just pings — since
        // the liveness contract is "no traffic for activity_timeout", not
        // "no pings specifically".
        resetIdlePing(ws);

        try {
            JsonNode root = mapper.readTree(frame);
            String event = root.path("event").asText("");

            switch (event) {
                case "pusher:connection_established" -> handleConnectionEstablished(ws, root);
                case "pusher:ping" -> sendControlFrame(ws, "pusher:pong");
                case CHAT_MESSAGE_EVENT -> handleChatMessage(root);
                default -> {
                    // pusher_internal:subscription_succeeded and anything
                    // else needs no action here.
                }
            }
        } catch (IOException malformed) {
            // A single malformed frame shouldn't take the connection down;
            // the next frame (or a reconnect) recovers on its own.
        }
    }

    private void handleConnectionEstablished(WebSocket ws, JsonNode root) throws IOException {
        JsonNode data = mapper.readTree(root.path("data").asText("{}"));
        socketId = data.path("socket_id").asText(null);
        if (data.hasNonNull("activity_timeout")) {
            activityTimeoutSeconds = data.get("activity_timeout").asLong(DEFAULT_ACTIVITY_TIMEOUT_SECONDS);
        }
        subscribe(ws);
    }

    private void subscribe(WebSocket ws) {
        Long userId = currentUserIdSupplier.get();
        String token = tokenSupplier.get();
        String socket = socketId;
        if (userId == null || token == null || socket == null) {
            return;
        }
        String channel = "private-App.Models.User." + userId;

        // Off the WebSocket listener's own thread: this makes a blocking
        // HTTP call, which must never happen inside a Listener callback.
        new Thread(() -> {
            try {
                String auth = authorize(channel, socket, token);
                if (auth == null) {
                    return;
                }
                sendSubscribe(ws, channel, auth);
            } catch (Exception ignored) {
                // the next reconnect attempt redoes the whole handshake
            }
        }, "realtime-auth").start();
    }

    /** POSTs to the Sanctum-guarded /api/broadcasting/auth route and returns the signed "auth" string for a private channel. */
    private String authorize(String channel, String socket, String token) throws IOException, InterruptedException {
        String body = mapper.writeValueAsString(Map.of("socket_id", socket, "channel_name", channel));

        HttpRequest request = HttpRequest.newBuilder()
                .uri(URI.create(authUrl))
                .timeout(Duration.ofSeconds(10))
                .header("Accept", "application/json")
                .header("Content-Type", "application/json")
                .header("Authorization", "Bearer " + token)
                .POST(HttpRequest.BodyPublishers.ofString(body))
                .build();

        HttpResponse<String> response = httpClient.send(request, HttpResponse.BodyHandlers.ofString());
        if (response.statusCode() < 200 || response.statusCode() >= 300) {
            return null;
        }
        return mapper.readTree(response.body()).path("auth").asText(null);
    }

    private void sendSubscribe(WebSocket ws, String channel, String auth) throws IOException {
        String frame = mapper.writeValueAsString(Map.of(
                "event", "pusher:subscribe",
                "data", Map.of("channel", channel, "auth", auth)
        ));
        ws.sendText(frame, true);
    }

    private void sendControlFrame(WebSocket ws, String event) {
        try {
            ws.sendText(mapper.writeValueAsString(Map.of("event", event, "data", Map.of())), true);
        } catch (IOException ignored) {
        }
    }

    private void handleChatMessage(JsonNode root) {
        try {
            JsonNode data = mapper.readTree(root.path("data").asText("{}"));
            onChatMessage.accept(data);
        } catch (IOException ignored) {
        }
    }

    /** Proactively pings when idle for activity_timeout, per the protocol's liveness contract — reset on every received frame. */
    private void resetIdlePing(WebSocket ws) {
        if (idlePingTask != null) {
            idlePingTask.cancel(false);
        }
        if (scheduler == null || scheduler.isShutdown()) {
            return;
        }
        idlePingTask = scheduler.schedule(() -> sendControlFrame(ws, "pusher:ping"), activityTimeoutSeconds, TimeUnit.SECONDS);
    }

    private void scheduleReconnect() {
        webSocket = null;
        socketId = null;
        if (stopped || scheduler == null || scheduler.isShutdown()) {
            return;
        }
        // A fresh socket means a fresh socket_id and auth signature — the
        // whole handshake (connect → auth → subscribe) redoes from scratch,
        // never reusing anything from the dropped connection.
        scheduler.schedule(this::connect, RECONNECT_DELAY_SECONDS, TimeUnit.SECONDS);
    }
}
