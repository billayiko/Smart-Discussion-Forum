package com.academicpulse.desktop.controllers;

import com.academicpulse.desktop.Router;
import com.academicpulse.desktop.model.Question;
import com.academicpulse.desktop.model.Topic;
import com.academicpulse.desktop.model.TopicActivityData;
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
import javafx.scene.layout.StackPane;
import javafx.scene.layout.VBox;
import javafx.stage.FileChooser;

import java.nio.file.Path;
import java.util.List;

/** A topic's thread list — mirrors pages/dashboards/questions/_forum-topic.blade.php's message list, composer, and panels. */
public class TopicThreadsController {
    @FXML private Label titleLabel;
    @FXML private Label statusLabel;
    @FXML private VBox threadsBox;
    @FXML private TextField questionTitleField;
    @FXML private TextArea questionBodyField;
    @FXML private VBox leaderboardBox;
    @FXML private VBox activityBox;
    @FXML private Button exportCsvButton;

    private Topic topic;

    public void setTopic(Topic topic) {
        this.topic = topic;
        titleLabel.setText(topic.title);

        String role = Router.currentUser() != null ? Router.currentUser().role : null;
        boolean maybeCanExportCsv = "admin".equals(role) || "lecturer".equals(role);
        exportCsvButton.setVisible(maybeCanExportCsv);
        exportCsvButton.setManaged(maybeCanExportCsv);

        loadThreads();
        loadActivity();
    }

    @FXML
    private void handleRefresh() {
        loadThreads();
        loadActivity();
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

    private void loadActivity() {
        new Thread(() -> {
            try {
                TopicActivityData data = Router.api().getTopicLeaderboardAndActivity(topic.id);
                Platform.runLater(() -> renderActivity(data));
            } catch (Exception ignored) {
                // leaderboard/activity are supplementary panels; a failure here shouldn't block the thread list
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

    private void renderActivity(TopicActivityData data) {
        leaderboardBox.getChildren().clear();
        if (data.participationLeaderboard.isEmpty()) {
            Label empty = new Label("No student activity in this group yet.");
            empty.setStyle("-fx-text-fill: #71717a;");
            leaderboardBox.getChildren().add(empty);
        } else {
            int rank = 1;
            for (TopicActivityData.LeaderboardRow row : data.participationLeaderboard) {
                Label nameLabel = new Label(rank + ". " + row.userName);
                nameLabel.setStyle("-fx-font-weight: bold;");
                Label postsLabel = new Label("Posts: " + row.posts);
                postsLabel.setStyle("-fx-text-fill: #71717a; -fx-font-size: 11px;");
                Region spacer = new Region();
                HBox.setHgrow(spacer, Priority.ALWAYS);
                Label scoreTag = new Label("Score: " + row.score + "%");
                scoreTag.getStyleClass().addAll("app-tag", "app-tag-green");
                HBox row1 = new HBox(10, nameLabel, postsLabel, spacer, scoreTag);
                row1.setAlignment(Pos.CENTER_LEFT);
                leaderboardBox.getChildren().add(row1);
                rank++;
            }
        }

        activityBox.getChildren().clear();
        if (data.recentActivity.isEmpty()) {
            Label empty = new Label("No recent activity yet.");
            empty.setStyle("-fx-text-fill: #71717a;");
            activityBox.getChildren().add(empty);
        } else {
            for (TopicActivityData.ActivityEvent event : data.recentActivity) {
                Label textLabel = new Label(event.text);
                textLabel.setWrapText(true);
                HBox.setHgrow(textLabel, Priority.ALWAYS);
                Label timeLabel = new Label(RelativeTime.ago(event.at));
                timeLabel.setStyle("-fx-text-fill: #71717a; -fx-font-size: 11px;");
                HBox row = new HBox(10, textLabel, timeLabel);
                row.setAlignment(Pos.CENTER_LEFT);
                activityBox.getChildren().add(row);
            }
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

    @FXML
    private void handleExportPdf() {
        FileChooser chooser = new FileChooser();
        chooser.setTitle("Save discussion export");
        chooser.setInitialFileName(topic.title.replaceAll("[^a-zA-Z0-9]+", "-") + "-discussions.pdf");
        chooser.getExtensionFilters().add(new FileChooser.ExtensionFilter("PDF files", "*.pdf"));
        java.io.File file = chooser.showSaveDialog(Router.stage());
        if (file == null) {
            return;
        }
        Path destination = file.toPath();
        statusLabel.setText("Exporting PDF...");
        new Thread(() -> {
            try {
                Router.api().exportTopicPdf(topic.id, destination);
                Platform.runLater(() -> statusLabel.setText("Saved to " + destination));
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to export PDF: " + describe(e)));
            }
        }).start();
    }

    @FXML
    private void handleExportCsv() {
        FileChooser chooser = new FileChooser();
        chooser.setTitle("Save participation export");
        chooser.setInitialFileName(topic.title.replaceAll("[^a-zA-Z0-9]+", "-") + "-participation.csv");
        chooser.getExtensionFilters().add(new FileChooser.ExtensionFilter("CSV files", "*.csv"));
        java.io.File file = chooser.showSaveDialog(Router.stage());
        if (file == null) {
            return;
        }
        Path destination = file.toPath();
        statusLabel.setText("Exporting CSV...");
        new Thread(() -> {
            try {
                Router.api().exportTopicParticipationCsv(topic.id, destination);
                Platform.runLater(() -> statusLabel.setText("Saved to " + destination));
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to export CSV: " + describe(e)));
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
