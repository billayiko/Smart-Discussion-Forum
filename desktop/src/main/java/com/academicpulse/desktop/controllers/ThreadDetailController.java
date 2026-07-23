package com.academicpulse.desktop.controllers;

import com.academicpulse.desktop.Router;
import com.academicpulse.desktop.model.Answer;
import com.academicpulse.desktop.model.Question;
import com.academicpulse.desktop.model.Topic;
import javafx.application.Platform;
import javafx.collections.FXCollections;
import javafx.fxml.FXML;
import javafx.scene.control.Label;
import javafx.scene.control.ListCell;
import javafx.scene.control.ListView;
import javafx.scene.control.TextArea;

import java.util.concurrent.Executors;
import java.util.concurrent.ScheduledExecutorService;
import java.util.concurrent.TimeUnit;

public class ThreadDetailController {
    private static final long POLL_INTERVAL_SECONDS = 5;

    @FXML private Label titleLabel;
    @FXML private Label bodyLabel;
    @FXML private Label statusLabel;
    @FXML private ListView<Answer> answersListView;
    @FXML private TextArea replyField;

    private Topic topic;
    private long questionId;
    private ScheduledExecutorService poller;

    @FXML
    public void initialize() {
        answersListView.setCellFactory(list -> new ListCell<>() {
            @Override
            protected void updateItem(Answer answer, boolean empty) {
                super.updateItem(answer, empty);
                if (empty || answer == null) {
                    setText(null);
                    return;
                }
                String author = answer.user == null ? "unknown" : answer.user.name;
                setText(author + ": " + answer.body);
                setWrapText(true);
            }
        });
    }

    public void setTopicAndQuestionId(Topic topic, long questionId) {
        this.topic = topic;
        this.questionId = questionId;
        loadQuestion();
        startPolling();
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
        String author = question.user == null ? "unknown" : question.user.name;
        bodyLabel.setText("Asked by " + author + ": " + question.body);
        answersListView.setItems(FXCollections.observableArrayList(question.answers));
        int count = question.answers.size();
        String base = count == 0 ? "No replies yet." : count + " repl" + (count == 1 ? "y" : "ies") + ".";
        statusLabel.setText(Router.api().isOffline() ? "Offline — showing saved data. " + base : base);
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
