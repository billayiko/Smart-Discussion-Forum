package com.academicpulse.desktop.controllers;

import com.academicpulse.desktop.Router;
import com.academicpulse.desktop.model.Topic;
import javafx.application.Platform;
import javafx.collections.FXCollections;
import javafx.fxml.FXML;
import javafx.scene.control.Label;
import javafx.scene.control.ListCell;
import javafx.scene.control.ListView;

public class TopicsController {
    @FXML private ListView<Topic> topicsListView;
    @FXML private Label statusLabel;

    @FXML
    public void initialize() {
        topicsListView.setCellFactory(list -> new ListCell<>() {
            @Override
            protected void updateItem(Topic topic, boolean empty) {
                super.updateItem(topic, empty);
                setText(empty || topic == null
                        ? null
                        : String.format("%s  —  %d members, %d threads", topic.title, topic.subscribersCount, topic.questionsCount));
            }
        });

        loadTopics();
    }

    private void loadTopics() {
        statusLabel.setText("Loading topics...");
        new Thread(() -> {
            try {
                var topics = Router.api().getTopics();
                Platform.runLater(() -> {
                    topicsListView.setItems(FXCollections.observableArrayList(topics));
                    statusLabel.setText(topics.isEmpty()
                            ? "No topics yet — ask an admin/lecturer to add you to one."
                            : topics.size() + " topic(s).");
                });
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to load topics: " + describe(e)));
            }
        }).start();
    }

    @FXML
    private void handleOpenTopic() {
        Topic selected = topicsListView.getSelectionModel().getSelectedItem();
        if (selected == null) {
            statusLabel.setText("Select a topic first.");
            return;
        }
        try {
            TopicThreadsController controller = Router.navigate("/topic-threads.fxml", "Academic Pulse - " + selected.title);
            controller.setTopic(selected);
        } catch (Exception e) {
            statusLabel.setText("Failed to open topic: " + describe(e));
        }
    }

    @FXML
    private void handleLogout() {
        statusLabel.setText("Logging out...");
        new Thread(() -> {
            try {
                Router.api().logout();
            } catch (Exception ignored) {
                // token may already be invalid server-side; proceed to login regardless
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
