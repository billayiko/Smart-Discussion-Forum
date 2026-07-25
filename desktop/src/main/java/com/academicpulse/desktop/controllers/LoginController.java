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

    @FXML
    private void handleBack() {
        try {
            Router.navigate("/welcome.fxml", "Academic Pulse Forum");
        } catch (Exception e) {
            errorLabel.setText("Failed to go back: " + describe(e));
        }
    }

    @FXML
    private void handleForgotPassword() {
        try {
            Router.navigate("/forgot-password.fxml", "Academic Pulse - Forgot Password");
        } catch (Exception e) {
            errorLabel.setText("Failed to open: " + describe(e));
        }
    }

    private void goToTopics() {
        try {
            String role = Router.currentUser() != null ? Router.currentUser().role : null;
            if ("admin".equals(role)) {
                Router.navigate("/admin-dashboard.fxml", "Academic Pulse - Admin Dashboard");
            } else if ("lecturer".equals(role)) {
                Router.navigate("/lecturer-dashboard.fxml", "Academic Pulse - Lecturer Dashboard");
            } else if ("student".equals(role)) {
                Router.startLiveQuizWatch();
                Router.navigate("/student-dashboard.fxml", "Academic Pulse - Student Dashboard");
            } else if ("member".equals(role)) {
                Router.navigate("/onboarding.fxml", "Academic Pulse - Welcome");
            } else {
                Router.navigate("/topics.fxml", "Academic Pulse - Discussion Forum");
            }
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
