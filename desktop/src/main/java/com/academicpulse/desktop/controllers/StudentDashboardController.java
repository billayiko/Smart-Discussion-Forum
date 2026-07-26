package com.academicpulse.desktop.controllers;

import com.academicpulse.desktop.Router;
import com.academicpulse.desktop.model.StudentDashboard;
import javafx.application.Platform;
import javafx.fxml.FXML;
import javafx.geometry.Insets;
import javafx.geometry.Pos;
import javafx.scene.control.Label;
import javafx.scene.layout.FlowPane;
import javafx.scene.layout.HBox;
import javafx.scene.layout.Priority;
import javafx.scene.layout.Region;
import javafx.scene.layout.StackPane;
import javafx.scene.layout.VBox;

import java.time.LocalTime;
import java.util.List;

/** Student's main dashboard — mirrors pages/dashboards/student.blade.php. */
public class StudentDashboardController {
    @FXML private Label greetingLabel;
    @FXML private Label statusLabel;
    @FXML private FlowPane statsBox;
    @FXML private VBox quizLoadBox;
    @FXML private VBox answeredRateBox;
    @FXML private VBox upcomingQuizzesBox;
    @FXML private VBox discussionForumBox;
    @FXML private VBox announcementsBox;

    @FXML
    public void initialize() {
        var user = Router.currentUser();
        String firstName = user != null && user.name != null ? user.name.trim().split(" ")[0] : "Student";
        int hour = LocalTime.now().getHour();
        String greeting = hour < 12 ? "Good morning" : hour < 17 ? "Good afternoon" : "Good evening";
        greetingLabel.setText(greeting + ", " + firstName);

        load();
    }

    @FXML
    private void handleRefresh() {
        load();
    }

    private void load() {
        statusLabel.setText("Loading...");

        new Thread(() -> {
            try {
                StudentDashboard data = Router.api().getStudentDashboard();
                Platform.runLater(() -> render(data));
            } catch (Exception e) {
                Platform.runLater(() -> statusLabel.setText("Failed to load dashboard: " + describe(e)));
            }
        }).start();
    }

    private void render(StudentDashboard data) {
        statusLabel.setText(Router.api().isOffline() ? "Offline — showing saved data." : "");

        StudentDashboard.Stats s = data.stats;
        statsBox.getChildren().setAll(
                statCard("Enrolled Lectures", String.valueOf(s.enrolledLectures), s.newSubscriptionsThisWeek + " new this week"),
                statCard("Quizzes", String.valueOf(s.quizzes), s.upcomingClasses + " upcoming"),
                statCard("Upcoming Classes", String.valueOf(s.upcomingClasses), s.nextClassScheduledAt != null ? "Next class scheduled" : "None scheduled"),
                statCard("Average Grade", s.averageGradePercent != null ? s.averageGradePercent + "%" : "—", s.gradedQuizCount + " quiz(zes) graded")
        );

        quizLoadBox.getChildren().clear();
        List<StudentDashboard.SubjectRow> subjects = data.quizzesBySubject;
        if (subjects == null || subjects.isEmpty()) {
            quizLoadBox.getChildren().add(new Label("No quizzes published yet."));
        } else {
            for (StudentDashboard.SubjectRow row : subjects) {
                quizLoadBox.getChildren().add(barRow(row.subject, row.total, row.pct));
            }
        }
        answeredRateBox.getChildren().setAll(progressBar(data.answeredRate));

        upcomingQuizzesBox.getChildren().clear();
        List<StudentDashboard.UpcomingQuiz> quizzes = data.upcomingQuizzes;
        if (quizzes == null || quizzes.isEmpty()) {
            upcomingQuizzesBox.getChildren().add(listRow("No upcoming quizzes", "New quiz activity will appear here.", null, null));
        } else {
            for (StudentDashboard.UpcomingQuiz quiz : quizzes) {
                String sub = quiz.subject + " · " + quiz.durationMinutes + " mins";
                String tag = quiz.hasStarted ? "View report" : "Scheduled";
                HBox row = listRow(quiz.title, sub, tag, quiz.hasStarted ? "app-tag-purple" : "app-tag-gray");
                if (quiz.hasStarted) {
                    row.setOnMouseClicked(e -> openResult(quiz.id, quiz.title));
                }
                upcomingQuizzesBox.getChildren().add(row);
            }
        }

        discussionForumBox.getChildren().clear();
        if (data.unansweredQuestionsCount > 0) {
            discussionForumBox.getChildren().add(
                    listRow(data.unansweredQuestionsCount + " question(s) awaiting an answer", "Help by replying in the Discussion Forum.", "Reminder", "app-tag-orange"));
        }
        List<StudentDashboard.RecentQuestion> questions = data.recentQuestions;
        if (questions == null || questions.isEmpty()) {
            discussionForumBox.getChildren().add(listRow("No questions yet", "Be the first to ask one.", null, null));
        } else {
            for (StudentDashboard.RecentQuestion q : questions) {
                String sub = (q.topicTitle != null ? q.topicTitle : "Other") + " · asked by " + q.userName;
                if (q.answersCount > 0) {
                    discussionForumBox.getChildren().add(listRow(q.title, sub, "Answered", "app-tag-green"));
                } else {
                    discussionForumBox.getChildren().add(listRow(q.title, sub, "Not answered", "app-tag-orange"));
                }
            }
        }

        announcementsBox.getChildren().clear();
        List<StudentDashboard.UpcomingQuiz> announcements = data.upcomingQuizAnnouncements;
        if (announcements == null || announcements.isEmpty()) {
            announcementsBox.getChildren().add(listRow("No announcements", "Upcoming quizzes will appear here.", null, null));
        } else {
            for (StudentDashboard.UpcomingQuiz a : announcements) {
                String sub = a.subject + " · " + a.durationMinutes + " mins";
                announcementsBox.getChildren().add(listRow("Upcoming quiz: " + a.title, sub, "Soon", "app-tag-orange"));
            }
        }
    }

    private void openResult(long quizId, String title) {
        try {
            QuizResultController controller = Router.navigate("/quiz-result.fxml", "Academic Pulse - " + title + " Result");
            controller.setQuizId(quizId);
        } catch (Exception e) {
            statusLabel.setText("Failed to open result screen: " + describe(e));
        }
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

        StackPane barStack = progressBar(pct, total > 0);

        Label valueLabel = new Label(String.valueOf(total));
        valueLabel.setStyle("-fx-font-size: 12px; -fx-font-weight: bold;");

        HBox row = new HBox(10, labelNode, barStack, valueLabel);
        row.setAlignment(Pos.CENTER_LEFT);
        HBox.setHgrow(barStack, Priority.ALWAYS);

        return new VBox(row);
    }

    private StackPane progressBar(int pct) {
        return progressBar(pct, pct > 0);
    }

    private StackPane progressBar(int pct, boolean hasValue) {
        Region fill = new Region();
        fill.getStyleClass().add("app-bar-fill");
        if (hasValue) {
            fill.getStyleClass().add("has-value");
        }
        fill.setPrefWidth(Math.max(0, Math.min(100, pct)) * 2.0);
        fill.setMaxWidth(Region.USE_PREF_SIZE);

        Region track = new Region();
        track.getStyleClass().add("app-bar-track");
        track.setPrefWidth(200);

        StackPane stack = new StackPane(track, fill);
        stack.setAlignment(Pos.CENTER_LEFT);
        return stack;
    }

    private String describe(Exception e) {
        String message = e.getMessage();
        return message == null || message.isBlank() ? e.getClass().getSimpleName() : message;
    }
}
