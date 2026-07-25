package com.academicpulse.desktop.controllers;

import com.academicpulse.desktop.Router;
import javafx.application.Platform;
import javafx.fxml.FXML;
import javafx.scene.control.Button;
import javafx.scene.control.CheckBox;
import javafx.scene.control.ComboBox;
import javafx.scene.control.Label;
import javafx.scene.control.RadioButton;
import javafx.scene.control.TextField;
import javafx.scene.control.Toggle;
import javafx.scene.control.ToggleGroup;
import javafx.scene.layout.VBox;

import java.util.LinkedHashMap;
import java.util.Map;

/** For a role='member' account (created via web social login without a role) logging into desktop — mirrors OnboardingController. */
public class OnboardingController {
    private static final Map<String, String> SECURITY_QUESTIONS = new LinkedHashMap<>();
    static {
        SECURITY_QUESTIONS.put("age", "What is your age?");
        SECURITY_QUESTIONS.put("childhood_nickname", "What was your childhood nickname?");
        SECURITY_QUESTIONS.put("favorite_sport", "What is your favorite sport?");
    }

    @FXML private ComboBox<String> roleCombo;
    @FXML private VBox securityQuestionBox;
    @FXML private TextField securityAnswerField;
    @FXML private CheckBox agreeCheckbox;
    @FXML private Button continueButton;
    @FXML private Label errorLabel;

    private final ToggleGroup securityQuestionGroup = new ToggleGroup();

    @FXML
    public void initialize() {
        roleCombo.getItems().addAll("Student", "Lecturer");

        for (Map.Entry<String, String> entry : SECURITY_QUESTIONS.entrySet()) {
            RadioButton radio = new RadioButton(entry.getValue());
            radio.setUserData(entry.getKey());
            radio.setToggleGroup(securityQuestionGroup);
            securityQuestionBox.getChildren().add(radio);
        }
    }

    @FXML
    private void handleContinue() {
        String role = roleCombo.getValue();
        Toggle selectedQuestion = securityQuestionGroup.getSelectedToggle();
        String answer = securityAnswerField.getText() == null ? "" : securityAnswerField.getText().trim();

        if (role == null || selectedQuestion == null || answer.isEmpty()) {
            errorLabel.setText("Choose a role, a security question, and provide an answer.");
            return;
        }
        if (!agreeCheckbox.isSelected()) {
            errorLabel.setText("You must agree to the platform rules to continue.");
            return;
        }

        String questionKey = (String) selectedQuestion.getUserData();

        continueButton.setDisable(true);
        errorLabel.setText("");

        new Thread(() -> {
            try {
                var user = Router.api().completeOnboarding(role.toLowerCase(), questionKey, answer);
                Router.setCurrentUser(user);
                Platform.runLater(() -> {
                    try {
                        if ("lecturer".equals(user.role)) {
                            Router.navigate("/lecturer-dashboard.fxml", "Academic Pulse - Lecturer Dashboard");
                        } else {
                            Router.startLiveQuizWatch();
                            Router.navigate("/student-dashboard.fxml", "Academic Pulse - Student Dashboard");
                        }
                    } catch (Exception e) {
                        errorLabel.setText("Onboarding complete, but failed to open your dashboard: " + describe(e));
                        continueButton.setDisable(false);
                    }
                });
            } catch (Exception e) {
                Platform.runLater(() -> {
                    errorLabel.setText(describe(e));
                    continueButton.setDisable(false);
                });
            }
        }).start();
    }

    @FXML
    private void handleDecline() {
        continueButton.setDisable(true);
        new Thread(() -> {
            try {
                Router.api().declineOnboarding();
            } catch (Exception ignored) {
                // the account may already be gone; proceed to the welcome screen regardless
            }
            Router.setCurrentUser(null);
            Platform.runLater(() -> {
                try {
                    Router.navigate("/welcome.fxml", "Academic Pulse Forum");
                } catch (Exception e) {
                    errorLabel.setText("Failed to go back: " + describe(e));
                }
            });
        }).start();
    }

    private String describe(Exception e) {
        String message = e.getMessage();
        return message == null || message.isBlank() ? e.getClass().getSimpleName() : message;
    }
}
