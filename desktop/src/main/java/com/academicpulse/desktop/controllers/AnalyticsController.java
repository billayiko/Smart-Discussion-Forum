package com.academicpulse.desktop.controllers;

import com.academicpulse.desktop.Router;
import com.academicpulse.desktop.model.AnalyticsSummary;
import com.academicpulse.desktop.model.Topic;
import com.academicpulse.desktop.model.User;
import javafx.application.Platform;
import javafx.fxml.FXML;
import javafx.scene.Cursor;
import javafx.scene.control.Label;
import javafx.scene.layout.FlowPane;
import javafx.scene.layout.VBox;

import java.util.List;
import java.util.function.Function;

/** Admin-only site-wide analytics — mirrors Admin\AnalyticsController::index() on the web. */
public class AnalyticsController {
    @FXML private Label statusLabel;
    @FXML private FlowPane statsBox;
    @FXML private VBox topAskersBox;
    @FXML private VBox topAnswerersBox;
    @FXML private VBox topTopicsBox;
    @FXML private VBox topLecturersBox;

    @FXML
    public void initialize() {
        loadAnalytics();
    }

    private void loadAnalytics() {
        statusLabel.setText("Loading analytics...");
        new Thread(() -> {
            try {
                AnalyticsSummary data = Router.api().getAnalytics();
                Platform.runLater(() -> render(data));
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to load analytics: " + describe(e)));
            }
        }).start();
    }

    private void render(AnalyticsSummary data) {
        statusLabel.setText(Router.api().isOffline() ? "Offline — showing saved data." : "");

        statsBox.getChildren().setAll(
                statCard("Students", String.valueOf(data.summary.students)),
                statCard("Lecturers", String.valueOf(data.summary.lecturers)),
                statCard("Topics", String.valueOf(data.summary.topics)),
                statCard("Questions", data.summary.questions + " (" + data.summary.unansweredQuestions + " unanswered)"),
                statCard("Answers", String.valueOf(data.summary.answers)),
                statCard("Pending complaints", String.valueOf(data.summary.pendingComplaints))
        );

        fillRows(topAskersBox, data.topAskers, "No questions asked yet.",
                (User u) -> u.name + "  —  " + u.questionsCount + " question(s)");
        fillRows(topAnswerersBox, data.topAnswerers, "No answers posted yet.",
                (User u) -> u.name + " (" + u.roleLabel() + ")  —  " + u.answersCount + " answer(s)");
        fillRows(topLecturersBox, data.topLecturers, "No topics assigned yet.",
                (User u) -> u.name + "  —  " + u.assignedTopicsCount + " topic(s)");

        topTopicsBox.getChildren().clear();
        List<Topic> topics = data.topTopics;
        if (topics == null || topics.isEmpty()) {
            topTopicsBox.getChildren().add(new Label("No topic subscriptions yet."));
        } else {
            for (Topic topic : topics) {
                Label row = new Label(topic.title + "  —  " + topic.subscribersCount + " subscriber(s)");
                row.getStyleClass().add("app-list-row");
                row.setMaxWidth(Double.MAX_VALUE);
                row.setCursor(Cursor.HAND);
                row.setOnMouseClicked(e -> openTopic(topic));
                topTopicsBox.getChildren().add(row);
            }
        }
    }

    private void openTopic(Topic topic) {
        try {
            TopicAnalyticsController controller = Router.navigate("/topic-analytics.fxml", "Academic Pulse - " + topic.title + " Analytics");
            controller.setTopic(topic);
        } catch (Exception e) {
            statusLabel.setText("Failed to open topic analytics: " + describe(e));
        }
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

    private String describe(Exception e) {
        String message = e.getMessage();
        return message == null || message.isBlank() ? e.getClass().getSimpleName() : message;
    }
}
