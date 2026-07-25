package com.academicpulse.desktop.controllers;

import com.academicpulse.desktop.Router;
import com.academicpulse.desktop.model.Answer;
import com.academicpulse.desktop.model.Question;
import com.academicpulse.desktop.model.Topic;
import com.academicpulse.desktop.util.ForumUi;
import com.academicpulse.desktop.util.RelativeTime;
import javafx.application.Platform;
import javafx.fxml.FXML;
import javafx.geometry.Pos;
import javafx.scene.control.Label;
import javafx.scene.control.TextArea;
import javafx.scene.layout.HBox;
import javafx.scene.layout.Priority;
import javafx.scene.layout.VBox;

import java.util.concurrent.Executors;
import java.util.concurrent.ScheduledExecutorService;
import java.util.concurrent.TimeUnit;

/** A single thread's question + replies — mirrors pages/dashboards/questions/_forum-thread.blade.php's message list. */
public class ThreadDetailController {
    private static final long POLL_INTERVAL_SECONDS = 5;

    @FXML private Label titleLabel;
    @FXML private Label statusLabel;
    @FXML private VBox messagesBox;
    @FXML private TextArea replyField;

    private Topic topic;
    private long questionId;
    private ScheduledExecutorService poller;

    public void setTopicAndQuestionId(Topic topic, long questionId) {
        this.topic = topic;
        this.questionId = questionId;
        loadQuestion();
        startPolling();
    }

    @FXML
    private void handleRefresh() {
        loadQuestion();
    }

    /**
     * Polls for new replies every few seconds — the same mechanism also
     * covers reconnect-after-offline, since each tick just retries the API
     * call and {@link com.academicpulse.desktop.api.ApiClient} transparently
     * falls back to (and later recovers from) its local cache.
     */
    private void startPolling() {
        stopPolling();
        poller = Executors.newSingleThreadScheduledExecutor(runnable -> {
            Thread thread = new Thread(runnable, "thread-poll");
            thread.setDaemon(true);
            return thread;
        });
        poller.scheduleWithFixedDelay(this::pollQuestion, POLL_INTERVAL_SECONDS, POLL_INTERVAL_SECONDS, TimeUnit.SECONDS);
    }

    /**
     * {@code Router.navigate()} swaps the FXML root with no lifecycle
     * callback, so this must be stopped explicitly before navigating away —
     * otherwise it keeps polling forever from an orphaned instance.
     */
    private void stopPolling() {
        if (poller != null) {
            poller.shutdownNow();
            poller = null;
        }
    }

    private void loadQuestion() {
        statusLabel.setText("Loading...");
        new Thread(() -> {
            try {
                Question question = Router.api().getQuestion(questionId);
                Platform.runLater(() -> applyQuestion(question));
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to load thread: " + describe(e)));
            }
        }).start();
    }

    private void pollQuestion() {
        try {
            Question question = Router.api().getQuestion(questionId);
            Platform.runLater(() -> applyQuestion(question));
        } catch (Exception e) {
            Platform.runLater(() -> statusLabel.setText("Failed to refresh: " + describe(e)));
        }
    }

    private void applyQuestion(Question question) {
        titleLabel.setText(question.title);

        int count = question.answers.size();
        String base = count == 0 ? "No replies yet." : count + " repl" + (count == 1 ? "y" : "ies") + ".";
        statusLabel.setText(Router.api().isOffline() ? "Offline — showing saved data. " + base : base);

        messagesBox.getChildren().clear();
        messagesBox.getChildren().add(messageCard(question.user, question.body, question.createdAt, question.views));
        for (Answer answer : question.answers) {
            messagesBox.getChildren().add(messageCard(answer.user, answer.body, answer.createdAt, -1));
        }
    }

    private VBox messageCard(com.academicpulse.desktop.model.User author, String body, String createdAt, long views) {
        String name = author == null ? "unknown" : author.name;
        String initials = author == null ? "?" : author.initials();

        Label nameLabel = new Label(name);
        nameLabel.setStyle("-fx-font-weight: bold;");
        Label roleBadge = ForumUi.roleBadge(author);
        Label timeLabel = new Label(RelativeTime.ago(createdAt));
        timeLabel.setStyle("-fx-text-fill: #71717a; -fx-font-size: 11px; -fx-font-weight: bold;");
        HBox head = new HBox(8, nameLabel, roleBadge, timeLabel);
        head.setAlignment(Pos.CENTER_LEFT);

        Label bodyLabel = new Label(body == null ? "" : body);
        bodyLabel.setWrapText(true);
        bodyLabel.setStyle("-fx-text-fill: #2d2a3d;");

        VBox textBox = new VBox(6, head, bodyLabel);
        if (views >= 0) {
            Label viewsLabel = new Label(views + " views");
            viewsLabel.setStyle("-fx-text-fill: #71717a; -fx-font-size: 11px; -fx-font-weight: bold;");
            textBox.getChildren().add(viewsLabel);
        }
        HBox.setHgrow(textBox, Priority.ALWAYS);

        HBox row = new HBox(12, ForumUi.avatar(initials), textBox);
        row.setAlignment(Pos.TOP_LEFT);

        VBox card = new VBox(row);
        card.getStyleClass().add("app-topic-card");
        return card;
    }

    @FXML
    private void handleReply() {
        String body = replyField.getText() == null ? "" : replyField.getText().trim();
        if (body.isEmpty()) {
            statusLabel.setText("Write a reply first.");
            return;
        }

        statusLabel.setText("Sending...");
        new Thread(() -> {
            try {
                Router.api().createAnswer(questionId, body);
                Platform.runLater(() -> {
                    replyField.clear();
                    loadQuestion();
                });
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to send reply: " + describe(e)));
            }
        }).start();
    }

    @FXML
    private void handleBack() {
        stopPolling();
        try {
            TopicThreadsController controller = Router.navigate("/topic-threads.fxml", "Academic Pulse - " + topic.title);
            controller.setTopic(topic);
        } catch (Exception e) {
            statusLabel.setText("Failed to go back: " + describe(e));
        }
    }

    private String describe(Exception e) {
        String message = e.getMessage();
        return message == null || message.isBlank() ? e.getClass().getSimpleName() : message;
    }
}
