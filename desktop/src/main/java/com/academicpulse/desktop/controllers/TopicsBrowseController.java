package com.academicpulse.desktop.controllers;

import com.academicpulse.desktop.Router;
import com.academicpulse.desktop.model.TopicBrowseData;
import javafx.application.Platform;
import javafx.fxml.FXML;
import javafx.geometry.Pos;
import javafx.scene.control.Button;
import javafx.scene.control.Label;
import javafx.scene.layout.FlowPane;
import javafx.scene.layout.HBox;
import javafx.scene.layout.VBox;

/** Student's Topics browse/subscribe screen — mirrors pages/dashboards/student/topics.blade.php. */
public class TopicsBrowseController {
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
                TopicBrowseData data = Router.api().getBrowseTopics();
                Platform.runLater(() -> render(data));
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to load topics: " + describe(e)));
            }
        }).start();
    }

    private void render(TopicBrowseData data) {
        statusLabel.setText(Router.api().isOffline() ? "Offline — showing saved data." : "");

        topicsBox.getChildren().clear();
        for (TopicBrowseData.Item topic : data.topics) {
            boolean subscribed = data.subscribedTopicIds.contains(topic.id);
            topicsBox.getChildren().add(topicCard(topic, subscribed));
        }
    }

    private VBox topicCard(TopicBrowseData.Item topic, boolean subscribed) {
        Label title = new Label(topic.title);
        title.setStyle("-fx-font-size: 15px; -fx-font-weight: bold;");
        title.setWrapText(true);
        HBox.setHgrow(title, javafx.scene.layout.Priority.ALWAYS);

        HBox head = new HBox(8, title);
        if (subscribed) {
            Label tag = new Label("Subscribed");
            tag.getStyleClass().addAll("app-tag", "app-tag-green");
            head.getChildren().add(tag);
        }
        head.setAlignment(Pos.CENTER_LEFT);

        VBox card = new VBox(8, head);
        card.getStyleClass().add("app-topic-card");
        card.setPrefWidth(280);
        card.setMaxWidth(280);

        Label description = new Label(topic.description == null || topic.description.isBlank()
                ? "No description provided." : topic.description);
        description.setStyle("-fx-text-fill: #71717a; -fx-font-size: 12px;");
        description.setWrapText(true);
        card.getChildren().add(description);

        Label lecturer = new Label((topic.lecturerName == null ? "No lecturer assigned yet" : topic.lecturerName));
        lecturer.setStyle("-fx-text-fill: #71717a; -fx-font-size: 11px;");
        Label subscribers = new Label(topic.subscribersCount + " subscriber(s)");
        subscribers.setStyle("-fx-text-fill: #71717a; -fx-font-size: 11px;");
        card.getChildren().addAll(lecturer, subscribers);

        Button actionButton = new Button(subscribed ? "Unsubscribe" : "Subscribe");
        actionButton.getStyleClass().add(subscribed ? "app-btn-light" : "app-btn-primary");
        actionButton.setMaxWidth(Double.MAX_VALUE);
        actionButton.setOnAction(e -> toggleSubscription(topic, subscribed));
        card.getChildren().add(actionButton);

        return card;
    }

    private void toggleSubscription(TopicBrowseData.Item topic, boolean currentlySubscribed) {
        statusLabel.setText(currentlySubscribed ? "Unsubscribing..." : "Subscribing...");
        new Thread(() -> {
            try {
                if (currentlySubscribed) {
                    Router.api().unsubscribeTopic(topic.id);
                } else {
                    Router.api().subscribeTopic(topic.id);
                }
                Platform.runLater(this::load);
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to update subscription: " + describe(e)));
            }
        }).start();
    }

    private String describe(Exception e) {
        String message = e.getMessage();
        return message == null || message.isBlank() ? e.getClass().getSimpleName() : message;
    }
}
