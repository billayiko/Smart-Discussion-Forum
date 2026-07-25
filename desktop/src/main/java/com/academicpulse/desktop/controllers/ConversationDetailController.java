package com.academicpulse.desktop.controllers;

import com.academicpulse.desktop.Router;
import com.academicpulse.desktop.model.ChatMessage;
import com.academicpulse.desktop.model.Conversation;
import com.academicpulse.desktop.util.ForumUi;
import com.academicpulse.desktop.util.RelativeTime;
import javafx.application.Platform;
import javafx.fxml.FXML;
import javafx.geometry.Pos;
import javafx.scene.control.Label;
import javafx.scene.control.TextArea;
import javafx.scene.layout.HBox;
import javafx.scene.layout.Priority;
import javafx.scene.layout.VBox;

import java.util.concurrent.Executors;
import java.util.concurrent.ScheduledExecutorService;
import java.util.concurrent.TimeUnit;

/** A single conversation's messages — mirrors pages/dashboards/messages/_thread.blade.php's message list. */
public class ConversationDetailController {
    private static final long POLL_INTERVAL_SECONDS = 5;

    @FXML private Label titleLabel;
    @FXML private Label statusLabel;
    @FXML private VBox messagesBox;
    @FXML private TextArea replyField;

    private long conversationId;
    private ScheduledExecutorService poller;

    public void setConversationId(long conversationId) {
        this.conversationId = conversationId;
        loadConversation();
        startPolling();
    }

    @FXML
    private void handleRefresh() {
        loadConversation();
    }

    /**
     * Polls for new messages every few seconds so the conversation updates
     * without the user having to leave and reopen it — this is also what
     * makes reconnect-after-offline work automatically: each tick simply
     * retries the same API call, and {@link com.academicpulse.desktop.api.ApiClient}
     * transparently falls back to (and later recovers from) its local cache.
     */
    private void startPolling() {
        stopPolling();
        poller = Executors.newSingleThreadScheduledExecutor(runnable -> {
            Thread thread = new Thread(runnable, "conversation-poll");
            thread.setDaemon(true);
            return thread;
        });
        poller.scheduleWithFixedDelay(this::pollConversation, POLL_INTERVAL_SECONDS, POLL_INTERVAL_SECONDS, TimeUnit.SECONDS);
    }

    /**
     * {@code Router.navigate()} swaps the FXML root with no lifecycle
     * callback, so a controller that started a background poller must stop
     * it itself before navigating away — otherwise it keeps polling forever
     * from an orphaned instance.
     */
    private void stopPolling() {
        if (poller != null) {
            poller.shutdownNow();
            poller = null;
        }
    }

    private void loadConversation() {
        statusLabel.setText("Loading...");
        new Thread(() -> {
            try {
                Conversation conversation = Router.api().getConversation(conversationId);
                Platform.runLater(() -> applyConversation(conversation));
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to load conversation: " + describe(e)));
            }
        }).start();
    }

    private void pollConversation() {
        try {
            Conversation conversation = Router.api().getConversation(conversationId);
            Platform.runLater(() -> applyConversation(conversation));
        } catch (Exception e) {
            Platform.runLater(() -> statusLabel.setText("Failed to refresh: " + describe(e)));
        }
    }

    private void applyConversation(Conversation conversation) {
        titleLabel.setText(conversation.displayName);

        int count = conversation.messages.size();
        String base = count == 0 ? "No messages yet." : count + " message" + (count == 1 ? "" : "s") + ".";
        statusLabel.setText(Router.api().isOffline() ? "Offline — showing saved data. " + base : base);

        long currentUserId = Router.currentUser() == null ? -1 : Router.currentUser().id;

        messagesBox.getChildren().clear();
        for (ChatMessage message : conversation.messages) {
            messagesBox.getChildren().add(messageCard(message, currentUserId));
        }
    }

    private VBox messageCard(ChatMessage message, long currentUserId) {
        boolean isOwn = message.user != null && message.user.id == currentUserId;
        String name = isOwn ? "You" : message.user == null ? "unknown" : message.user.name;
        String initials = message.user == null ? "?" : message.user.initials();

        Label nameLabel = new Label(name);
        nameLabel.setStyle("-fx-font-weight: bold;");
        Label timeLabel = new Label(RelativeTime.ago(message.createdAt));
        timeLabel.setStyle("-fx-text-fill: #71717a; -fx-font-size: 11px; -fx-font-weight: bold;");
        HBox head = new HBox(8, nameLabel, timeLabel);
        head.setAlignment(Pos.CENTER_LEFT);

        Label bodyLabel = new Label(message.body == null ? "" : message.body);
        bodyLabel.setWrapText(true);
        bodyLabel.setStyle("-fx-text-fill: #2d2a3d;");

        VBox textBox = new VBox(4, head, bodyLabel);
        HBox.setHgrow(textBox, Priority.ALWAYS);

        HBox row = new HBox(12, ForumUi.avatar(initials), textBox);
        row.setAlignment(Pos.TOP_LEFT);

        VBox card = new VBox(row);
        card.getStyleClass().add("app-topic-card");
        return card;
    }

    @FXML
    private void handleSend() {
        String body = replyField.getText() == null ? "" : replyField.getText().trim();
        if (body.isEmpty()) {
            statusLabel.setText("Write a message first.");
            return;
        }

        statusLabel.setText("Sending...");
        new Thread(() -> {
            try {
                Router.api().sendMessage(conversationId, body);
                Platform.runLater(() -> {
                    replyField.clear();
                    loadConversation();
                });
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to send message: " + describe(e)));
            }
        }).start();
    }

    @FXML
    private void handleBack() {
        stopPolling();
        try {
            Router.navigate("/messages.fxml", "Academic Pulse - Messages");
        } catch (Exception e) {
            statusLabel.setText("Failed to go back: " + describe(e));
        }
    }

    private String describe(Exception e) {
        String message = e.getMessage();
        return message == null || message.isBlank() ? e.getClass().getSimpleName() : message;
    }
}
