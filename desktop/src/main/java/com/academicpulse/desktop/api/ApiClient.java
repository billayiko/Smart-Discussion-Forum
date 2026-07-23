package com.academicpulse.desktop.api;

import com.academicpulse.desktop.cache.LocalCache;
import com.academicpulse.desktop.model.Answer;
import com.academicpulse.desktop.model.Conversation;
import com.academicpulse.desktop.model.Question;
import com.academicpulse.desktop.model.Topic;
import com.academicpulse.desktop.model.User;
import com.fasterxml.jackson.annotation.JsonIgnoreProperties;
import com.fasterxml.jackson.core.type.TypeReference;
import com.fasterxml.jackson.databind.DeserializationFeature;
import com.fasterxml.jackson.databind.JsonNode;
import com.fasterxml.jackson.databind.ObjectMapper;
import com.fasterxml.jackson.databind.PropertyNamingStrategies;

import java.io.IOException;
import java.net.URI;
import java.net.http.HttpClient;
import java.net.http.HttpRequest;
import java.net.http.HttpResponse;
import java.sql.SQLException;
import java.time.Duration;
import java.util.List;
import java.util.Map;

/**
 * Talks to the Laravel API (routes/api.php) over HTTP using Sanctum bearer
 * tokens. Point {@code baseUrl} at wherever `php artisan serve` (or the
 * deployed app) is running, with an /api suffix.
 *
 * <p>Read (GET) calls are backed by a {@link LocalCache}: a successful
 * response is cached, and a genuine connectivity failure (as opposed to a
 * real non-2xx server response) falls back to whatever was last cached, so
 * the app stays browsable offline. Write calls are not cached — composing
 * new content still requires connectivity.</p>
 */
public class ApiClient {
    private static final String DEFAULT_BASE_URL = "http://127.0.0.1:8000/api";

    private final String baseUrl;
    private final HttpClient httpClient;
    private final ObjectMapper mapper;
    private final LocalCache cache;
    private String token;
    private boolean offline;

    public ApiClient() {
        this(DEFAULT_BASE_URL);
    }

    public ApiClient(String baseUrl) {
        this.baseUrl = baseUrl;
        this.httpClient = HttpClient.newBuilder()
                .connectTimeout(Duration.ofSeconds(10))
                .build();
        this.mapper = new ObjectMapper()
                .setPropertyNamingStrategy(PropertyNamingStrategies.SNAKE_CASE)
                .configure(DeserializationFeature.FAIL_ON_UNKNOWN_PROPERTIES, false);
        this.cache = createCache();
    }

    private static LocalCache createCache() {
        try {
            return new LocalCache();
        } catch (SQLException e) {
            // Caching is a nice-to-have for offline browsing; if it can't be
            // set up (e.g. a locked/unwritable profile directory), the app
            // should keep working online-only rather than fail to start.
            return null;
        }
    }

    public boolean isLoggedIn() {
        return token != null;
    }

    /** Whether the most recent GET fell back to cached data due to a connectivity failure. */
    public boolean isOffline() {
        return offline;
    }

    public User login(String email, String password) throws ApiException, IOException, InterruptedException {
        Map<String, String> payload = Map.of("email", email, "password", password);
        ApiResponse response = send("POST", "/login", payload, false);
        requireSuccess(response);

        LoginResponse parsed = mapper.readValue(response.body(), LoginResponse.class);
        this.token = parsed.accessToken;
        return parsed.user;
    }

    public void logout() throws ApiException, IOException, InterruptedException {
        try {
            requireSuccess(send("POST", "/logout", null, true));
        } finally {
            this.token = null;
        }
    }

    public User me() throws ApiException, IOException, InterruptedException {
        ApiResponse response = send("GET", "/me", null, true);
        requireSuccess(response);
        return mapper.readValue(response.body(), User.class);
    }

    public List<Topic> getTopics() throws ApiException, InterruptedException {
        return fetchCached("topics", "/topics", new TypeReference<List<Topic>>() {});
    }

    public List<Question> getTopicQuestions(long topicId) throws ApiException, InterruptedException {
        return fetchCached("topic:" + topicId + ":questions", "/topics/" + topicId + "/questions", new TypeReference<List<Question>>() {});
    }

    public Question getQuestion(long questionId) throws ApiException, InterruptedException {
        return fetchCached("question:" + questionId, "/questions/" + questionId, Question.class);
    }

    public Question createQuestion(long topicId, String title, String body) throws ApiException, IOException, InterruptedException {
        Map<String, Object> payload = Map.of("title", title, "body", body, "course_topic_id", topicId);
        ApiResponse response = send("POST", "/questions", payload, true);
        requireSuccess(response);
        return mapper.readValue(response.body(), Question.class);
    }

    public Answer createAnswer(long questionId, String body) throws ApiException, IOException, InterruptedException {
        Map<String, String> payload = Map.of("body", body);
        ApiResponse response = send("POST", "/questions/" + questionId + "/answers", payload, true);
        requireSuccess(response);
        return mapper.readValue(response.body(), Answer.class);
    }

    public List<Conversation> getConversations() throws ApiException, InterruptedException {
        return fetchCached("conversations", "/conversations", new TypeReference<List<Conversation>>() {});
    }

    public Conversation getConversation(long conversationId) throws ApiException, InterruptedException {
        return fetchCached("conversation:" + conversationId, "/conversations/" + conversationId, Conversation.class);
    }

    public void sendMessage(long conversationId, String body) throws ApiException, IOException, InterruptedException {
        Map<String, String> payload = Map.of("body", body);
        requireSuccess(send("POST", "/conversations/" + conversationId + "/messages", payload, true));
    }

    public Conversation startConversation(long userId) throws ApiException, IOException, InterruptedException {
        Map<String, Object> payload = Map.of("user_id", userId);
        ApiResponse response = send("POST", "/conversations/start", payload, true);
        requireSuccess(response);
        return mapper.readValue(response.body(), Conversation.class);
    }

    public List<User> getContacts() throws ApiException, InterruptedException {
        return fetchCached("contacts", "/conversation-contacts", new TypeReference<List<User>>() {});
    }

    /**
     * Performs a GET, caching the raw response on success. On a genuine
     * connectivity failure (the request never got a response at all, as
     * opposed to {@link ApiException} which {@link #requireSuccess} throws
     * for a real non-2xx server response and which must keep propagating
     * normally), falls back to the last cached value for this key if one
     * exists.
     */
    private <T> T fetchCached(String cacheKey, String path, Class<T> type) throws ApiException, InterruptedException {
        try {
            ApiResponse response = send("GET", path, null, true);
            requireSuccess(response);
            offline = false;
            if (cache != null) {
                cache.put(cacheKey, response.body());
            }
            return mapper.readValue(response.body(), type);
        } catch (IOException networkFailure) {
            return readFromCacheOrThrow(cacheKey, type);
        }
    }

    private <T> T fetchCached(String cacheKey, String path, TypeReference<T> type) throws ApiException, InterruptedException {
        try {
            ApiResponse response = send("GET", path, null, true);
            requireSuccess(response);
            offline = false;
            if (cache != null) {
                cache.put(cacheKey, response.body());
            }
            return mapper.readValue(response.body(), type);
        } catch (IOException networkFailure) {
            return readFromCacheOrThrow(cacheKey, type);
        }
    }

    private <T> T readFromCacheOrThrow(String cacheKey, Class<T> type) throws ApiException {
        offline = true;
        String cached = cache == null ? null : cache.get(cacheKey).orElse(null);
        if (cached == null) {
            throw new ApiException("No internet connection and no saved data available yet.", 0);
        }
        try {
            return mapper.readValue(cached, type);
        } catch (IOException corrupted) {
            throw new ApiException("No internet connection and no saved data available yet.", 0);
        }
    }

    private <T> T readFromCacheOrThrow(String cacheKey, TypeReference<T> type) throws ApiException {
        offline = true;
        String cached = cache == null ? null : cache.get(cacheKey).orElse(null);
        if (cached == null) {
            throw new ApiException("No internet connection and no saved data available yet.", 0);
        }
        try {
            return mapper.readValue(cached, type);
        } catch (IOException corrupted) {
            throw new ApiException("No internet connection and no saved data available yet.", 0);
        }
    }

    private void requireSuccess(ApiResponse response) throws ApiException {
        if (response.status() >= 200 && response.status() < 300) {
            return;
        }
        throw new ApiException(extractErrorMessage(response.body()), response.status());
    }

    private String extractErrorMessage(String body) {
        try {
            JsonNode node = mapper.readTree(body);
            if (node.has("message")) {
                return node.get("message").asText();
            }
            if (node.has("errors")) {
                return node.get("errors").toString();
            }
        } catch (IOException ignored) {
            // fall through to raw body below
        }
        return body == null || body.isBlank() ? "Request failed" : body;
    }

    private ApiResponse send(String method, String path, Object payload, boolean authRequired)
            throws IOException, InterruptedException, ApiException {
        if (authRequired && token == null) {
            throw new ApiException("Not logged in", 401);
        }

        HttpRequest.Builder builder = HttpRequest.newBuilder()
                .uri(URI.create(baseUrl + path))
                .timeout(Duration.ofSeconds(15))
                .header("Accept", "application/json");

        if (payload != null) {
            String json = mapper.writeValueAsString(payload);
            builder.header("Content-Type", "application/json")
                    .method(method, HttpRequest.BodyPublishers.ofString(json));
        } else {
            builder.method(method, HttpRequest.BodyPublishers.noBody());
        }

        if (authRequired) {
            builder.header("Authorization", "Bearer " + token);
        }

        HttpResponse<String> response = httpClient.send(builder.build(), HttpResponse.BodyHandlers.ofString());
        return new ApiResponse(response.statusCode(), response.body());
    }

    private record ApiResponse(int status, String body) {
    }

    @JsonIgnoreProperties(ignoreUnknown = true)
    private static class LoginResponse {
        public String accessToken;
        public User user;
    }
}
