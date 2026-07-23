package com.academicpulse.desktop.controllers;

import com.academicpulse.desktop.Router;
import com.academicpulse.desktop.model.Conversation;
import javafx.application.Platform;
import javafx.collections.FXCollections;
import javafx.fxml.FXML;
import javafx.scene.control.Label;
import javafx.scene.control.ListCell;
import javafx.scene.control.ListView;

public class MessagesController {
    @FXML private ListView<Conversation> conversationsListView;
    @FXML private Label statusLabel;

    @FXML
    public void initialize() {
        conversationsListView.setCellFactory(list -> new ListCell<>() {
            @Override
            protected void updateItem(Conversation conversation, boolean empty) {
                super.updateItem(conversation, empty);
                if (empty || conversation == null) {
                    setText(null);
                    return;
                }
                String preview = conversation.lastMessage == null
                        ? "No messages yet"
                        : conversation.lastMessage.userName + ": " + conversation.lastMessage.body;
                setText(conversation.displayName + "  —  " + preview);
            }
        });

        loadConversations();
    }

    private void loadConversations() {
        statusLabel.setText("Loading conversations...");
        new Thread(() -> {
            try {
                var conversations = Router.api().getConversations();
                Platform.runLater(() -> {
                    conversationsListView.setItems(FXCollections.observableArrayList(conversations));
                    String base = conversations.isEmpty()
                            ? "No conversations yet — start a new message."
                            : conversations.size() + " conversation(s).";
                    statusLabel.setText(Router.api().isOffline() ? "Offline — showing saved data. " + base : base);
                });
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to load conversations: " + describe(e)));
            }
        }).start();
    }

    @FXML
    private void handleOpenConversation() {
        Conversation selected = conversationsListView.getSelectionModel().getSelectedItem();
        if (selected == null) {
            statusLabel.setText("Select a conversation first.");
            return;
        }
        try {
            ConversationDetailController controller = Router.navigate("/conversation-detail.fxml", "Academic Pulse - " + selected.displayName);
            controller.setConversationId(selected.id);
        } catch (Exception e) {
            statusLabel.setText("Failed to open conversation: " + describe(e));
        }
    }

    @FXML
    private void handleNewMessage() {
        try {
            Router.navigate("/new-message.fxml", "Academic Pulse - New Message");
        } catch (Exception e) {
            statusLabel.setText("Failed to open new message screen: " + describe(e));
        }
    }

    private String describe(Exception e) {
        String message = e.getMessage();
        return message == null || message.isBlank() ? e.getClass().getSimpleName() : message;
    }
}
