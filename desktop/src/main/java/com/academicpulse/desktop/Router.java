package com.academicpulse.desktop;

import com.academicpulse.desktop.api.ApiClient;
import com.academicpulse.desktop.controllers.QuizTakeController;
import com.academicpulse.desktop.model.LiveQuizStatus;
import com.academicpulse.desktop.model.User;
import javafx.application.Platform;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.stage.Stage;

import java.io.IOException;
import java.util.concurrent.Executors;
import java.util.concurrent.ScheduledExecutorService;
import java.util.concurrent.TimeUnit;

/** Owns the primary stage, the shared {@link ApiClient}, and the logged-in user for the app's lifetime. */
public final class Router {
    private static final long LIVE_QUIZ_POLL_SECONDS = 15;

    private static final ApiClient API_CLIENT = new ApiClient();
    private static Stage stage;
    private static User currentUser;
    private static ScheduledExecutorService liveQuizWatcher;
    private static volatile boolean quizTakeActive = false;

    private Router() {
    }

    public static void init(Stage primaryStage) {
        stage = primaryStage;
    }

    public static ApiClient api() {
        return API_CLIENT;
    }

    public static Stage stage() {
        return stage;
    }

    public static User currentUser() {
        return currentUser;
    }

    public static void setCurrentUser(User user) {
        currentUser = user;
    }

    /** Loads the given FXML into the primary stage and returns its controller. */
    public static <T> T navigate(String fxmlPath, String title) throws IOException {
        FXMLLoader loader = new FXMLLoader(Router.class.getResource(fxmlPath));
        Parent root = loader.load();
        Scene scene = new Scene(root, 1080, 640);
        scene.getStylesheets().add(Router.class.getResource("/app.css").toExternalForm());
        stage.setScene(scene);
        stage.setTitle(title);
        return loader.getController();
    }

    /**
     * While true, the background live-quiz watcher (see {@link #startLiveQuizWatch()})
     * won't interrupt with another navigation — set by {@link QuizTakeController}
     * for as long as it's the active screen, mirroring the web's excluded-routes
     * list on {@code RedirectToLiveQuiz} (it never redirects away from the
     * quiz-taking page itself).
     */
    public static void setQuizTakeActive(boolean active) {
        quizTakeActive = active;
    }

    /**
     * Starts a background poll for a quiz going live for the current
     * student, interrupting with the quiz-take screen the moment one is
     * found — from whatever screen the student happens to be on. This is
     * the desktop equivalent of the web's combination of a site-wide
     * {@code RedirectToLiveQuiz} middleware and a client-side countdown
     * timer that together "pop up" a quiz the instant it starts.
     */
    public static void startLiveQuizWatch() {
        stopLiveQuizWatch();
        liveQuizWatcher = Executors.newSingleThreadScheduledExecutor(r -> {
            Thread t = new Thread(r, "live-quiz-watch");
            t.setDaemon(true);
            return t;
        });
        liveQuizWatcher.scheduleWithFixedDelay(Router::pollLiveQuiz, 0, LIVE_QUIZ_POLL_SECONDS, TimeUnit.SECONDS);
    }

    public static void stopLiveQuizWatch() {
        if (liveQuizWatcher != null) {
            liveQuizWatcher.shutdownNow();
            liveQuizWatcher = null;
        }
    }

    private static void pollLiveQuiz() {
        if (quizTakeActive || currentUser == null) {
            return;
        }
        LiveQuizStatus status = API_CLIENT.getLiveQuizStatusOrNull();
        if (status == null || status.quiz == null) {
            return;
        }
        long quizId = status.quiz.id;
        Platform.runLater(() -> {
            if (quizTakeActive) {
                return;
            }
            try {
                QuizTakeController controller = navigate("/quiz-take.fxml", "Academic Pulse - " + status.quiz.title);
                controller.setQuizId(quizId);
            } catch (IOException ignored) {
                // the next poll tick will retry
            }
        });
    }
}
