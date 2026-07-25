package com.academicpulse.desktop.controllers;

import com.academicpulse.desktop.Router;
import javafx.application.Platform;
import javafx.fxml.FXML;
import javafx.scene.control.Button;
import javafx.scene.control.Label;
import javafx.scene.control.PasswordField;

/** Mirrors resources/views/pages/auth/reset-password.blade.php (via SecurityQuestionPasswordController::update). */
public class ResetPasswordController {
    @FXML private PasswordField passwordField;
    @FXML private PasswordField confirmField;
    @FXML private Button resetButton;
    @FXML private Label errorLabel;

    private String resetToken;

    public void setResetToken(String resetToken) {
        this.resetToken = resetToken;
    }

    @FXML
    private void handleReset() {
        String password = passwordField.getText() == null ? "" : passwordField.getText();
        String confirm = confirmField.getText() == null ? "" : confirmField.getText();

        if (password.isEmpty() || confirm.isEmpty()) {
            errorLabel.setText("Enter and confirm your new password.");
            return;
        }
        if (!password.equals(confirm)) {
            errorLabel.setText("Password and confirmation don't match.");
            return;
        }

        resetButton.setDisable(true);
        errorLabel.setText("");

        new Thread(() -> {
            try {
                Router.api().resetPassword(resetToken, password, confirm);
                Platform.runLater(() -> {
                    try {
                        Router.navigate("/login.fxml", "Academic Pulse - Login");
                    } catch (Exception e) {
                        errorLabel.setText("Password reset, but failed to return to login: " + describe(e));
                        resetButton.setDisable(false);
                    }
                });
            } catch (Exception e) {
                Platform.runLater(() -> {
                    errorLabel.setText(describe(e));
                    resetButton.setDisable(false);
                });
            }
        }).start();
    }

    private String describe(Exception e) {
        String message = e.getMessage();
        return message == null || message.isBlank() ? e.getClass().getSimpleName() : message;
    }
}
