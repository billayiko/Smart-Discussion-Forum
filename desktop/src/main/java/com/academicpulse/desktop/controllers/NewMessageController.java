package com.academicpulse.desktop.controllers;

import com.academicpulse.desktop.Router;
import com.academicpulse.desktop.model.Conversation;
import com.academicpulse.desktop.model.User;
import com.academicpulse.desktop.util.ForumUi;
import javafx.application.Platform;
import javafx.fxml.FXML;
import javafx.geometry.Insets;
import javafx.geometry.Pos;
import javafx.scene.control.Button;
import javafx.scene.control.Label;
import javafx.scene.control.TextField;
import javafx.scene.layout.HBox;
import javafx.scene.layout.Priority;
import javafx.scene.layout.VBox;

import java.util.List;

/** Contact picker for starting a new conversation or group — mirrors the "New Message" panel in
 *  pages/dashboards/messages/_conversations.blade.php (search filters client-side; the desktop
 *  contacts endpoint returns the full list rather than a server-side search). */
public class NewMessageController {
    @FXML private Label statusLabel;
    @FXML private TextField searchField;
    @FXML private VBox contactsBox;

    private List<User> allContacts = List.of();

    @FXML
    public void initialize() {
        searchField.textProperty().addListener((obs, old, value) -> render(filtered(value)));
        loadContacts();
    }

    @FXML
    private void handleRefresh() {
        loadContacts();
    }

    private void loadContacts() {
        statusLabel.setText("Loading contacts...");
        new Thread(() -> {
            try {
                var contacts = Router.api().getContacts();
                Platform.runLater(() -> {
                    allContacts = contacts;
                    render(filtered(searchField.getText()));
                });
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to load contacts: " + describe(e)));
            }
        }).start();
    }

    private List<User> filtered(String search) {
        String term = search == null ? "" : search.trim().toLowerCase();
        if (term.isEmpty()) {
            return allContacts;
        }
        return allContacts.stream()
                .filter(u -> (u.name != null && u.name.toLowerCase().contains(term))
                        || (u.email != null && u.email.toLowerCase().contains(term)))
                .toList();
    }

    private void render(List<User> contacts) {
        statusLabel.setText(contacts.isEmpty() ? "No users found." : "Select a contact to message.");

        contactsBox.getChildren().clear();
        for (User contact : contacts) {
            contactsBox.getChildren().add(contactRow(contact));
        }
    }

    private HBox contactRow(User contact) {
        Label name = new Label(contact.name);
        name.setStyle("-fx-font-weight: bold; -fx-font-size: 13px;");
        Label role = new Label(contact.roleLabel());
        role.setStyle("-fx-text-fill: #71717a; -fx-font-size: 12px;");
        VBox textBox = new VBox(3, name, role);
        HBox.setHgrow(textBox, Priority.ALWAYS);

        Button messageButton = new Button("Message");
        messageButton.getStyleClass().add("app-btn-light");
        messageButton.setOnAction(e -> startConversation(contact));

        HBox row = new HBox(12, ForumUi.avatar(contact.initials()), textBox, messageButton);
        row.setAlignment(Pos.CENTER_LEFT);
        row.setPadding(new Insets(12));
        row.getStyleClass().add("app-topic-card");

        return row;
    }

    private void startConversation(User contact) {
        statusLabel.setText("Starting conversation...");
        new Thread(() -> {
            try {
                Conversation conversation = Router.api().startConversation(contact.id);
                Platform.runLater(() -> {
                    try {
                        ConversationDetailController controller = Router.navigate(
                                "/conversation-detail.fxml", "Academic Pulse - " + conversation.displayName);
                        controller.setConversationId(conversation.id);
                    } catch (Exception e) {
                        statusLabel.setText("Failed to open conversation: " + describe(e));
                    }
                });
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to start conversation: " + describe(e)));
            }
        }).start();
    }

    @FXML
    private void handleCreateGroup() {
        try {
            Router.navigate("/group-create.fxml", "Academic Pulse - Create Group");
        } catch (Exception e) {
            statusLabel.setText("Failed to open group creation: " + describe(e));
        }
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
