package com.academicpulse.desktop.controllers;

import com.academicpulse.desktop.Router;
import com.academicpulse.desktop.model.Topic;
import com.academicpulse.desktop.model.TopicAnalytics;
import com.academicpulse.desktop.model.User;
import javafx.application.Platform;
import javafx.fxml.FXML;
import javafx.scene.control.Label;
import javafx.scene.layout.FlowPane;
import javafx.scene.layout.VBox;

import java.util.List;
import java.util.function.Function;

/** Admin-only per-topic analytics — mirrors Admin\AnalyticsController::show() on the web. */
public class TopicAnalyticsController {
    @FXML private Label titleLabel;
    @FXML private Label statusLabel;
    @FXML private FlowPane statsBox;
    @FXML private VBox topAskersBox;
    @FXML private VBox topAnswerersBox;

    private Topic topic;

    public void setTopic(Topic topic) {
        this.topic = topic;
        titleLabel.setText(topic.title);
        loadAnalytics();
    }

    private void loadAnalytics() {
        statusLabel.setText("Loading analytics...");
        new Thread(() -> {
            try {
                TopicAnalytics data = Router.api().getTopicAnalytics(topic.id);
                Platform.runLater(() -> render(data));
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to load analytics: " + describe(e)));
            }
        }).start();
    }

    private void render(TopicAnalytics data) {
        titleLabel.setText(data.topic.title);
        String lecturerName = data.topic.lecturer == null ? "an unassigned lecturer" : data.topic.lecturer.name;
        String base = "Led by " + lecturerName;
        statusLabel.setText(Router.api().isOffline() ? "Offline — showing saved data. " + base : base);

        statsBox.getChildren().setAll(
                statCard("Subscribers", String.valueOf(data.summary.subscribers)),
                statCard("Questions", data.summary.questions + " (" + data.summary.unansweredQuestions + " unanswered)"),
                statCard("Answers", String.valueOf(data.summary.answers)),
                statCard("Pending complaints", String.valueOf(data.summary.pendingComplaints)),
                statCard("Quizzes", String.valueOf(data.summary.quizzes)),
                statCard("Avg. quiz score", data.summary.averageQuizScore != null ? data.summary.averageQuizScore + "%" : "—")
        );

        fillRows(topAskersBox, data.topAskers, "No questions asked yet.",
                (User u) -> u.name + "  —  " + u.questionsCount + " question(s)");
        fillRows(topAnswerersBox, data.topAnswerers, "No answers posted yet.",
                (User u) -> u.name + " (" + u.roleLabel() + ")  —  " + u.answersCount + " answer(s)");
    }

    private <T> void fillRows(VBox box, List<T> items, String emptyText, Function<T, String> text) {
        box.getChildren().clear();
        if (items == null || items.isEmpty()) {
            box.getChildren().add(new Label(emptyText));
            return;
        }
        for (T item : items) {
            Label row = new Label(text.apply(item));
            row.getStyleClass().add("app-list-row");
            row.setMaxWidth(Double.MAX_VALUE);
            box.getChildren().add(row);
        }
    }

    private VBox statCard(String label, String value) {
        Label valueLabel = new Label(value);
        valueLabel.getStyleClass().add("app-stat-value");
        Label textLabel = new Label(label);
        textLabel.getStyleClass().add("app-stat-label");
        VBox card = new VBox(2, valueLabel, textLabel);
        card.getStyleClass().add("app-stat-card");
        return card;
    }

    @FXML
    private void handleBack() {
        try {
            Router.navigate("/analytics.fxml", "Academic Pulse - Analytics");
        } catch (Exception e) {
            statusLabel.setText("Failed to go back: " + describe(e));
        }
    }

    private String describe(Exception e) {
        String message = e.getMessage();
        return message == null || message.isBlank() ? e.getClass().getSimpleName() : message;
    }
}
