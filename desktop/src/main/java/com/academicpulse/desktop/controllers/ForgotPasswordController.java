package com.academicpulse.desktop.controllers;

import com.academicpulse.desktop.Router;
import javafx.application.Platform;
import javafx.fxml.FXML;
import javafx.scene.control.Button;
import javafx.scene.control.ComboBox;
import javafx.scene.control.Label;
import javafx.scene.control.TextField;
import javafx.util.StringConverter;

import java.util.LinkedHashMap;
import java.util.Map;

/** Mirrors resources/views/pages/auth/forgot-password.blade.php (via SecurityQuestionPasswordController::verify). */
public class ForgotPasswordController {
    private static final Map<String, String> SECURITY_QUESTIONS = new LinkedHashMap<>();
    static {
        SECURITY_QUESTIONS.put("age", "What is your age?");
        SECURITY_QUESTIONS.put("childhood_nickname", "What was your childhood nickname?");
        SECURITY_QUESTIONS.put("favorite_sport", "What is your favorite sport?");
    }

    @FXML private TextField emailField;
    @FXML private ComboBox<String> securityQuestionCombo;
    @FXML private TextField answerField;
    @FXML private Button verifyButton;
    @FXML private Label errorLabel;

    @FXML
    public void initialize() {
        securityQuestionCombo.getItems().addAll(SECURITY_QUESTIONS.keySet());
        securityQuestionCombo.setConverter(new StringConverter<>() {
            @Override
            public String toString(String key) {
                return key == null ? "" : SECURITY_QUESTIONS.get(key);
            }

            @Override
            public String fromString(String string) {
                return null;
            }
        });
    }

    @FXML
    private void handleVerify() {
        String email = trim(emailField.getText());
        String question = securityQuestionCombo.getValue();
        String answer = trim(answerField.getText());

        if (email.isEmpty() || question == null || answer.isEmpty()) {
            errorLabel.setText("Fill in your email, security question, and answer.");
            return;
        }

        verifyButton.setDisable(true);
        errorLabel.setText("");

        new Thread(() -> {
            try {
                String resetToken = Router.api().verifyForgotPassword(email, question, answer);
                Platform.runLater(() -> {
                    try {
                        ResetPasswordController controller = Router.navigate("/reset-password.fxml", "Academic Pulse - Reset Password");
                        controller.setResetToken(resetToken);
                    } catch (Exception e) {
                        errorLabel.setText("Verified, but failed to open the next screen: " + describe(e));
                        verifyButton.setDisable(false);
                    }
                });
            } catch (Exception e) {
                Platform.runLater(() -> {
                    errorLabel.setText(describe(e));
                    verifyButton.setDisable(false);
                });
            }
        }).start();
    }

    @FXML
    private void handleBack() {
        try {
            Router.navigate("/login.fxml", "Academic Pulse - Login");
        } catch (Exception e) {
            errorLabel.setText("Failed to go back: " + describe(e));
        }
    }

    private String trim(String value) {
        return value == null ? "" : value.trim();
    }

    private String describe(Exception e) {
        String message = e.getMessage();
        return message == null || message.isBlank() ? e.getClass().getSimpleName() : message;
    }
}
