package com.academicpulse.desktop.controllers;

import com.academicpulse.desktop.Router;
import com.academicpulse.desktop.model.QuizEditData;
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
import java.time.OffsetDateTime;
import java.time.format.DateTimeFormatter;
import java.util.List;

/** Edit-quiz screen — mirrors quizzes/edit.blade.php. */
public class QuizEditController {
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

    @FXML private Label statusLabel;
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
    @FXML private Button saveButton;

    private long quizId;
    private List<Topic> topics = List.of();

    @FXML
    public void initialize() {
        statusCombo.getItems().setAll(STATUS_OPTIONS);
        topicCombo.setConverter(TOPIC_CONVERTER);
        topicCombo.getItems().add(null);
    }

    public void setQuizId(long quizId) {
        this.quizId = quizId;
        load();
    }

    private void load() {
        statusLabel.setText("Loading...");
        new Thread(() -> {
            try {
                QuizEditData data = Router.api().getQuizEdit(quizId);
                List<Topic> loadedTopics = Router.api().getQuizTopics();
                Platform.runLater(() -> {
                    topics = loadedTopics;
                    topicCombo.getItems().setAll(java.util.Collections.singletonList((Topic) null));
                    topicCombo.getItems().addAll(topics);
                    render(data);
                });
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to load quiz: " + describe(e)));
            }
        }).start();
    }

    private void render(QuizEditData data) {
        statusLabel.setText(data.isFinalized ? "This quiz's questions are already saved; the question count is locked." : "");
        titleField.setText(data.title);
        subjectField.setText(data.subject);
        totalQuestionsField.setText(String.valueOf(data.totalQuestions));
        totalQuestionsField.setDisable(data.isFinalized);
        durationField.setText(String.valueOf(data.durationMinutes));
        proctoredCheckbox.setSelected(data.proctored);

        statusCombo.setValue(STATUS_OPTIONS.stream()
                .filter(o -> o.value().equals(data.status))
                .findFirst().orElse(STATUS_OPTIONS.get(0)));

        topicCombo.setValue(topics.stream()
                .filter(t -> data.courseTopicId != null && t.id == data.courseTopicId)
                .findFirst().orElse(null));

        if (data.scheduledAt != null && !data.scheduledAt.isBlank()) {
            OffsetDateTime dt = OffsetDateTime.parse(data.scheduledAt);
            scheduledDatePicker.setValue(dt.toLocalDate());
            scheduledTimeField.setText(dt.toLocalTime().format(DateTimeFormatter.ofPattern("HH:mm")));
        }
    }

    @FXML
    private void handleSave() {
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

        saveButton.setDisable(true);
        errorLabel.setText("");

        new Thread(() -> {
            try {
                Router.api().updateQuiz(quizId, title, subject, totalQuestions, finalScheduledAt,
                        duration, status, topic == null ? null : topic.id, proctored);
                Platform.runLater(() -> {
                    try {
                        Router.navigate("/quizzes.fxml", "Academic Pulse - Quiz Management");
                    } catch (Exception e) {
                        errorLabel.setText("Saved, but failed to return to the list: " + describe(e));
                        saveButton.setDisable(false);
                    }
                });
            } catch (Exception e) {
                Platform.runLater(() -> {
                    errorLabel.setText("Failed to save changes: " + describe(e));
                    saveButton.setDisable(false);
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
