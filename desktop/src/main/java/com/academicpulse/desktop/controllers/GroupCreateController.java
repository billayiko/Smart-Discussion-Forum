package com.academicpulse.desktop.controllers;

import com.academicpulse.desktop.Router;
import com.academicpulse.desktop.model.Conversation;
import com.academicpulse.desktop.model.User;
import javafx.application.Platform;
import javafx.fxml.FXML;
import javafx.scene.control.Button;
import javafx.scene.control.CheckBox;
import javafx.scene.control.Label;
import javafx.scene.control.TextField;
import javafx.scene.layout.VBox;

import java.util.ArrayList;
import java.util.List;

/** Group creation — mirrors the "Create a group" details panel in pages/dashboards/messages/_conversations.blade.php. */
public class GroupCreateController {
    @FXML private Label errorLabel;
    @FXML private TextField nameField;
    @FXML private VBox membersBox;
    @FXML private Button createButton;

    private final List<CheckBox> memberCheckboxes = new ArrayList<>();

    @FXML
    public void initialize() {
        new Thread(() -> {
            try {
                List<User> contacts = Router.api().getContacts();
                Platform.runLater(() -> {
                    for (User contact : contacts) {
                        CheckBox checkbox = new CheckBox(contact.name + " (" + contact.roleLabel() + ")");
                        checkbox.setUserData(contact.id);
                        memberCheckboxes.add(checkbox);
                        membersBox.getChildren().add(checkbox);
                    }
                });
            } catch (Exception e) {
                Platform.runLater(() -> errorLabel.setText("Failed to load contacts: " + describe(e)));
            }
        }).start();
    }

    @FXML
    private void handleCreate() {
        String name = nameField.getText() == null ? "" : nameField.getText().trim();
        List<Long> memberIds = memberCheckboxes.stream()
                .filter(CheckBox::isSelected)
                .map(cb -> (Long) cb.getUserData())
                .toList();

        if (name.isEmpty()) {
            errorLabel.setText("Enter a group name.");
            return;
        }
        if (memberIds.isEmpty()) {
            errorLabel.setText("Select at least one member.");
            return;
        }

        createButton.setDisable(true);
        errorLabel.setText("");

        new Thread(() -> {
            try {
                Conversation conversation = Router.api().createGroup(name, memberIds);
                Platform.runLater(() -> {
                    try {
                        ConversationDetailController controller = Router.navigate(
                                "/conversation-detail.fxml", "Academic Pulse - " + conversation.displayName);
                        controller.setConversationId(conversation.id);
                    } catch (Exception e) {
                        errorLabel.setText("Group created, but failed to open it: " + describe(e));
                        createButton.setDisable(false);
                    }
                });
            } catch (Exception e) {
                Platform.runLater(() -> {
                    errorLabel.setText("Failed to create group: " + describe(e));
                    createButton.setDisable(false);
                });
            }
        }).start();
    }

    @FXML
    private void handleBack() {
        try {
            Router.navigate("/new-message.fxml", "Academic Pulse - New Message");
        } catch (Exception e) {
            errorLabel.setText("Failed to go back: " + describe(e));
        }
    }

    private String describe(Exception e) {
        String message = e.getMessage();
        return message == null || message.isBlank() ? e.getClass().getSimpleName() : message;
    }
}
