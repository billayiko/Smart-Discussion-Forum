package com.academicpulse.desktop.controllers;

import com.academicpulse.desktop.Router;
import com.academicpulse.desktop.model.Answer;
import com.academicpulse.desktop.model.Question;
import com.academicpulse.desktop.model.Topic;
import com.academicpulse.desktop.model.User;
import com.academicpulse.desktop.util.ForumUi;
import com.academicpulse.desktop.util.RelativeTime;
import javafx.application.Platform;
import javafx.fxml.FXML;
import javafx.geometry.Insets;
import javafx.geometry.Pos;
import javafx.scene.control.Button;
import javafx.scene.control.Label;
import javafx.scene.control.TextArea;
import javafx.scene.control.TextField;
import javafx.scene.layout.HBox;
import javafx.scene.layout.Priority;
import javafx.scene.layout.Region;
import javafx.scene.layout.VBox;

import java.util.concurrent.Executors;
import java.util.concurrent.ScheduledExecutorService;
import java.util.concurrent.TimeUnit;

/** A single thread's question + replies — mirrors pages/dashboards/questions/_forum-thread.blade.php's message list. */
public class ThreadDetailController {
    private static final long POLL_INTERVAL_SECONDS = 5;

    @FXML private Label titleLabel;
    @FXML private Button reportButton;
    @FXML private Label statusLabel;
    @FXML private VBox reportBox;
    @FXML private TextField reportReasonField;
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

    /** Reached from the admin's flat moderation list, which has no single owning topic to return to. */
    public void setQuestionIdForAdmin(long questionId) {
        this.topic = null;
        this.questionId = questionId;
        loadQuestion();
        startPolling();
    }

    @FXML
    private void handleRefresh() {
        loadQuestion();
    }

    private void startPolling() {
        stopPolling();
        poller = Executors.newSingleThreadScheduledExecutor(runnable -> {
            Thread thread = new Thread(runnable, "thread-poll");
            thread.setDaemon(true);
            return thread;
        });
        poller.scheduleWithFixedDelay(this::pollQuestion, POLL_INTERVAL_SECONDS, POLL_INTERVAL_SECONDS, TimeUnit.SECONDS);
    }

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
        messagesBox.getChildren().add(questionCard(question));
        for (Answer answer : question.answers) {
            messagesBox.getChildren().add(answerCard(answer));
        }
    }

    private VBox questionCard(Question question) {
        VBox card = messageShell(question.user, question.body, question.createdAt, question.views,
                question.likesCount, question.likedByMe, () -> Router.api().toggleQuestionLike(question.id));
        return card;
    }

    private VBox answerCard(Answer answer) {
        return messageShell(answer.user, answer.body, answer.createdAt, -1,
                answer.likesCount, answer.likedByMe, () -> Router.api().toggleAnswerLike(answer.id));
    }

    private interface LikeToggle {
        boolean toggle() throws Exception;
    }

    private VBox messageShell(User author, String body, String createdAt, long views,
                               long likesCount, boolean likedByMe, LikeToggle toggle) {
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

        Button likeButton = new Button((likedByMe ? "♥ " : "♡ ") + likesCount);
        likeButton.getStyleClass().add(likedByMe ? "app-btn-primary" : "app-btn-light");
        likeButton.setOnAction(e -> {
            likeButton.setDisable(true);
            new Thread(() -> {
                try {
                    toggle.toggle();
                    Platform.runLater(this::loadQuestion);
                } catch (Exception ex) {
                    Platform.runLater(() -> {
                        statusLabel.setText("Failed to like: " + describe(ex));
                        likeButton.setDisable(false);
                    });
                }
            }).start();
        });

        HBox footer = new HBox(10, likeButton);
        footer.setAlignment(Pos.CENTER_LEFT);

        VBox textBox = new VBox(6, head, bodyLabel);
        if (views >= 0) {
            Label viewsLabel = new Label(views + " views");
            viewsLabel.setStyle("-fx-text-fill: #71717a; -fx-font-size: 11px; -fx-font-weight: bold;");
            textBox.getChildren().add(viewsLabel);
        }
        textBox.getChildren().add(footer);
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
    private void handleToggleReport() {
        boolean show = !reportBox.isVisible();
        reportBox.setVisible(show);
        reportBox.setManaged(show);
    }

    @FXML
    private void handleSubmitReport() {
        String reason = reportReasonField.getText() == null ? "" : reportReasonField.getText().trim();
        if (reason.isEmpty()) {
            statusLabel.setText("Describe why you're reporting this question first.");
            return;
        }

        statusLabel.setText("Submitting report...");
        new Thread(() -> {
            try {
                Router.api().reportQuestion(questionId, reason);
                Platform.runLater(() -> {
                    reportReasonField.clear();
                    reportBox.setVisible(false);
                    reportBox.setManaged(false);
                    statusLabel.setText("Your complaint has been submitted to the admin for review.");
                });
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to submit report: " + describe(e)));
            }
        }).start();
    }

    @FXML
    private void handleBack() {
        stopPolling();
        try {
            if (topic != null) {
                TopicThreadsController controller = Router.navigate("/topic-threads.fxml", "Academic Pulse - " + topic.title);
                controller.setTopic(topic);
            } else {
                Router.navigate("/admin-questions.fxml", "Academic Pulse - Questions");
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
