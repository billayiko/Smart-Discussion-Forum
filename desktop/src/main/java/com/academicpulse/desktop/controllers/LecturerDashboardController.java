package com.academicpulse.desktop.controllers;

import com.academicpulse.desktop.Router;
import com.academicpulse.desktop.model.LecturerDashboard;
import javafx.application.Platform;
import javafx.fxml.FXML;
import javafx.geometry.Insets;
import javafx.geometry.Pos;
import javafx.scene.control.Label;
import javafx.scene.control.TextField;
import javafx.scene.layout.FlowPane;
import javafx.scene.layout.HBox;
import javafx.scene.layout.Priority;
import javafx.scene.layout.Region;
import javafx.scene.layout.VBox;

import java.time.LocalTime;
import java.util.List;

/** Lecturer's main dashboard — mirrors pages/dashboards/lecturer.blade.php. */
public class LecturerDashboardController {
    @FXML private Label greetingLabel;
    @FXML private Label statusLabel;
    @FXML private FlowPane statsBox;
    @FXML private VBox quizManagementBox;
    @FXML private VBox questionsBox;
    @FXML private VBox quizStatusBox;
    @FXML private VBox performanceBox;
    @FXML private VBox discussionsBox;
    @FXML private TextField pointsPerQuestionField;
    @FXML private TextField pointsPerAnswerField;
    @FXML private TextField pointsPerLikeField;
    @FXML private TextField targetPointsField;
    @FXML private Label criteriaStatusLabel;

    @FXML
    public void initialize() {
        var user = Router.currentUser();
        String firstName = user != null && user.name != null ? user.name.trim().split(" ")[0] : "Lecturer";
        int hour = LocalTime.now().getHour();
        String greeting = hour < 12 ? "Good morning" : hour < 17 ? "Good afternoon" : "Good evening";
        greetingLabel.setText(greeting + ", " + firstName);

        load();
    }

    @FXML
    private void handleRefresh() {
        load();
    }

    @FXML
    private void handleSaveCriteria() {
        Integer perQuestion = parseIntOrNull(pointsPerQuestionField.getText());
        Integer perAnswer = parseIntOrNull(pointsPerAnswerField.getText());
        Integer perLike = parseIntOrNull(pointsPerLikeField.getText());
        Integer target = parseIntOrNull(targetPointsField.getText());

        if (perQuestion == null || perAnswer == null || perLike == null || target == null) {
            criteriaStatusLabel.setText("Enter whole numbers in every field.");
            return;
        }

        criteriaStatusLabel.setText("Saving...");

        new Thread(() -> {
            try {
                Router.api().updateParticipationCriteria(perQuestion, perAnswer, perLike, target);
                Platform.runLater(() -> criteriaStatusLabel.setText("Criteria saved."));
            } catch (Exception e) {
                Platform.runLater(() -> criteriaStatusLabel.setText("Failed to save: " + describe(e)));
            }
        }).start();
    }

    private void load() {
        statusLabel.setText("Loading...");

        new Thread(() -> {
            try {
                LecturerDashboard data = Router.api().getLecturerDashboard();
                Platform.runLater(() -> render(data));
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to load dashboard: " + describe(e)));
            }
        }).start();
    }

    private void render(LecturerDashboard data) {
        statusLabel.setText(Router.api().isOffline() ? "Offline — showing saved data." : "");

        LecturerDashboard.Stats s = data.stats;
        statsBox.getChildren().setAll(
                statCard("Quizzes", String.valueOf(s.quizzes), s.publishedThisWeek + " published this week"),
                statCard("Total Lectures", String.valueOf(s.totalTopics), "Assigned to you"),
                statCard("Students", String.valueOf(s.students), "Registered on the platform"),
                statCard("Upcoming Classes", String.valueOf(s.upcomingClasses), s.nextClassScheduledAt != null ? "Next class scheduled" : "None scheduled")
        );

        quizManagementBox.getChildren().setAll(
                listRow(s.activeQuizzes + " active quizzes", s.publishedThisWeek + " published this week", "Ready", "app-tag-green")
        );

        questionsBox.getChildren().clear();
        if (data.unansweredQuestionsCount > 0) {
            questionsBox.getChildren().add(
                    listRow(data.unansweredQuestionsCount + " question(s) awaiting an answer", "Help by replying in the Discussion Forum.", "Reminder", "app-tag-orange"));
        }
        List<LecturerDashboard.RecentQuestion> questions = data.recentQuestions;
        if (questions == null || questions.isEmpty()) {
            questionsBox.getChildren().add(listRow("No questions yet", "Questions from students will appear here.", null, null));
        } else {
            for (LecturerDashboard.RecentQuestion q : questions) {
                String sub = (q.topicTitle != null ? q.topicTitle : "Other") + " · asked by " + q.userName;
                if (q.answersCount > 0) {
                    questionsBox.getChildren().add(listRow(q.title, sub, "Answered", "app-tag-green"));
                } else {
                    questionsBox.getChildren().add(listRow(q.title, sub, "Not answered", "app-tag-orange"));
                }
            }
        }

        quizStatusBox.getChildren().clear();
        List<LecturerDashboard.QuizStatusRow> rows = data.quizzesByStatus;
        if (rows != null) {
            for (LecturerDashboard.QuizStatusRow row : rows) {
                quizStatusBox.getChildren().add(barRow(row.label, row.total, row.pct));
            }
        }

        performanceBox.getChildren().clear();
        if (s.averageScorePercent != null) {
            boolean strong = s.averageScorePercent >= 70;
            performanceBox.getChildren().add(listRow(
                    "Avg. score " + s.averageScorePercent + "%",
                    "Across your quizzes' attempts",
                    strong ? "Strong" : "Needs attention",
                    strong ? "app-tag-green" : "app-tag-orange"));
        } else {
            performanceBox.getChildren().add(listRow("No attempts yet", "Scores will appear once students take your quizzes.", null, null));
        }

        LecturerDashboard.DiscussionStats ds = data.discussionStats;
        discussionsBox.getChildren().setAll(
                listRow(ds.newThreadsThisWeek + " new thread(s) this week", ds.unresolvedCount + " unresolved question(s)",
                        ds.unresolvedCount > 0 ? "Active" : "Clear", ds.unresolvedCount > 0 ? "app-tag-orange" : "app-tag-green"),
                listRow(ds.participantsCount + " participant(s)",
                        ds.topTopicTitle != null ? "Top topic: " + ds.topTopicTitle : "No activity yet", null, null)
        );

        LecturerDashboard.ParticipationCriteria pc = data.participationCriteria;
        pointsPerQuestionField.setText(String.valueOf(pc.pointsPerQuestion));
        pointsPerAnswerField.setText(String.valueOf(pc.pointsPerAnswer));
        pointsPerLikeField.setText(String.valueOf(pc.pointsPerLikeReceived));
        targetPointsField.setText(String.valueOf(pc.targetPoints));
        criteriaStatusLabel.setText("");
    }

    private VBox statCard(String label, String value, String sub) {
        Label valueLabel = new Label(value);
        valueLabel.getStyleClass().add("app-stat-value");
        Label textLabel = new Label(label);
        textLabel.getStyleClass().add("app-stat-label");
        Label subLabel = new Label(sub);
        subLabel.getStyleClass().add("app-stat-label");
        VBox card = new VBox(2, valueLabel, textLabel, subLabel);
        card.getStyleClass().add("app-stat-card");
        return card;
    }

    private HBox listRow(String title, String sub, String tagText, String tagStyleClass) {
        Label titleLabel = new Label(title);
        titleLabel.setStyle("-fx-font-weight: bold;");
        Label subLabel = new Label(sub);
        subLabel.setStyle("-fx-text-fill: #71717a; -fx-font-size: 11px;");
        VBox textBox = new VBox(2, titleLabel, subLabel);

        HBox row = new HBox(10, textBox);
        row.setAlignment(Pos.CENTER_LEFT);
        row.getStyleClass().add("app-list-row");
        row.setPadding(new Insets(8));

        if (tagText != null) {
            Region spacer = new Region();
            HBox.setHgrow(spacer, Priority.ALWAYS);
            Label tag = new Label(tagText);
            tag.getStyleClass().addAll("app-tag", tagStyleClass);
            row.getChildren().addAll(spacer, tag);
        }

        return row;
    }

    private VBox barRow(String label, long total, int pct) {
        Label labelNode = new Label(label);
        labelNode.setStyle("-fx-font-size: 12px;");

        Region fill = new Region();
        fill.getStyleClass().add("app-bar-fill");
        if (total > 0) {
            fill.getStyleClass().add("has-value");
        }
        fill.setPrefWidth(Math.max(0, Math.min(100, pct)) * 2.0);
        fill.setMaxWidth(Region.USE_PREF_SIZE);

        Region track = new Region();
        track.getStyleClass().add("app-bar-track");
        track.setPrefWidth(200);

        javafx.scene.layout.StackPane barStack = new javafx.scene.layout.StackPane(track, fill);
        barStack.setAlignment(Pos.CENTER_LEFT);

        Label valueLabel = new Label(String.valueOf(total));
        valueLabel.setStyle("-fx-font-size: 12px; -fx-font-weight: bold;");

        HBox row = new HBox(10, labelNode, barStack, valueLabel);
        row.setAlignment(Pos.CENTER_LEFT);
        HBox.setHgrow(barStack, Priority.ALWAYS);

        return new VBox(row);
    }

    private Integer parseIntOrNull(String text) {
        try {
            return Integer.parseInt(text.trim());
        } catch (Exception e) {
            return null;
        }
    }

    private String describe(Exception e) {
        String message = e.getMessage();
        return message == null || message.isBlank() ? e.getClass().getSimpleName() : message;
    }
}
