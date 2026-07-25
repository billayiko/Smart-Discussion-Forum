package com.academicpulse.desktop.controllers;

import com.academicpulse.desktop.Router;
import com.academicpulse.desktop.model.Topic;
import javafx.application.Platform;
import javafx.fxml.FXML;
import javafx.scene.control.Button;
import javafx.scene.control.CheckBox;
import javafx.scene.control.ComboBox;
import javafx.scene.control.DatePicker;
import javafx.scene.control.Label;
import javafx.scene.control.TextField;
import javafx.util.StringConverter;

import java.time.LocalTime;
import java.time.format.DateTimeFormatter;
import java.util.List;

/** Create-quiz screen — mirrors quizzes/create.blade.php. */
public class QuizCreateController {
    private record StatusOption(String value, String label) {
        @Override
        public String toString() {
            return label;
        }
    }

    private static final List<StatusOption> STATUS_OPTIONS = List.of(
            new StatusOption("draft", "Draft (hidden from students)"),
            new StatusOption("scheduled", "Published"),
            new StatusOption("closed", "Closed (force end early)")
    );

    @FXML private Label errorLabel;
    @FXML private TextField titleField;
    @FXML private TextField subjectField;
    @FXML private TextField totalQuestionsField;
    @FXML private TextField durationField;
    @FXML private DatePicker scheduledDatePicker;
    @FXML private TextField scheduledTimeField;
    @FXML private ComboBox<StatusOption> statusCombo;
    @FXML private ComboBox<Topic> topicCombo;
    @FXML private CheckBox proctoredCheckbox;
    @FXML private Button continueButton;

    private static final StringConverter<Topic> TOPIC_CONVERTER = new StringConverter<>() {
        @Override
        public String toString(Topic topic) {
            return topic == null ? "None" : topic.title;
        }

        @Override
        public Topic fromString(String string) {
            return null;
        }
    };

    @FXML
    public void initialize() {
        statusCombo.getItems().setAll(STATUS_OPTIONS);
        statusCombo.setValue(STATUS_OPTIONS.get(0));

        topicCombo.setConverter(TOPIC_CONVERTER);
        topicCombo.getItems().add(null);

        new Thread(() -> {
            try {
                List<Topic> topics = Router.api().getQuizTopics();
                Platform.runLater(() -> topicCombo.getItems().addAll(topics));
            } catch (Exception ignored) {
                // topic selection is optional; leave the dropdown at just "None" on failure
            }
        }).start();
    }

    @FXML
    private void handleContinue() {
        String title = trim(titleField.getText());
        String subject = trim(subjectField.getText());
        Integer totalQuestions = parseIntOrNull(totalQuestionsField.getText());
        Integer duration = parseIntOrNull(durationField.getText());

        if (title.isEmpty() || subject.isEmpty() || totalQuestions == null || totalQuestions < 1
                || duration == null || duration < 1) {
            errorLabel.setText("Fill in a title, subject, and valid question/duration counts.");
            return;
        }

        String scheduledAt = null;
        if (scheduledDatePicker.getValue() != null) {
            LocalTime time = parseTimeOrNull(scheduledTimeField.getText());
            if (time == null) {
                errorLabel.setText("Enter the scheduled time as HH:mm (24-hour), e.g. 14:30.");
                return;
            }
            scheduledAt = scheduledDatePicker.getValue().atTime(time)
                    .format(DateTimeFormatter.ofPattern("yyyy-MM-dd'T'HH:mm:ss"));
        }

        Topic topic = topicCombo.getValue();
        boolean proctored = proctoredCheckbox.isSelected();
        String status = statusCombo.getValue().value();
        String finalScheduledAt = scheduledAt;

        continueButton.setDisable(true);
        errorLabel.setText("");

        new Thread(() -> {
            try {
                long quizId = Router.api().createQuiz(title, subject, totalQuestions, finalScheduledAt,
                        duration, status, topic == null ? null : topic.id, proctored);
                Platform.runLater(() -> {
                    try {
                        QuizBuilderController controller = Router.navigate("/quiz-builder.fxml", "Academic Pulse - " + title);
                        controller.setQuizId(quizId);
                    } catch (Exception e) {
                        errorLabel.setText("Quiz created, but failed to open the question builder: " + describe(e));
                        continueButton.setDisable(false);
                    }
                });
            } catch (Exception e) {
                Platform.runLater(() -> {
                    errorLabel.setText("Failed to create quiz: " + describe(e));
                    continueButton.setDisable(false);
                });
            }
        }).start();
    }

    @FXML
    private void handleCancel() {
        try {
            Router.navigate("/quizzes.fxml", "Academic Pulse - Quiz Management");
        } catch (Exception e) {
            errorLabel.setText("Failed to go back: " + describe(e));
        }
    }

    private LocalTime parseTimeOrNull(String text) {
        try {
            return LocalTime.parse(trim(text), DateTimeFormatter.ofPattern("H:mm"));
        } catch (Exception e) {
            return null;
        }
    }

    private Integer parseIntOrNull(String text) {
        try {
            return Integer.parseInt(trim(text));
        } catch (Exception e) {
            return null;
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
