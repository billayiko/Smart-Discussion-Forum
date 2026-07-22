package com.academicpulse.desktop.controllers;

import com.academicpulse.desktop.Router;
import javafx.application.Platform;
import javafx.fxml.FXML;
import javafx.scene.control.Button;
import javafx.scene.control.Label;
import javafx.scene.control.PasswordField;
import javafx.scene.control.TextField;

public class LoginController {
    @FXML private TextField emailField;
    @FXML private PasswordField passwordField;
    @FXML private Button loginButton;
    @FXML private Label errorLabel;

    @FXML
    private void handleLogin() {
        String email = emailField.getText() == null ? "" : emailField.getText().trim();
        String password = passwordField.getText() == null ? "" : passwordField.getText();

        if (email.isEmpty() || password.isEmpty()) {
            errorLabel.setText("Enter both email and password.");
            return;
        }

        loginButton.setDisable(true);
        errorLabel.setText("");

        new Thread(() -> {
            try {
                var user = Router.api().login(email, password);
                Router.setCurrentUser(user);
                Platform.runLater(this::goToTopics);
            } catch (Exception e) {
                Platform.runLater(() -> showError(describe(e)));
            }
        }).start();
    }

    private void goToTopics() {
        try {
            Router.navigate("/topics.fxml", "Academic Pulse - Discussion Forum");
        } catch (Exception e) {
            showError("Failed to load the next screen: " + describe(e));
        }
    }

    private void showError(String message) {
        loginButton.setDisable(false);
        errorLabel.setText(message);
    }

    private String describe(Exception e) {
        String message = e.getMessage();
        return message == null || message.isBlank() ? e.getClass().getSimpleName() : message;
    }
}
