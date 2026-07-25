package com.academicpulse.desktop.controllers;

import com.academicpulse.desktop.Router;
import com.academicpulse.desktop.model.NotificationsData;
import com.academicpulse.desktop.util.RelativeTime;
import javafx.application.Platform;
import javafx.fxml.FXML;
import javafx.geometry.Insets;
import javafx.geometry.Pos;
import javafx.scene.control.Button;
import javafx.scene.control.Label;
import javafx.scene.layout.HBox;
import javafx.scene.layout.Priority;
import javafx.scene.layout.Region;
import javafx.scene.layout.VBox;

/** Notifications list — mirrors partials/_notification-bell.blade.php's dropdown content as a full screen. */
public class NotificationsController {
    @FXML private Label subtitleLabel;
    @FXML private Label statusLabel;
    @FXML private VBox notificationsBox;

    @FXML
    public void initialize() {
        load();
    }

    @FXML
    private void handleRefresh() {
        load();
    }

    @FXML
    private void handleMarkAllRead() {
        statusLabel.setText("Marking all read...");
        new Thread(() -> {
            try {
                Router.api().markAllNotificationsRead();
                Platform.runLater(this::load);
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed: " + describe(e)));
            }
        }).start();
    }

    private void load() {
        statusLabel.setText("Loading...");
        new Thread(() -> {
            try {
                NotificationsData data = Router.api().getNotifications();
                Platform.runLater(() -> render(data));
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to load notifications: " + describe(e)));
            }
        }).start();
    }

    private void render(NotificationsData data) {
        statusLabel.setText(Router.api().isOffline() ? "Offline — showing saved data." : "");
        subtitleLabel.setText(data.unreadCount + " unread");

        notificationsBox.getChildren().clear();
        if (data.notifications.isEmpty()) {
            Label empty = new Label("No notifications yet.");
            empty.setStyle("-fx-text-fill: #71717a;");
            notificationsBox.getChildren().add(empty);
            return;
        }
        for (NotificationsData.Item item : data.notifications) {
            notificationsBox.getChildren().add(notificationRow(item));
        }
    }

    private HBox notificationRow(NotificationsData.Item item) {
        String title;
        String body;

        switch (item.type) {
            case "quiz_scheduled" -> {
                title = "New quiz scheduled: " + str(item.data.get("quiz_title"));
                body = str(item.data.get("subject")) + " · " + str(item.data.get("duration_minutes")) + " min";
            }
            case "membership_blacklisted" -> {
                title = "Account suspended";
                body = str(item.data.get("message"));
            }
            case "membership_warning" -> {
                title = "Inactivity warning";
                body = str(item.data.get("message"));
            }
            case "topic_suggested" -> {
                title = "Suggested topic: " + str(item.data.get("topic_title"));
                body = "Students with similar recent activity are engaging with this.";
            }
            case "question_answered" -> {
                title = str(item.data.get("answerer_name")) + " replied to " + str(item.data.get("question_title"));
                body = str(item.data.get("excerpt"));
            }
            default -> {
                title = "Notification";
                body = "";
            }
        }

        Label titleLabel = new Label(title);
        titleLabel.setStyle("-fx-font-weight: bold;");
        titleLabel.setWrapText(true);
        Label bodyLabel = new Label(body);
        bodyLabel.setStyle("-fx-text-fill: #71717a; -fx-font-size: 11px;");
        bodyLabel.setWrapText(true);
        Label timeLabel = new Label(RelativeTime.ago(item.createdAt));
        timeLabel.setStyle("-fx-text-fill: #a0a0ab; -fx-font-size: 10px;");

        VBox textBox = new VBox(3, titleLabel, bodyLabel, timeLabel);
        HBox.setHgrow(textBox, Priority.ALWAYS);

        HBox row = new HBox(10, textBox);
        row.setAlignment(Pos.CENTER_LEFT);
        row.setPadding(new Insets(12));
        row.getStyleClass().add(item.read ? "app-list-row" : "app-forum-card");

        if (!item.read) {
            Region spacer = new Region();
            HBox.setHgrow(spacer, Priority.ALWAYS);
            Label unreadTag = new Label("New");
            unreadTag.getStyleClass().addAll("app-tag", "app-tag-purple");
            row.getChildren().add(unreadTag);
        }

        if ("topic_suggested".equals(item.type)) {
            Object topicIdObj = item.data.get("topic_id");
            long topicId = topicIdObj == null ? -1 : Long.parseLong(String.valueOf(topicIdObj));
            Button subscribeButton = new Button("Subscribe");
            subscribeButton.getStyleClass().add("app-btn-primary");
            subscribeButton.setOnAction(e -> subscribeToSuggestion(topicId));
            Button ignoreButton = new Button("Ignore");
            ignoreButton.getStyleClass().add("app-btn-light");
            ignoreButton.setOnAction(e -> ignoreSuggestion(topicId));
            row.getChildren().addAll(subscribeButton, ignoreButton);
        } else if (!item.read) {
            row.setOnMouseClicked(e -> markRead(item.id));
        }

        return row;
    }

    private void subscribeToSuggestion(long topicId) {
        if (topicId < 0) {
            return;
        }
        new Thread(() -> {
            try {
                Router.api().subscribeTopic(topicId);
                Platform.runLater(this::load);
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to subscribe: " + describe(e)));
            }
        }).start();
    }

    private void ignoreSuggestion(long topicId) {
        if (topicId < 0) {
            return;
        }
        new Thread(() -> {
            try {
                Router.api().ignoreTopicSuggestion(topicId);
                Platform.runLater(this::load);
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to dismiss suggestion: " + describe(e)));
            }
        }).start();
    }

    private void markRead(String notificationId) {
        new Thread(() -> {
            try {
                Router.api().markNotificationRead(notificationId);
                Platform.runLater(this::load);
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed: " + describe(e)));
            }
        }).start();
    }

    private String str(Object value) {
        return value == null ? "" : String.valueOf(value);
    }

    private String describe(Exception e) {
        String message = e.getMessage();
        return message == null || message.isBlank() ? e.getClass().getSimpleName() : message;
    }
}
