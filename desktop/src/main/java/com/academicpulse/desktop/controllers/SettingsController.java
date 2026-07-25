package com.academicpulse.desktop.controllers;

import com.academicpulse.desktop.Router;
import com.academicpulse.desktop.model.User;
import javafx.application.Platform;
import javafx.fxml.FXML;
import javafx.scene.control.Label;
import javafx.scene.control.PasswordField;
import javafx.scene.control.TextField;

/** Profile (name/email) and password settings, mirroring the web app's Settings page. */
public class SettingsController {
    @FXML private TextField nameField;
    @FXML private TextField emailField;
    @FXML private Label profileStatusLabel;

    @FXML private PasswordField currentPasswordField;
    @FXML private PasswordField newPasswordField;
    @FXML private PasswordField confirmPasswordField;
    @FXML private Label passwordStatusLabel;

    @FXML
    public void initialize() {
        User user = Router.currentUser();
        if (user != null) {
            nameField.setText(user.name);
            emailField.setText(user.email);
        }
    }

    @FXML
    private void handleSaveProfile() {
        String name = nameField.getText() == null ? "" : nameField.getText().trim();
        String email = emailField.getText() == null ? "" : emailField.getText().trim();

        if (name.isEmpty() || email.isEmpty()) {
            profileStatusLabel.setText("Name and email are both required.");
            return;
        }

        profileStatusLabel.setText("Saving...");
        new Thread(() -> {
            try {
                User updated = Router.api().updateProfile(name, email);
                Router.setCurrentUser(updated);
                Platform.runLater(() -> profileStatusLabel.setText("Profile updated."));
            } catch (Exception e) {
                Platform.runLater(() -> profileStatusLabel.setText("Failed to update profile: " + describe(e)));
            }
        }).start();
    }

    @FXML
    private void handleChangePassword() {
        String current = currentPasswordField.getText() == null ? "" : currentPasswordField.getText();
        String updated = newPasswordField.getText() == null ? "" : newPasswordField.getText();
        String confirm = confirmPasswordField.getText() == null ? "" : confirmPasswordField.getText();

        if (current.isEmpty() || updated.isEmpty() || confirm.isEmpty()) {
            passwordStatusLabel.setText("All three password fields are required.");
            return;
        }

        if (!updated.equals(confirm)) {
            passwordStatusLabel.setText("New password and confirmation don't match.");
            return;
        }

        passwordStatusLabel.setText("Changing password...");
        new Thread(() -> {
            try {
                Router.api().changePassword(current, updated, confirm);
                Platform.runLater(() -> {
                    passwordStatusLabel.setText("Password changed.");
                    currentPasswordField.clear();
                    newPasswordField.clear();
                    confirmPasswordField.clear();
                });
            } catch (Exception e) {
                Platform.runLater(() -> passwordStatusLabel.setText("Failed to change password: " + describe(e)));
            }
        }).start();
    }

    private String describe(Exception e) {
        String message = e.getMessage();
        return message == null || message.isBlank() ? e.getClass().getSimpleName() : message;
    }
}
