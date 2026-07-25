package com.academicpulse.desktop.controllers;

import com.academicpulse.desktop.Router;
import com.academicpulse.desktop.model.Topic;
import javafx.application.Platform;
import javafx.fxml.FXML;
import javafx.geometry.Pos;
import javafx.scene.control.Button;
import javafx.scene.control.Label;
import javafx.scene.layout.FlowPane;
import javafx.scene.layout.HBox;
import javafx.scene.layout.VBox;

import java.util.List;

/** Discussion Forum landing screen — mirrors pages/dashboards/questions/_topic-bubbles.blade.php. */
public class TopicsController {
    @FXML private Label statusLabel;
    @FXML private FlowPane topicsBox;

    @FXML
    public void initialize() {
        load();
    }

    @FXML
    private void handleRefresh() {
        load();
    }

    private void load() {
        statusLabel.setText("Loading topics...");
        new Thread(() -> {
            try {
                var topics = Router.api().getTopics();
                Platform.runLater(() -> render(topics));
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to load topics: " + describe(e)));
            }
        }).start();
    }

    private void render(List<Topic> topics) {
        String base = topics.isEmpty()
                ? "No subscribed topics yet — browse the Topics page to subscribe to one."
                : topics.size() + " topic(s).";
        statusLabel.setText(Router.api().isOffline() ? "Offline — showing saved data. " + base : base);

        topicsBox.getChildren().clear();
        for (Topic topic : topics) {
            topicsBox.getChildren().add(topicCard(topic));
        }
    }

    private VBox topicCard(Topic topic) {
        Label title = new Label(topic.title);
        title.setStyle("-fx-font-size: 15px; -fx-font-weight: bold;");
        title.setWrapText(true);

        VBox card = new VBox(10, title);
        card.getStyleClass().add("app-topic-card");
        card.setPrefWidth(280);
        card.setMaxWidth(280);

        if (topic.description != null && !topic.description.isBlank()) {
            String text = topic.description.length() > 90 ? topic.description.substring(0, 90) + "..." : topic.description;
            Label description = new Label(text);
            description.setStyle("-fx-text-fill: #71717a; -fx-font-size: 12px;");
            description.setWrapText(true);
            card.getChildren().add(description);
        }

        Label membersTag = new Label(topic.subscribersCount + " members");
        membersTag.getStyleClass().addAll("app-tag", "app-tag-purple");
        Label threadsTag = new Label(topic.questionsCount + " threads");
        threadsTag.getStyleClass().addAll("app-tag", "app-tag-gray");
        HBox tags = new HBox(8, membersTag, threadsTag);

        Button viewForum = new Button("View Forum →");
        viewForum.getStyleClass().add("app-btn-primary");
        viewForum.setMaxWidth(Double.MAX_VALUE);
        viewForum.setOnAction(e -> openTopic(topic));

        card.getChildren().addAll(tags, viewForum);
        card.setAlignment(Pos.TOP_LEFT);

        return card;
    }

    private void openTopic(Topic topic) {
        try {
            TopicThreadsController controller = Router.navigate("/topic-threads.fxml", "Academic Pulse - " + topic.title);
            controller.setTopic(topic);
        } catch (Exception e) {
            statusLabel.setText("Failed to open topic: " + describe(e));
        }
    }

    private String describe(Exception e) {
        String message = e.getMessage();
        return message == null || message.isBlank() ? e.getClass().getSimpleName() : message;
    }
}
