package com.academicpulse.desktop.controllers;

import com.academicpulse.desktop.Router;
import com.academicpulse.desktop.model.Question;
import com.academicpulse.desktop.model.Topic;
import javafx.application.Platform;
import javafx.collections.FXCollections;
import javafx.fxml.FXML;
import javafx.scene.control.Label;
import javafx.scene.control.ListCell;
import javafx.scene.control.ListView;
import javafx.scene.control.TextArea;
import javafx.scene.control.TextField;

public class TopicThreadsController {
    @FXML private Label titleLabel;
    @FXML private Label statusLabel;
    @FXML private ListView<Question> threadsListView;
    @FXML private TextField questionTitleField;
    @FXML private TextArea questionBodyField;

    private Topic topic;

    @FXML
    public void initialize() {
        threadsListView.setCellFactory(list -> new ListCell<>() {
            @Override
            protected void updateItem(Question question, boolean empty) {
                super.updateItem(question, empty);
                setText(empty || question == null
                        ? null
                        : String.format("%s  —  asked by %s  —  %d repl%s",
                        question.title,
                        question.user == null ? "unknown" : question.user.name,
                        question.answersCount,
                        question.answersCount == 1 ? "y" : "ies"));
            }
        });
    }

    public void setTopic(Topic topic) {
        this.topic = topic;
        titleLabel.setText(topic.title);
        loadThreads();
    }

    private void loadThreads() {
        statusLabel.setText("Loading threads...");
        new Thread(() -> {
            try {
                var threads = Router.api().getTopicQuestions(topic.id);
                Platform.runLater(() -> {
                    threadsListView.setItems(FXCollections.observableArrayList(threads));
                    String base = threads.isEmpty()
                            ? "No discussions yet — ask the first question below."
                            : threads.size() + " thread(s).";
                    statusLabel.setText(Router.api().isOffline() ? "Offline — showing saved data. " + base : base);
                });
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to load threads: " + describe(e)));
            }
        }).start();
    }

    @FXML
    private void handleOpenThread() {
        Question selected = threadsListView.getSelectionModel().getSelectedItem();
        if (selected == null) {
            statusLabel.setText("Select a thread first.");
            return;
        }
        try {
            ThreadDetailController controller = Router.navigate("/thread-detail.fxml", "Academic Pulse - " + selected.title);
            controller.setTopicAndQuestionId(topic, selected.id);
        } catch (Exception e) {
            statusLabel.setText("Failed to open thread: " + describe(e));
        }
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

    @FXML
    private void handleBack() {
        try {
            Router.navigate("/topics.fxml", "Academic Pulse - Discussion Forum");
        } catch (Exception e) {
            statusLabel.setText("Failed to go back: " + describe(e));
        }
    }

    private String describe(Exception e) {
        String message = e.getMessage();
        return message == null || message.isBlank() ? e.getClass().getSimpleName() : message;
    }
}
