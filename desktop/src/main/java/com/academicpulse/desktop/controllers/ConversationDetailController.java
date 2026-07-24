package com.academicpulse.desktop.controllers;

import com.academicpulse.desktop.Router;
import com.academicpulse.desktop.model.ChatMessage;
import com.academicpulse.desktop.model.Conversation;
import javafx.application.Platform;
import javafx.collections.FXCollections;
import javafx.fxml.FXML;
import javafx.scene.control.Label;
import javafx.scene.control.ListCell;
import javafx.scene.control.ListView;
import javafx.scene.control.TextArea;

import java.util.concurrent.Executors;
import java.util.concurrent.ScheduledExecutorService;
import java.util.concurrent.TimeUnit;

public class ConversationDetailController {
    private static final long POLL_INTERVAL_SECONDS = 5;

    @FXML private Label titleLabel;
    @FXML private Label statusLabel;
    @FXML private ListView<ChatMessage> messagesListView;
    @FXML private TextArea replyField;

    private long conversationId;
    private ScheduledExecutorService poller;

    @FXML
    public void initialize() {
        messagesListView.setCellFactory(list -> new ListCell<>() {
            @Override
            protected void updateItem(ChatMessage message, boolean empty) {
                super.updateItem(message, empty);
                if (empty || message == null) {
                    setText(null);
                    return;
                }
                String author = message.user == null ? "unknown" : message.user.name;
                setText(author + ": " + message.body);
                setWrapText(true);
            }
        });
    }

    public void setConversationId(long conversationId) {
        this.conversationId = conversationId;
        loadConversation();
        startPolling();
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
        messagesListView.setItems(FXCollections.observableArrayList(conversation.messages));
        int count = conversation.messages.size();
        String base = count == 0 ? "No messages yet." : count + " message" + (count == 1 ? "" : "s") + ".";
        statusLabel.setText(Router.api().isOffline() ? "Offline — showing saved data. " + base : base);
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
