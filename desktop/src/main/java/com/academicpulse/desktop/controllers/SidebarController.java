package com.academicpulse.desktop.controllers;

import com.academicpulse.desktop.Router;
import com.academicpulse.desktop.model.User;
import javafx.application.Platform;
import javafx.fxml.FXML;
import javafx.scene.control.Label;

/** Controller for the shared left-hand navigation, included on every authenticated screen. */
public class SidebarController {
    @FXML private Label userNameLabel;
    @FXML private Label userRoleLabel;

    @FXML
    public void initialize() {
        User user = Router.currentUser();
        if (user != null) {
            userNameLabel.setText(user.name);
            userRoleLabel.setText(user.roleLabel());
        }
    }

    @FXML
    private void handleDiscussionForum() {
        try {
            Router.navigate("/topics.fxml", "Academic Pulse - Discussion Forum");
        } catch (Exception ignored) {
            // nothing sensible to show here; the target screen will report its own load errors
        }
    }

    @FXML
    private void handleMessages() {
        try {
            Router.navigate("/messages.fxml", "Academic Pulse - Messages");
        } catch (Exception ignored) {
            // nothing sensible to show here; the target screen will report its own load errors
        }
    }

    @FXML
    private void handleLogout() {
        new Thread(() -> {
            try {
                Router.api().logout();
            } catch (Exception ignored) {
                // token may already be invalid server-side; proceed to login regardless
            }
            Router.setCurrentUser(null);
            Platform.runLater(() -> {
                try {
                    Router.navigate("/login.fxml", "Academic Pulse - Login");
                } catch (Exception ignored) {
                }
            });
        }).start();
    }
}
