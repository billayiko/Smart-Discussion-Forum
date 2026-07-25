package com.academicpulse.desktop.controllers;

import com.academicpulse.desktop.Router;
import com.academicpulse.desktop.model.QuizBuilderData;
import com.academicpulse.desktop.model.QuizQuestion;
import com.academicpulse.desktop.util.FriendlyDate;
import javafx.application.Platform;
import javafx.fxml.FXML;
import javafx.geometry.Insets;
import javafx.geometry.Pos;
import javafx.scene.control.Button;
import javafx.scene.control.Label;
import javafx.scene.control.ProgressBar;
import javafx.scene.control.TextField;
import javafx.scene.layout.HBox;
import javafx.scene.layout.Priority;
import javafx.scene.layout.Region;
import javafx.scene.layout.VBox;

/** Question builder — mirrors quizzes/questions.blade.php. */
public class QuizBuilderController {
    @FXML private Label titleLabel;
    @FXML private Label subtitleLabel;
    @FXML private Label statusLabel;
    @FXML private Label errorLabel;
    @FXML private Label progressLabel;
    @FXML private Label progressNoteLabel;
    @FXML private ProgressBar progressBar;
    @FXML private Button finalizeButton;
    @FXML private VBox questionsBox;
    @FXML private VBox addQuestionCard;
    @FXML private TextField questionField;
    @FXML private TextField optionAField;
    @FXML private TextField optionBField;
    @FXML private TextField optionCField;
    @FXML private TextField optionDField;
    @FXML private TextField correctOptionField;

    private long quizId;

    public void setQuizId(long quizId) {
        this.quizId = quizId;
        load();
    }

    @FXML
    private void handleRefresh() {
        load();
    }

    private void load() {
        statusLabel.setText("Loading...");
        new Thread(() -> {
            try {
                QuizBuilderData data = Router.api().getQuizBuilder(quizId);
                Platform.runLater(() -> render(data));
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to load quiz: " + describe(e)));
            }
        }).start();
    }

    private void render(QuizBuilderData data) {
        statusLabel.setText(Router.api().isOffline() ? "Offline — showing saved data." : "");

        titleLabel.setText(data.title);
        subtitleLabel.setText(data.subject + " · " + data.durationMinutes + " mins · "
                + (data.scheduledAt != null ? FriendlyDate.format(data.scheduledAt) : "Not scheduled yet"));

        int added = data.questions.size();
        boolean finalized = data.questionsFinalizedAt != null;
        boolean targetReached = added >= data.totalQuestions;

        progressLabel.setText(added + " / " + data.totalQuestions);
        progressBar.setProgress(data.totalQuestions > 0 ? Math.min((double) added / data.totalQuestions, 1) : 0);

        if (finalized) {
            progressNoteLabel.setText("Saved on " + FriendlyDate.format(data.questionsFinalizedAt)
                    + ". Students will see this quiz announced ahead of time and be taken to it once it starts.");
            finalizeButton.setVisible(false);
            finalizeButton.setManaged(false);
        } else if (targetReached) {
            progressNoteLabel.setText("All required questions have been added. Save them to notify students.");
            finalizeButton.setVisible(true);
            finalizeButton.setManaged(true);
        } else {
            progressNoteLabel.setText((data.totalQuestions - added) + " more question(s) needed before you can save this quiz.");
            finalizeButton.setVisible(false);
            finalizeButton.setManaged(false);
        }

        questionsBox.getChildren().clear();
        if (data.questions.isEmpty()) {
            Label empty = new Label("No questions added yet. Add your first question using the form.");
            empty.setStyle("-fx-text-fill: #71717a;");
            questionsBox.getChildren().add(empty);
        } else {
            int index = 1;
            for (QuizQuestion question : data.questions) {
                questionsBox.getChildren().add(questionRow(index++, question, finalized));
            }
        }

        addQuestionCard.setVisible(!targetReached);
        addQuestionCard.setManaged(!targetReached);
    }

    private VBox questionRow(int index, QuizQuestion question, boolean finalized) {
        Label numberLabel = new Label(index + ".");
        numberLabel.setStyle("-fx-font-weight: bold;");
        Label questionLabel = new Label(question.question);
        questionLabel.setStyle("-fx-font-weight: bold;");
        questionLabel.setWrapText(true);
        HBox.setHgrow(questionLabel, Priority.ALWAYS);

        HBox head = new HBox(8, numberLabel, questionLabel);
        head.setAlignment(Pos.TOP_LEFT);

        if (!finalized) {
            Button deleteButton = new Button("Remove");
            deleteButton.getStyleClass().add("app-btn-danger");
            deleteButton.setOnAction(e -> deleteQuestion(question));
            Region spacer = new Region();
            HBox.setHgrow(spacer, Priority.ALWAYS);
            head.getChildren().addAll(spacer, deleteButton);
        }

        VBox optionsBox = new VBox(2);
        String[][] options = {
                {"a", question.optionA}, {"b", question.optionB}, {"c", question.optionC}, {"d", question.optionD}
        };
        for (String[] option : options) {
            boolean correct = option[0].equals(question.correctOption);
            Label optionLabel = new Label(option[0].toUpperCase() + ". " + option[1] + (correct ? "  ✓" : ""));
            optionLabel.setStyle(correct ? "-fx-text-fill: #0d9f6e; -fx-font-weight: bold;" : "-fx-text-fill: #52525b;");
            optionsBox.getChildren().add(optionLabel);
        }

        VBox card = new VBox(6, head, optionsBox);
        card.setPadding(new Insets(10));
        card.getStyleClass().add("app-topic-card");
        return card;
    }

    private void deleteQuestion(QuizQuestion question) {
        statusLabel.setText("Removing...");
        new Thread(() -> {
            try {
                Router.api().deleteQuizQuestion(quizId, question.id);
                Platform.runLater(this::load);
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to remove question: " + describe(e)));
            }
        }).start();
    }

    @FXML
    private void handleAddQuestion() {
        String question = trim(questionField.getText());
        String a = trim(optionAField.getText());
        String b = trim(optionBField.getText());
        String c = trim(optionCField.getText());
        String d = trim(optionDField.getText());
        String correct = trim(correctOptionField.getText()).toLowerCase();

        if (question.isEmpty() || a.isEmpty() || b.isEmpty() || c.isEmpty() || d.isEmpty()) {
            errorLabel.setText("Fill in the question and all four options.");
            return;
        }
        if (!correct.equals("a") && !correct.equals("b") && !correct.equals("c") && !correct.equals("d")) {
            errorLabel.setText("Type a, b, c or d for the correct answer.");
            return;
        }

        errorLabel.setText("");
        statusLabel.setText("Adding...");
        new Thread(() -> {
            try {
                Router.api().addQuizQuestion(quizId, question, a, b, c, d, correct);
                Platform.runLater(() -> {
                    questionField.clear();
                    optionAField.clear();
                    optionBField.clear();
                    optionCField.clear();
                    optionDField.clear();
                    correctOptionField.clear();
                    load();
                });
            } catch (Exception e) {
                Platform.runLater(() -> errorLabel.setText("Failed to add question: " + describe(e)));
            }
        }).start();
    }

    @FXML
    private void handleFinalize() {
        finalizeButton.setDisable(true);
        statusLabel.setText("Saving...");
        new Thread(() -> {
            try {
                Router.api().finalizeQuiz(quizId);
                Platform.runLater(this::load);
            } catch (Exception e) {
                Platform.runLater(() -> {
                    statusLabel.setText("Failed to save questions: " + describe(e));
                    finalizeButton.setDisable(false);
                });
            }
        }).start();
    }

    @FXML
    private void handleBack() {
        try {
            Router.navigate("/quizzes.fxml", "Academic Pulse - Quiz Management");
        } catch (Exception e) {
            statusLabel.setText("Failed to go back: " + describe(e));
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
