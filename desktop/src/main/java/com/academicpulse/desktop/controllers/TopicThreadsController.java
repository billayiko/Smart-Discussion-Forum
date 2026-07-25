package com.academicpulse.desktop.controllers;

import com.academicpulse.desktop.Router;
import com.academicpulse.desktop.model.Question;
import com.academicpulse.desktop.model.Topic;
import com.academicpulse.desktop.util.ForumUi;
import com.academicpulse.desktop.util.RelativeTime;
import javafx.application.Platform;
import javafx.fxml.FXML;
import javafx.geometry.Insets;
import javafx.geometry.Pos;
import javafx.scene.control.Label;
import javafx.scene.control.TextArea;
import javafx.scene.control.TextField;
import javafx.scene.layout.HBox;
import javafx.scene.layout.Priority;
import javafx.scene.layout.Region;
import javafx.scene.layout.StackPane;
import javafx.scene.layout.VBox;

import java.util.List;

/** A topic's thread list — mirrors pages/dashboards/questions/_forum-topic.blade.php's message list and composer. */
public class TopicThreadsController {
    @FXML private Label titleLabel;
    @FXML private Label statusLabel;
    @FXML private VBox threadsBox;
    @FXML private TextField questionTitleField;
    @FXML private TextArea questionBodyField;

    private Topic topic;

    public void setTopic(Topic topic) {
        this.topic = topic;
        titleLabel.setText(topic.title);
        loadThreads();
    }

    @FXML
    private void handleRefresh() {
        loadThreads();
    }

    private void loadThreads() {
        statusLabel.setText("Loading threads...");
        new Thread(() -> {
            try {
                var threads = Router.api().getTopicQuestions(topic.id);
                Platform.runLater(() -> render(threads));
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to load threads: " + describe(e)));
            }
        }).start();
    }

    private void render(List<Question> threads) {
        String base = threads.isEmpty()
                ? "No discussions yet — ask the first question below."
                : threads.size() + " thread(s).";
        statusLabel.setText(Router.api().isOffline() ? "Offline — showing saved data. " + base : base);

        threadsBox.getChildren().clear();
        for (Question thread : threads) {
            threadsBox.getChildren().add(threadRow(thread));
        }
    }

    private HBox threadRow(Question thread) {
        String authorName = thread.user == null ? "unknown" : thread.user.name;
        String initials = thread.user == null ? "?" : thread.user.initials();

        StackPane avatar = ForumUi.avatar(initials);

        Label title = new Label(thread.title);
        title.setStyle("-fx-font-weight: bold; -fx-font-size: 13px;");
        Label roleBadge = ForumUi.roleBadge(thread.user);
        Label meta = new Label("asked by " + authorName + " · " + RelativeTime.ago(thread.createdAt));
        meta.setStyle("-fx-text-fill: #71717a; -fx-font-size: 11px; -fx-font-weight: bold;");
        HBox head = new HBox(8, title, roleBadge, meta);
        head.setAlignment(Pos.CENTER_LEFT);

        Label body = new Label(thread.body == null ? "" : truncate(thread.body, 140));
        body.setStyle("-fx-text-fill: #2d2a3d;");
        body.setWrapText(true);

        VBox textBox = new VBox(4, head, body);
        HBox.setHgrow(textBox, Priority.ALWAYS);

        Label countBadge = new Label(String.valueOf(thread.answersCount));
        countBadge.getStyleClass().add("app-count-badge");

        HBox row = new HBox(12, avatar, textBox, countBadge);
        row.setAlignment(Pos.TOP_LEFT);
        row.setPadding(new Insets(14));
        row.getStyleClass().add("app-forum-card");
        row.setOnMouseClicked(e -> openThread(thread));

        return row;
    }

    @FXML
    private void handleAsk() {
        String title = questionTitleField.getText() == null ? "" : questionTitleField.getText().trim();
        String body = questionBodyField.getText() == null ? "" : questionBodyField.getText().trim();

        if (title.isEmpty() || body.isEmpty()) {
            statusLabel.setText("Enter both a title and a description.");
            return;
        }

        statusLabel.setText("Posting...");
        new Thread(() -> {
            try {
                Router.api().createQuestion(topic.id, title, body);
                Platform.runLater(() -> {
                    questionTitleField.clear();
                    questionBodyField.clear();
                    loadThreads();
                });
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to post question: " + describe(e)));
            }
        }).start();
    }

    private void openThread(Question thread) {
        try {
            ThreadDetailController controller = Router.navigate("/thread-detail.fxml", "Academic Pulse - " + thread.title);
            controller.setTopicAndQuestionId(topic, thread.id);
        } catch (Exception e) {
            statusLabel.setText("Failed to open thread: " + describe(e));
        }
    }

    @FXML
    private void handleBack() {
        try {
            Router.navigate("/topics.fxml", "Academic Pulse - Discussion Forum");
        } catch (Exception e) {
            statusLabel.setText("Failed to go back: " + describe(e));
        }
    }

    private String truncate(String text, int max) {
        return text.length() > max ? text.substring(0, max) + "..." : text;
    }

    private String describe(Exception e) {
        String message = e.getMessage();
        return message == null || message.isBlank() ? e.getClass().getSimpleName() : message;
    }
}
