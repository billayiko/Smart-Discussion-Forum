package com.academicpulse.desktop.controllers;

import com.academicpulse.desktop.Router;
import javafx.application.Platform;
import javafx.fxml.FXML;
import javafx.scene.control.Button;
import javafx.scene.control.CheckBox;
import javafx.scene.control.ComboBox;
import javafx.scene.control.Label;
import javafx.scene.control.PasswordField;
import javafx.scene.control.RadioButton;
import javafx.scene.control.TextField;
import javafx.scene.control.Toggle;
import javafx.scene.control.ToggleGroup;
import javafx.scene.layout.VBox;

import java.util.LinkedHashMap;
import java.util.Map;

/** Account creation — mirrors resources/views/pages/auth/register.blade.php field-for-field. */
public class RegisterController {
    // Same fixed set as App\Support\SecurityQuestion::OPTIONS on the server.
    private static final Map<String, String> SECURITY_QUESTIONS = new LinkedHashMap<>();
    static {
        SECURITY_QUESTIONS.put("age", "What is your age?");
        SECURITY_QUESTIONS.put("childhood_nickname", "What was your childhood nickname?");
        SECURITY_QUESTIONS.put("favorite_sport", "What is your favorite sport?");
    }

    @FXML private TextField nameField;
    @FXML private TextField emailField;
    @FXML private ComboBox<String> roleCombo;
    @FXML private PasswordField passwordField;
    @FXML private PasswordField confirmField;
    @FXML private VBox securityQuestionBox;
    @FXML private TextField securityAnswerField;
    @FXML private CheckBox agreeCheckbox;
    @FXML private Button registerButton;
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
    private void handleRegister() {
        String name = trim(nameField.getText());
        String email = trim(emailField.getText());
        String role = roleCombo.getValue();
        String password = passwordField.getText() == null ? "" : passwordField.getText();
        String confirm = confirmField.getText() == null ? "" : confirmField.getText();
        Toggle selectedQuestion = securityQuestionGroup.getSelectedToggle();
        String securityAnswer = trim(securityAnswerField.getText());

        if (name.isEmpty() || email.isEmpty() || role == null || password.isEmpty()
                || selectedQuestion == null || securityAnswer.isEmpty()) {
            errorLabel.setText("Please fill in every field, including a security question.");
            return;
        }

        if (!password.equals(confirm)) {
            errorLabel.setText("Password and confirmation don't match.");
            return;
        }

        if (!agreeCheckbox.isSelected()) {
            errorLabel.setText("You must agree to the platform rules to create an account.");
            return;
        }

        String securityQuestionKey = (String) selectedQuestion.getUserData();

        registerButton.setDisable(true);
        errorLabel.setText("");

        new Thread(() -> {
            try {
                var user = Router.api().register(
                        name, email, password, confirm,
                        role.toLowerCase(),
                        securityQuestionKey, securityAnswer);
                Router.setCurrentUser(user);
                Platform.runLater(this::goToTopics);
            } catch (Exception e) {
                Platform.runLater(() -> showError(describe(e)));
            }
        }).start();
    }

    @FXML
    private void handleGoToLogin() {
        try {
            Router.navigate("/login.fxml", "Academic Pulse - Login");
        } catch (Exception e) {
            errorLabel.setText("Failed to load the login screen: " + describe(e));
        }
    }

    @FXML
    private void handleBack() {
        try {
            Router.navigate("/welcome.fxml", "Academic Pulse Forum");
        } catch (Exception e) {
            errorLabel.setText("Failed to go back: " + describe(e));
        }
    }

    private void goToTopics() {
        try {
            Router.navigate("/topics.fxml", "Academic Pulse - Discussion Forum");
        } catch (Exception e) {
            showError("Failed to load the next screen: " + describe(e));
        }
    }

    private void showError(String message) {
        registerButton.setDisable(false);
        errorLabel.setText(message);
    }

    private String trim(String value) {
        return value == null ? "" : value.trim();
    }

    private String describe(Exception e) {
        String message = e.getMessage();
        return message == null || message.isBlank() ? e.getClass().getSimpleName() : message;
    }
}
