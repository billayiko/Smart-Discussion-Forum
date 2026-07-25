package com.academicpulse.desktop.controllers;

import com.academicpulse.desktop.Router;
import com.academicpulse.desktop.api.ApiException;
import com.academicpulse.desktop.model.QuizQuestion;
import com.academicpulse.desktop.model.QuizTakeData;
import javafx.application.Platform;
import javafx.beans.value.ChangeListener;
import javafx.fxml.FXML;
import javafx.scene.control.Button;
import javafx.scene.control.Label;
import javafx.scene.control.RadioButton;
import javafx.scene.control.Toggle;
import javafx.scene.control.ToggleGroup;
import javafx.scene.layout.VBox;
import javafx.stage.Stage;

import java.time.OffsetDateTime;
import java.util.LinkedHashMap;
import java.util.Map;
import java.util.concurrent.Executors;
import java.util.concurrent.ScheduledExecutorService;
import java.util.concurrent.TimeUnit;

/**
 * Quiz-taking screen — mirrors quizzes/take.blade.php exactly: a countdown
 * that auto-submits at zero, and for proctored quizzes a fullscreen gate
 * that treats losing OS focus (the desktop equivalent of switching browser
 * tabs) or exiting fullscreen as a violation, auto-submitting after 3.
 */
public class QuizTakeController {
    private static final int MAX_VIOLATIONS = 3;

    @FXML private Label titleLabel;
    @FXML private Label subtitleLabel;
    @FXML private Label countdownLabel;
    @FXML private Label errorLabel;
    @FXML private Button backToDashboardButton;
    @FXML private Label warningLabel;
    @FXML private VBox proctorGate;
    @FXML private VBox questionsBox;
    @FXML private Button submitButton;

    private long quizId;
    private long endsAtMillis = -1;
    private final Map<Long, ToggleGroup> answerGroups = new LinkedHashMap<>();
    private ScheduledExecutorService countdownTimer;
    private volatile boolean autoSubmitted = false;
    private volatile boolean submitting = false;
    private int violations = 0;
    private boolean proctoringActive = false;
    private ChangeListener<Boolean> focusListener;
    private ChangeListener<Boolean> fullScreenListener;

    public void setQuizId(long quizId) {
        this.quizId = quizId;
        Router.setQuizTakeActive(true);
        load();
    }

    private void load() {
        errorLabel.setText("Loading quiz...");
        new Thread(() -> {
            try {
                QuizTakeData data = Router.api().getQuizTake(quizId);
                Platform.runLater(() -> render(data));
            } catch (ApiException e) {
                Platform.runLater(() -> handleLoadFailure(e));
            } catch (Exception e) {
                Platform.runLater(() -> {
                    errorLabel.setText("Failed to load quiz: " + describe(e));
                    showBackButton();
                });
            }
        }).start();
    }

    private void handleLoadFailure(ApiException e) {
        if (e.statusCode == 409) {
            // Already attempted — the API mirrors take()'s own redirect to the result screen.
            Router.setQuizTakeActive(false);
            try {
                QuizResultController controller = Router.navigate("/quiz-result.fxml", "Academic Pulse - Quiz Result");
                controller.setQuizId(quizId);
            } catch (Exception navError) {
                errorLabel.setText("Failed to open result screen: " + describe(navError));
                showBackButton();
            }
            return;
        }
        errorLabel.setText(e.getMessage());
        showBackButton();
    }

    private void showBackButton() {
        backToDashboardButton.setVisible(true);
        backToDashboardButton.setManaged(true);
    }

    private void render(QuizTakeData data) {
        errorLabel.setText("");
        titleLabel.setText(data.title);
        subtitleLabel.setText(data.subject + " · " + data.questions.size() + " question(s)"
                + (data.proctored ? " · Proctored" : ""));

        questionsBox.getChildren().clear();
        answerGroups.clear();
        for (int i = 0; i < data.questions.size(); i++) {
            questionsBox.getChildren().add(questionCard(i + 1, data.questions.get(i)));
        }

        if (data.endsAt != null && !data.endsAt.isBlank()) {
            endsAtMillis = OffsetDateTime.parse(data.endsAt).toInstant().toEpochMilli();
            startCountdown();
        } else {
            countdownLabel.setText("");
        }

        boolean gated = data.proctored;
        proctorGate.setVisible(gated);
        proctorGate.setManaged(gated);
        questionsBox.setVisible(!gated);
        questionsBox.setManaged(!gated);
        submitButton.setVisible(!gated);
        submitButton.setManaged(!gated);
    }

    private VBox questionCard(int index, QuizQuestion question) {
        Label questionLabel = new Label(index + ". " + question.question);
        questionLabel.setStyle("-fx-font-weight: bold;");
        questionLabel.setWrapText(true);

        ToggleGroup group = new ToggleGroup();
        answerGroups.put(question.id, group);

        VBox optionsBox = new VBox(6);
        String[][] options = {
                {"a", question.optionA}, {"b", question.optionB}, {"c", question.optionC}, {"d", question.optionD}
        };
        for (String[] option : options) {
            RadioButton radio = new RadioButton(option[0].toUpperCase() + ". " + option[1]);
            radio.setUserData(option[0]);
            radio.setToggleGroup(group);
            radio.setWrapText(true);
            optionsBox.getChildren().add(radio);
        }

        return new VBox(8, questionLabel, optionsBox);
    }

    private void startCountdown() {
        stopCountdown();
        countdownTimer = Executors.newSingleThreadScheduledExecutor(r -> {
            Thread t = new Thread(r, "quiz-countdown");
            t.setDaemon(true);
            return t;
        });
        countdownTimer.scheduleAtFixedRate(this::tickCountdown, 0, 1, TimeUnit.SECONDS);
    }

    private void stopCountdown() {
        if (countdownTimer != null) {
            countdownTimer.shutdownNow();
            countdownTimer = null;
        }
    }

    private void tickCountdown() {
        long remainingMs = endsAtMillis - System.currentTimeMillis();
        Platform.runLater(() -> {
            if (remainingMs <= 0) {
                submitNow("Time's up — submitting...");
                return;
            }
            long totalSeconds = remainingMs / 1000;
            countdownLabel.setText(String.format("Time left: %d:%02d", totalSeconds / 60, totalSeconds % 60));
        });
    }

    @FXML
    private void handleBeginProctored() {
        Stage stage = Router.stage();
        if (stage != null) {
            stage.setFullScreen(true);
        }
        proctoringActive = true;
        proctorGate.setVisible(false);
        proctorGate.setManaged(false);
        questionsBox.setVisible(true);
        questionsBox.setManaged(true);
        submitButton.setVisible(true);
        submitButton.setManaged(true);

        attachProctoringListeners();
    }

    private void attachProctoringListeners() {
        Stage stage = Router.stage();
        if (stage == null) {
            return;
        }
        focusListener = (obs, was, isNow) -> {
            if (!isNow) {
                recordViolation("you switched away from the quiz window");
            }
        };
        fullScreenListener = (obs, was, isNow) -> {
            if (proctoringActive && !isNow) {
                recordViolation("you exited fullscreen");
            }
        };
        stage.focusedProperty().addListener(focusListener);
        stage.fullScreenProperty().addListener(fullScreenListener);
    }

    private void detachProctoringListeners() {
        Stage stage = Router.stage();
        if (stage == null) {
            return;
        }
        if (focusListener != null) {
            stage.focusedProperty().removeListener(focusListener);
            focusListener = null;
        }
        if (fullScreenListener != null) {
            stage.fullScreenProperty().removeListener(fullScreenListener);
            fullScreenListener = null;
        }
        if (stage.isFullScreen()) {
            stage.setFullScreen(false);
        }
    }

    private void recordViolation(String label) {
        if (!proctoringActive || autoSubmitted) {
            return;
        }
        violations++;
        int current = violations;
        Platform.runLater(() -> {
            warningLabel.setVisible(true);
            warningLabel.setManaged(true);
            if (current >= MAX_VIOLATIONS) {
                warningLabel.setText("Final warning (" + current + "/" + MAX_VIOLATIONS + "): " + label + ". Submitting your quiz now.");
                submitNow("Too many proctoring warnings — submitting...");
            } else {
                warningLabel.setText("Warning " + current + "/" + MAX_VIOLATIONS + ": " + label + ". Stay in fullscreen and on this window.");
            }
        });
    }

    private void submitNow(String reason) {
        if (autoSubmitted) {
            return;
        }
        autoSubmitted = true;
        countdownLabel.setText(reason);
        doSubmit();
    }

    @FXML
    private void handleSubmit() {
        doSubmit();
    }

    private void doSubmit() {
        if (submitting) {
            return;
        }
        submitting = true;
        submitButton.setDisable(true);
        submitButton.setText("Submitting…");
        stopCountdown();
        proctoringActive = false;

        Map<Long, String> answers = new LinkedHashMap<>();
        for (Map.Entry<Long, ToggleGroup> entry : answerGroups.entrySet()) {
            Toggle selected = entry.getValue().getSelectedToggle();
            if (selected != null) {
                answers.put(entry.getKey(), (String) selected.getUserData());
            }
        }
        int violationsSnapshot = violations;

        new Thread(() -> {
            try {
                Router.api().submitQuiz(quizId, answers, violationsSnapshot);
                Platform.runLater(() -> {
                    detachProctoringListeners();
                    Router.setQuizTakeActive(false);
                    try {
                        QuizResultController controller = Router.navigate("/quiz-result.fxml", "Academic Pulse - Quiz Result");
                        controller.setQuizId(quizId);
                    } catch (Exception e) {
                        errorLabel.setText("Quiz submitted, but failed to open the result screen: " + describe(e));
                        showBackButton();
                    }
                });
            } catch (Exception e) {
                Platform.runLater(() -> {
                    errorLabel.setText("Failed to submit quiz: " + describe(e));
                    submitButton.setDisable(false);
                    submitButton.setText("Submit quiz");
                    submitting = false;
                });
            }
        }).start();
    }

    @FXML
    private void handleBackToDashboard() {
        stopCountdown();
        detachProctoringListeners();
        Router.setQuizTakeActive(false);
        try {
            Router.navigate("/student-dashboard.fxml", "Academic Pulse - Student Dashboard");
        } catch (Exception e) {
            errorLabel.setText("Failed to go back: " + describe(e));
        }
    }

    private String describe(Exception e) {
        String message = e.getMessage();
        return message == null || message.isBlank() ? e.getClass().getSimpleName() : message;
    }
}
