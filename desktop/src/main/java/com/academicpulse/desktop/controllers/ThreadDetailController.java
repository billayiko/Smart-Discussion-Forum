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

public class ThreadDetailController {
    @FXML private Label titleLabel;
    @FXML private Label bodyLabel;
    @FXML private Label statusLabel;
    @FXML private ListView<Answer> answersListView;
    @FXML private TextArea replyField;

    private Topic topic;
    private long questionId;

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
    }

    private void loadQuestion() {
        statusLabel.setText("Loading...");
        new Thread(() -> {
            try {
                Question question = Router.api().getQuestion(questionId);
                Platform.runLater(() -> {
                    titleLabel.setText(question.title);
                    String author = question.user == null ? "unknown" : question.user.name;
                    bodyLabel.setText("Asked by " + author + ": " + question.body);
                    answersListView.setItems(FXCollections.observableArrayList(question.answers));
                    int count = question.answers.size();
                    statusLabel.setText(count == 0 ? "No replies yet." : count + " repl" + (count == 1 ? "y" : "ies") + ".");
                });
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to load thread: " + describe(e)));
            }
        }).start();
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
        try {
            TopicThreadsController controller = Router.navigate("/topic-threads.fxml", "Academic Pulse - " + topic.title);
            controller.setTopic(topic);
        } catch (Exception e) {
            statusLabel.setText("Failed to go back: " + describe(e));
        }
    }

    @FXML
    private void handleLogout() {
        statusLabel.setText("Logging out...");
        new Thread(() -> {
            try {
                Router.api().logout();
            } catch (Exception ignored) {
            }
            Platform.runLater(() -> {
                try {
                    Router.navigate("/login.fxml", "Academic Pulse - Login");
                } catch (Exception e) {
                    statusLabel.setText("Failed to return to login: " + describe(e));
                }
            });
        }).start();
    }

    private String describe(Exception e) {
        String message = e.getMessage();
        return message == null || message.isBlank() ? e.getClass().getSimpleName() : message;
    }
}
