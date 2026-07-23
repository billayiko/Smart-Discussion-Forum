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

public class ConversationDetailController {
    @FXML private Label titleLabel;
    @FXML private Label statusLabel;
    @FXML private ListView<ChatMessage> messagesListView;
    @FXML private TextArea replyField;

    private long conversationId;

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
    }

    private void loadConversation() {
        statusLabel.setText("Loading...");
        new Thread(() -> {
            try {
                Conversation conversation = Router.api().getConversation(conversationId);
                Platform.runLater(() -> {
                    titleLabel.setText(conversation.displayName);
                    messagesListView.setItems(FXCollections.observableArrayList(conversation.messages));
                    int count = conversation.messages.size();
                    statusLabel.setText(count == 0 ? "No messages yet." : count + " message" + (count == 1 ? "" : "s") + ".");
                });
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to load conversation: " + describe(e)));
            }
        }).start();
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
