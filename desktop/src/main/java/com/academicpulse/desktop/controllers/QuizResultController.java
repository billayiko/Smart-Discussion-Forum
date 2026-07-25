package com.academicpulse.desktop.controllers;

import com.academicpulse.desktop.Router;
import com.academicpulse.desktop.api.ApiException;
import com.academicpulse.desktop.model.QuizQuestion;
import com.academicpulse.desktop.model.QuizResultData;
import com.academicpulse.desktop.util.FriendlyDate;
import javafx.application.Platform;
import javafx.fxml.FXML;
import javafx.scene.control.Button;
import javafx.scene.control.Label;
import javafx.scene.layout.HBox;
import javafx.scene.layout.VBox;

import java.util.Map;

/** Quiz result screen — mirrors quizzes/result.blade.php. */
public class QuizResultController {
    @FXML private Label titleLabel;
    @FXML private Label subjectLabel;
    @FXML private Label statusLabel;
    @FXML private Label scoreLabel;
    @FXML private Label violationsLabel;
    @FXML private Label noAttemptLabel;
    @FXML private Label classReportLabel;
    @FXML private HBox confirmBox;
    @FXML private Label confirmLabel;
    @FXML private Button confirmButton;
    @FXML private VBox topScorersBox;
    @FXML private VBox reviewBox;
    @FXML private Button backButton;

    private long quizId;

    public void setQuizId(long quizId) {
        this.quizId = quizId;
        load();
    }

    private void load() {
        statusLabel.setText("Loading...");
        new Thread(() -> {
            try {
                QuizResultData data = Router.api().getQuizResult(quizId);
                Platform.runLater(() -> render(data));
            } catch (ApiException e) {
                Platform.runLater(() -> handleFailure(e));
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to load result: " + describe(e)));
            }
        }).start();
    }

    private void handleFailure(ApiException e) {
        if (e.statusCode == 409) {
            // The quiz is still live and this student hasn't attempted it yet — mirrors result()'s own redirect to take().
            try {
                QuizTakeController controller = Router.navigate("/quiz-take.fxml", "Academic Pulse - Take Quiz");
                controller.setQuizId(quizId);
            } catch (Exception navError) {
                statusLabel.setText("Failed to open the quiz: " + describe(navError));
            }
            return;
        }
        statusLabel.setText(e.getMessage());
    }

    private void render(QuizResultData data) {
        statusLabel.setText("");
        titleLabel.setText(data.title + " — Result");
        subjectLabel.setText(data.subject);

        classReportLabel.setText("Class report: " + data.report.attemptsCount + " student(s) attempted · average score "
                + (data.report.averageScorePercent != null ? data.report.averageScorePercent + "%" : "—"));

        if (data.attempt != null) {
            int pct = data.attempt.total > 0 ? (int) Math.round((data.attempt.score * 100.0) / data.attempt.total) : 0;
            scoreLabel.setText("Score: " + data.attempt.score + " / " + data.attempt.total + " (" + pct + "%)");
            scoreLabel.setVisible(true);
            scoreLabel.setManaged(true);

            if (data.attempt.proctoringViolations > 0) {
                violationsLabel.setText(data.attempt.proctoringViolations + " proctoring warning(s) were recorded during this attempt.");
                violationsLabel.setVisible(true);
                violationsLabel.setManaged(true);
            } else {
                violationsLabel.setVisible(false);
                violationsLabel.setManaged(false);
            }

            noAttemptLabel.setVisible(false);
            noAttemptLabel.setManaged(false);
        } else {
            scoreLabel.setVisible(false);
            scoreLabel.setManaged(false);
            violationsLabel.setVisible(false);
            violationsLabel.setManaged(false);
            noAttemptLabel.setText("You did not attempt this quiz before it closed.");
            noAttemptLabel.setVisible(true);
            noAttemptLabel.setManaged(true);
        }

        if (data.canConfirm) {
            confirmBox.setVisible(true);
            confirmBox.setManaged(true);
            if (data.marksConfirmed) {
                confirmLabel.setText("✓ Marks confirmed on " + FriendlyDate.format(data.marksConfirmedAt) + " — visible to admin.");
                confirmButton.setVisible(false);
                confirmButton.setManaged(false);
            } else {
                confirmLabel.setText("⚠ Not yet confirmed — admin cannot see these marks until you confirm them.");
                confirmButton.setVisible(true);
                confirmButton.setManaged(true);
                confirmButton.setDisable(false);
            }
        } else {
            confirmBox.setVisible(false);
            confirmBox.setManaged(false);
        }

        topScorersBox.getChildren().clear();
        int rank = 1;
        for (QuizResultData.TopScorer scorer : data.report.topScorers) {
            int scorerPct = scorer.total > 0 ? (int) Math.round((scorer.score * 100.0) / scorer.total) : 0;
            HBox row = new HBox();
            row.setSpacing(10);
            Label nameLabel = new Label(rank + ". " + scorer.userName);
            nameLabel.setStyle("-fx-font-weight: bold;");
            javafx.scene.layout.Region spacer = new javafx.scene.layout.Region();
            HBox.setHgrow(spacer, javafx.scene.layout.Priority.ALWAYS);
            Label scoreValueLabel = new Label(scorer.score + "/" + scorer.total + " (" + scorerPct + "%)");
            row.getChildren().addAll(nameLabel, spacer, scoreValueLabel);
            row.setStyle("-fx-padding: 8; -fx-background-color: #f7f7fb; -fx-background-radius: 10;");
            topScorersBox.getChildren().add(row);
            rank++;
        }

        reviewBox.getChildren().clear();
        if (data.attempt != null) {
            int index = 1;
            for (QuizQuestion question : data.questions) {
                reviewBox.getChildren().add(reviewRow(index++, question, data.attempt.answers));
            }
        }

        String role = Router.currentUser() != null ? Router.currentUser().role : null;
        backButton.setText("lecturer".equals(role) ? "🏠 Back to Quizzes" : "admin".equals(role) ? "🏠 Back to Admin Dashboard" : "🏠 Back to Dashboard");
    }

    private VBox reviewRow(int index, QuizQuestion question, Map<String, String> answers) {
        String given = answers == null ? null : answers.get(String.valueOf(question.id));
        boolean correct = given != null && given.equals(question.correctOption);

        Label questionLabel = new Label(index + ". " + question.question);
        questionLabel.setStyle("-fx-font-weight: bold;");
        questionLabel.setWrapText(true);

        String givenText = given != null ? given.toUpperCase() + ". " + optionText(question, given) : "Not answered";
        Label answerLabel = new Label((correct ? "✓ " : "✗ ") + "Your answer: " + givenText);
        answerLabel.setStyle((correct ? "-fx-text-fill: #166534;" : "-fx-text-fill: #b91c1c;") + " -fx-font-weight: bold;");
        answerLabel.setWrapText(true);

        VBox card = new VBox(6, questionLabel, answerLabel);
        if (!correct) {
            Label correctLabel = new Label("Correct answer: " + question.correctOption.toUpperCase() + ". " + optionText(question, question.correctOption));
            correctLabel.setStyle("-fx-text-fill: #166534; -fx-font-weight: bold;");
            correctLabel.setWrapText(true);
            card.getChildren().add(correctLabel);
        }
        card.setStyle("-fx-padding: 12; -fx-background-color: white; -fx-background-radius: 12; -fx-border-color: #e2e2ea; -fx-border-radius: 12;");
        return card;
    }

    private String optionText(QuizQuestion question, String letter) {
        return switch (letter) {
            case "a" -> question.optionA;
            case "b" -> question.optionB;
            case "c" -> question.optionC;
            case "d" -> question.optionD;
            default -> "";
        };
    }

    @FXML
    private void handleConfirmMarks() {
        confirmButton.setDisable(true);
        new Thread(() -> {
            try {
                Router.api().confirmQuizMarks(quizId);
                Platform.runLater(this::load);
            } catch (Exception e) {
                Platform.runLater(() -> {
                    statusLabel.setText("Failed to confirm marks: " + describe(e));
                    confirmButton.setDisable(false);
                });
            }
        }).start();
    }

    @FXML
    private void handleBack() {
        try {
            String role = Router.currentUser() != null ? Router.currentUser().role : null;
            if ("lecturer".equals(role)) {
                Router.navigate("/quizzes.fxml", "Academic Pulse - Quiz Management");
            } else if ("admin".equals(role)) {
                Router.navigate("/admin-dashboard.fxml", "Academic Pulse - Admin Dashboard");
            } else {
                Router.navigate("/student-dashboard.fxml", "Academic Pulse - Student Dashboard");
            }
        } catch (Exception e) {
            statusLabel.setText("Failed to go back: " + describe(e));
        }
    }

    private String describe(Exception e) {
        String message = e.getMessage();
        return message == null || message.isBlank() ? e.getClass().getSimpleName() : message;
    }
}
