package com.academicpulse.desktop.controllers;

import com.academicpulse.desktop.Router;
import com.academicpulse.desktop.model.Conversation;
import com.academicpulse.desktop.util.ForumUi;
import javafx.application.Platform;
import javafx.fxml.FXML;
import javafx.geometry.Insets;
import javafx.geometry.Pos;
import javafx.scene.control.Label;
import javafx.scene.layout.HBox;
import javafx.scene.layout.Priority;
import javafx.scene.layout.VBox;

import java.util.List;

/** Conversation list — mirrors pages/dashboards/messages/_conversations.blade.php's "Your Conversations" panel. */
public class MessagesController {
    @FXML private Label statusLabel;
    @FXML private VBox conversationsBox;

    @FXML
    public void initialize() {
        loadConversations();
    }

    @FXML
    private void handleRefresh() {
        loadConversations();
    }

    private void loadConversations() {
        statusLabel.setText("Loading conversations...");
        new Thread(() -> {
            try {
                var conversations = Router.api().getConversations();
                Platform.runLater(() -> render(conversations));
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to load conversations: " + describe(e)));
            }
        }).start();
    }

    private void render(List<Conversation> conversations) {
        String base = conversations.isEmpty()
                ? "No conversations yet — start a new message."
                : conversations.size() + " conversation(s).";
        statusLabel.setText(Router.api().isOffline() ? "Offline — showing saved data. " + base : base);

        conversationsBox.getChildren().clear();
        for (Conversation conversation : conversations) {
            conversationsBox.getChildren().add(conversationRow(conversation));
        }
    }

    private HBox conversationRow(Conversation conversation) {
        Label name = new Label(conversation.displayName);
        name.setStyle("-fx-font-weight: bold; -fx-font-size: 13px;");

        String preview = conversation.lastMessage == null
                ? "No messages yet"
                : conversation.lastMessage.userName + ": " + conversation.lastMessage.body;
        Label previewLabel = new Label(preview);
        previewLabel.setStyle("-fx-text-fill: #71717a; -fx-font-size: 12px;");
        previewLabel.setWrapText(true);

        VBox textBox = new VBox(3, name, previewLabel);
        HBox.setHgrow(textBox, Priority.ALWAYS);

        HBox row = new HBox(12, ForumUi.avatar(ForumUi.initials(conversation.displayName)), textBox);
        row.setAlignment(Pos.CENTER_LEFT);
        row.setPadding(new Insets(12));
        row.getStyleClass().add("app-forum-card");
        row.setOnMouseClicked(e -> openConversation(conversation));

        return row;
    }

    @FXML
    private void handleNewMessage() {
        try {
            Router.navigate("/new-message.fxml", "Academic Pulse - New Message");
        } catch (Exception e) {
            statusLabel.setText("Failed to open new message screen: " + describe(e));
        }
    }

    private void openConversation(Conversation conversation) {
        try {
            ConversationDetailController controller = Router.navigate("/conversation-detail.fxml", "Academic Pulse - " + conversation.displayName);
            controller.setConversationId(conversation.id);
        } catch (Exception e) {
            statusLabel.setText("Failed to open conversation: " + describe(e));
        }
    }

    private String describe(Exception e) {
        String message = e.getMessage();
        return message == null || message.isBlank() ? e.getClass().getSimpleName() : message;
    }
}
