package com.academicpulse.desktop.controllers;

import com.academicpulse.desktop.Router;
import com.academicpulse.desktop.model.Conversation;
import com.academicpulse.desktop.model.User;
import javafx.application.Platform;
import javafx.collections.FXCollections;
import javafx.fxml.FXML;
import javafx.scene.control.Label;
import javafx.scene.control.ListCell;
import javafx.scene.control.ListView;

public class NewMessageController {
    @FXML private ListView<User> contactsListView;
    @FXML private Label statusLabel;

    @FXML
    public void initialize() {
        contactsListView.setCellFactory(list -> new ListCell<>() {
            @Override
            protected void updateItem(User user, boolean empty) {
                super.updateItem(user, empty);
                setText(empty || user == null ? null : user.name + " (" + user.roleLabel() + ")");
            }
        });

        loadContacts();
    }

    private void loadContacts() {
        statusLabel.setText("Loading contacts...");
        new Thread(() -> {
            try {
                var contacts = Router.api().getContacts();
                Platform.runLater(() -> {
                    contactsListView.setItems(FXCollections.observableArrayList(contacts));
                    statusLabel.setText(contacts.isEmpty() ? "No other users found." : "Select a contact to message.");
                });
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to load contacts: " + describe(e)));
            }
        }).start();
    }

    @FXML
    private void handleStartConversation() {
        User selected = contactsListView.getSelectionModel().getSelectedItem();
        if (selected == null) {
            statusLabel.setText("Select a contact first.");
            return;
        }

        statusLabel.setText("Starting conversation...");
        new Thread(() -> {
            try {
                Conversation conversation = Router.api().startConversation(selected.id);
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
