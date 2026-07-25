package com.academicpulse.desktop.api;

import com.academicpulse.desktop.cache.LocalCache;
import com.academicpulse.desktop.model.AdminComplaint;
import com.academicpulse.desktop.model.AdminDashboard;
import com.academicpulse.desktop.model.AdminMembersData;
import com.academicpulse.desktop.model.AdminTopicsData;
import com.academicpulse.desktop.model.AnalyticsSummary;
import com.academicpulse.desktop.model.Answer;
import com.academicpulse.desktop.model.Conversation;
import com.academicpulse.desktop.model.LecturerDashboard;
import com.academicpulse.desktop.model.LecturerMark;
import com.academicpulse.desktop.model.LecturerStudentsData;
import com.academicpulse.desktop.model.LiveQuizStatus;
import com.academicpulse.desktop.model.Question;
import com.academicpulse.desktop.model.QuizBuilderData;
import com.academicpulse.desktop.model.QuizEditData;
import com.academicpulse.desktop.model.QuizResultData;
import com.academicpulse.desktop.model.QuizTakeData;
import com.academicpulse.desktop.model.QuizzesData;
import com.academicpulse.desktop.model.StudentDashboard;
import com.academicpulse.desktop.model.Topic;
import com.academicpulse.desktop.model.TopicAnalytics;
import com.academicpulse.desktop.model.User;
import com.fasterxml.jackson.annotation.JsonIgnoreProperties;
import com.fasterxml.jackson.core.type.TypeReference;
import com.fasterxml.jackson.databind.DeserializationFeature;
import com.fasterxml.jackson.databind.JsonNode;
import com.fasterxml.jackson.databind.ObjectMapper;
import com.fasterxml.jackson.databind.PropertyNamingStrategies;

import java.io.IOException;
import java.net.URI;
import java.net.URLEncoder;
import java.net.http.HttpClient;
import java.net.http.HttpRequest;
import java.net.http.HttpResponse;
import java.nio.charset.StandardCharsets;
import java.sql.SQLException;
import java.time.Duration;
import java.util.List;
import java.util.Map;

/**
 * Talks to the Laravel API (routes/api.php) over HTTP using Sanctum bearer
 * tokens. Point {@code baseUrl} at wherever `php artisan serve` (or the
 * deployed app) is running, with an /api suffix.
 *
 * <p>Read (GET) calls are backed by a {@link LocalCache}: a successful
 * response is cached, and a genuine connectivity failure (as opposed to a
 * real non-2xx server response) falls back to whatever was last cached, so
 * the app stays browsable offline. Write calls are not cached — composing
 * new content still requires connectivity.</p>
 */
public class ApiClient {
    private static final String DEFAULT_BASE_URL = "http://127.0.0.1:8000/api";

    private final String baseUrl;
    private final HttpClient httpClient;
    private final ObjectMapper mapper;
    private final LocalCache cache;
    private String token;
    private boolean offline;

    public ApiClient() {
        this(DEFAULT_BASE_URL);
    }

    public ApiClient(String baseUrl) {
        this.baseUrl = baseUrl;
        this.httpClient = HttpClient.newBuilder()
                .connectTimeout(Duration.ofSeconds(10))
                .build();
        this.mapper = new ObjectMapper()
                .setPropertyNamingStrategy(PropertyNamingStrategies.SNAKE_CASE)
                .configure(DeserializationFeature.FAIL_ON_UNKNOWN_PROPERTIES, false);
        this.cache = createCache();
    }

    private static LocalCache createCache() {
        try {
            return new LocalCache();
        } catch (SQLException e) {
            // Caching is a nice-to-have for offline browsing; if it can't be
            // set up (e.g. a locked/unwritable profile directory), the app
            // should keep working online-only rather than fail to start.
            return null;
        }
    }

    public boolean isLoggedIn() {
        return token != null;
    }

    /** Whether the most recent GET fell back to cached data due to a connectivity failure. */
    public boolean isOffline() {
        return offline;
    }

    /**
     * Mirrors the web app's registration form exactly (role, security
     * question/answer, rules agreement) — see Api\AuthController::register.
     */
    public User register(String name, String email, String password, String passwordConfirmation,
                          String role, String securityQuestion, String securityAnswer)
            throws ApiException, IOException, InterruptedException {
        Map<String, Object> payload = new java.util.HashMap<>();
        payload.put("name", name);
        payload.put("email", email);
        payload.put("password", password);
        payload.put("password_confirmation", passwordConfirmation);
        payload.put("role", role);
        payload.put("rules_agreement", true);
        payload.put("security_question", securityQuestion);
        payload.put("security_answer", securityAnswer);

        ApiResponse response = send("POST", "/register", payload, false);
        requireSuccess(response);

        LoginResponse parsed = mapper.readValue(response.body(), LoginResponse.class);
        this.token = parsed.accessToken;
        return parsed.user;
    }

    public User login(String email, String password) throws ApiException, IOException, InterruptedException {
        Map<String, String> payload = Map.of("email", email, "password", password);
        ApiResponse response = send("POST", "/login", payload, false);
        requireSuccess(response);

        LoginResponse parsed = mapper.readValue(response.body(), LoginResponse.class);
        this.token = parsed.accessToken;
        return parsed.user;
    }

    public void logout() throws ApiException, IOException, InterruptedException {
        try {
            requireSuccess(send("POST", "/logout", null, true));
        } finally {
            this.token = null;
        }
    }

    public User me() throws ApiException, IOException, InterruptedException {
        ApiResponse response = send("GET", "/me", null, true);
        requireSuccess(response);
        return mapper.readValue(response.body(), User.class);
    }

    public List<Topic> getTopics() throws ApiException, InterruptedException {
        return fetchCached("topics", "/topics", new TypeReference<List<Topic>>() {});
    }

    public List<Question> getTopicQuestions(long topicId) throws ApiException, InterruptedException {
        return fetchCached("topic:" + topicId + ":questions", "/topics/" + topicId + "/questions", new TypeReference<List<Question>>() {});
    }

    public Question getQuestion(long questionId) throws ApiException, InterruptedException {
        return fetchCached("question:" + questionId, "/questions/" + questionId, Question.class);
    }

    public Question createQuestion(long topicId, String title, String body) throws ApiException, IOException, InterruptedException {
        Map<String, Object> payload = Map.of("title", title, "body", body, "course_topic_id", topicId);
        ApiResponse response = send("POST", "/questions", payload, true);
        requireSuccess(response);
        return mapper.readValue(response.body(), Question.class);
    }

    public Answer createAnswer(long questionId, String body) throws ApiException, IOException, InterruptedException {
        Map<String, String> payload = Map.of("body", body);
        ApiResponse response = send("POST", "/questions/" + questionId + "/answers", payload, true);
        requireSuccess(response);
        return mapper.readValue(response.body(), Answer.class);
    }

    public List<Conversation> getConversations() throws ApiException, InterruptedException {
        return fetchCached("conversations", "/conversations", new TypeReference<List<Conversation>>() {});
    }

    public Conversation getConversation(long conversationId) throws ApiException, InterruptedException {
        return fetchCached("conversation:" + conversationId, "/conversations/" + conversationId, Conversation.class);
    }

    public void sendMessage(long conversationId, String body) throws ApiException, IOException, InterruptedException {
        Map<String, String> payload = Map.of("body", body);
        requireSuccess(send("POST", "/conversations/" + conversationId + "/messages", payload, true));
    }

    public Conversation startConversation(long userId) throws ApiException, IOException, InterruptedException {
        Map<String, Object> payload = Map.of("user_id", userId);
        ApiResponse response = send("POST", "/conversations/start", payload, true);
        requireSuccess(response);
        return mapper.readValue(response.body(), Conversation.class);
    }

    public List<User> getContacts() throws ApiException, InterruptedException {
        return fetchCached("contacts", "/conversation-contacts", new TypeReference<List<User>>() {});
    }

    /** Admin-only; the server returns 403 for any other role. */
    public AnalyticsSummary getAnalytics() throws ApiException, InterruptedException {
        return fetchCached("analytics", "/analytics", AnalyticsSummary.class);
    }

    /** Admin-only; the server returns 403 for any other role. */
    public TopicAnalytics getTopicAnalytics(long topicId) throws ApiException, InterruptedException {
        return fetchCached("analytics:topic:" + topicId, "/analytics/topics/" + topicId, TopicAnalytics.class);
    }

    /**
     * Admin-only; the server returns 403 for any other role. {@code status}
     * (one of "published"/"draft"/"scheduled") and {@code search} are both
     * optional — pass null to omit either filter.
     */
    public AdminDashboard getAdminDashboard(String status, String search) throws ApiException, InterruptedException {
        StringBuilder path = new StringBuilder("/admin/dashboard");
        StringBuilder cacheKey = new StringBuilder("admin-dashboard");
        String separator = "?";

        if (status != null && !status.isBlank()) {
            path.append(separator).append("status=").append(URLEncoder.encode(status, StandardCharsets.UTF_8));
            cacheKey.append(":status=").append(status);
            separator = "&";
        }
        if (search != null && !search.isBlank()) {
            path.append(separator).append("q=").append(URLEncoder.encode(search, StandardCharsets.UTF_8));
            cacheKey.append(":q=").append(search);
        }

        return fetchCached(cacheKey.toString(), path.toString(), AdminDashboard.class);
    }

    // ---- Admin: Topics ----

    public AdminTopicsData getAdminTopics() throws ApiException, InterruptedException {
        return fetchCached("admin-topics", "/admin/topics", AdminTopicsData.class);
    }

    public void createTopic(String title, String description, Long lecturerId) throws ApiException, IOException, InterruptedException {
        Map<String, Object> payload = new java.util.HashMap<>();
        payload.put("title", title);
        payload.put("description", description);
        payload.put("lecturer_id", lecturerId);
        requireSuccess(send("POST", "/admin/topics", payload, true));
    }

    public void assignTopicLecturer(long topicId, Long lecturerId) throws ApiException, IOException, InterruptedException {
        Map<String, Object> payload = new java.util.HashMap<>();
        payload.put("lecturer_id", lecturerId);
        requireSuccess(send("PATCH", "/admin/topics/" + topicId + "/assign", payload, true));
    }

    public void deleteTopic(long topicId) throws ApiException, IOException, InterruptedException {
        requireSuccess(send("DELETE", "/admin/topics/" + topicId, null, true));
    }

    // ---- Admin: Complaints ----

    public List<AdminComplaint> getAdminComplaints() throws ApiException, InterruptedException {
        return fetchCached("admin-complaints", "/admin/complaints", new TypeReference<List<AdminComplaint>>() {});
    }

    public void resolveComplaint(long complaintId, String action) throws ApiException, IOException, InterruptedException {
        Map<String, String> payload = Map.of("action", action);
        requireSuccess(send("PATCH", "/admin/complaints/" + complaintId, payload, true));
    }

    // ---- Admin: Members ----

    public AdminMembersData getAdminMembers() throws ApiException, InterruptedException {
        return fetchCached("admin-members", "/admin/members", AdminMembersData.class);
    }

    public void updateModerationSettings(int inactivityThresholdDays, int complianceDays, int blacklistDurationDays)
            throws ApiException, IOException, InterruptedException {
        Map<String, Object> payload = Map.of(
                "inactivity_threshold_days", inactivityThresholdDays,
                "compliance_days", complianceDays,
                "blacklist_duration_days", blacklistDurationDays);
        requireSuccess(send("PATCH", "/admin/members/settings", payload, true));
    }

    public void updateMemberRole(long memberId, String role) throws ApiException, IOException, InterruptedException {
        requireSuccess(send("PATCH", "/admin/members/" + memberId + "/role", Map.of("role", role), true));
    }

    public void warnMember(long memberId) throws ApiException, IOException, InterruptedException {
        requireSuccess(send("POST", "/admin/members/" + memberId + "/warn", null, true));
    }

    public void blacklistMember(long memberId) throws ApiException, IOException, InterruptedException {
        requireSuccess(send("POST", "/admin/members/" + memberId + "/blacklist", null, true));
    }

    public void unblacklistMember(long memberId) throws ApiException, IOException, InterruptedException {
        requireSuccess(send("POST", "/admin/members/" + memberId + "/unblacklist", null, true));
    }

    // ---- Lecturer ----

    public LecturerDashboard getLecturerDashboard() throws ApiException, InterruptedException {
        return fetchCached("lecturer-dashboard", "/lecturer/dashboard", LecturerDashboard.class);
    }

    public void updateParticipationCriteria(int pointsPerQuestion, int pointsPerAnswer, int pointsPerLikeReceived, int targetPoints)
            throws ApiException, IOException, InterruptedException {
        Map<String, Object> payload = Map.of(
                "points_per_question", pointsPerQuestion,
                "points_per_answer", pointsPerAnswer,
                "points_per_like_received", pointsPerLikeReceived,
                "target_points", targetPoints);
        requireSuccess(send("PATCH", "/lecturer/participation-criteria", payload, true));
    }

    public LecturerStudentsData getLecturerStudents() throws ApiException, InterruptedException {
        return fetchCached("lecturer-students", "/lecturer/students", LecturerStudentsData.class);
    }

    public List<LecturerMark> getLecturerMarks() throws ApiException, InterruptedException {
        return fetchCached("lecturer-marks", "/lecturer/marks", new TypeReference<List<LecturerMark>>() {});
    }

    // ---- Student ----

    public StudentDashboard getStudentDashboard() throws ApiException, InterruptedException {
        return fetchCached("student-dashboard", "/student/dashboard", StudentDashboard.class);
    }

    // ---- Quizzes ----

    public QuizzesData getQuizzes() throws ApiException, InterruptedException {
        return fetchCached("quizzes", "/quizzes", QuizzesData.class);
    }

    /** All course topics, for the create/edit quiz form's dropdown. */
    public List<Topic> getQuizTopics() throws ApiException, InterruptedException {
        return fetchCached("quiz-topics", "/quizzes/topics", new TypeReference<List<Topic>>() {});
    }

    /** Returns the new quiz's id, so the caller can jump straight to its question builder. */
    public long createQuiz(String title, String subject, int totalQuestions, String scheduledAt,
                            int durationMinutes, String status, Long courseTopicId, boolean proctored)
            throws ApiException, IOException, InterruptedException {
        Map<String, Object> payload = new java.util.HashMap<>();
        payload.put("title", title);
        payload.put("subject", subject);
        payload.put("total_questions", totalQuestions);
        payload.put("scheduled_at", scheduledAt);
        payload.put("duration_minutes", durationMinutes);
        payload.put("status", status);
        payload.put("course_topic_id", courseTopicId);
        payload.put("proctored", proctored);

        ApiResponse response = send("POST", "/quizzes", payload, true);
        requireSuccess(response);
        JsonNode node = mapper.readTree(response.body());
        return node.get("id").asLong();
    }

    public QuizEditData getQuizEdit(long quizId) throws ApiException, InterruptedException {
        return fetchCached("quiz-edit:" + quizId, "/quizzes/" + quizId + "/edit", QuizEditData.class);
    }

    public void updateQuiz(long quizId, String title, String subject, int totalQuestions, String scheduledAt,
                            int durationMinutes, String status, Long courseTopicId, boolean proctored)
            throws ApiException, IOException, InterruptedException {
        Map<String, Object> payload = new java.util.HashMap<>();
        payload.put("title", title);
        payload.put("subject", subject);
        payload.put("total_questions", totalQuestions);
        payload.put("scheduled_at", scheduledAt);
        payload.put("duration_minutes", durationMinutes);
        payload.put("status", status);
        payload.put("course_topic_id", courseTopicId);
        payload.put("proctored", proctored);
        requireSuccess(send("PATCH", "/quizzes/" + quizId, payload, true));
    }

    public QuizBuilderData getQuizBuilder(long quizId) throws ApiException, InterruptedException {
        return fetchCached("quiz-builder:" + quizId, "/quizzes/" + quizId + "/questions", QuizBuilderData.class);
    }

    public void addQuizQuestion(long quizId, String question, String optionA, String optionB, String optionC,
                                 String optionD, String correctOption) throws ApiException, IOException, InterruptedException {
        Map<String, String> payload = new java.util.HashMap<>();
        payload.put("question", question);
        payload.put("option_a", optionA);
        payload.put("option_b", optionB);
        payload.put("option_c", optionC);
        payload.put("option_d", optionD);
        payload.put("correct_option", correctOption);
        requireSuccess(send("POST", "/quizzes/" + quizId + "/questions", payload, true));
    }

    public void deleteQuizQuestion(long quizId, long questionId) throws ApiException, IOException, InterruptedException {
        requireSuccess(send("DELETE", "/quizzes/" + quizId + "/questions/" + questionId, null, true));
    }

    public void finalizeQuiz(long quizId) throws ApiException, IOException, InterruptedException {
        requireSuccess(send("POST", "/quizzes/" + quizId + "/finalize", null, true));
    }

    /**
     * Fails with a 409 ({@link ApiException#statusCode}) if the student has
     * already submitted this quiz — the caller should send them to the
     * result screen instead of retrying.
     */
    public QuizTakeData getQuizTake(long quizId) throws ApiException, IOException, InterruptedException {
        ApiResponse response = send("GET", "/quizzes/" + quizId + "/take", null, true);
        requireSuccess(response);
        return mapper.readValue(response.body(), QuizTakeData.class);
    }

    public void submitQuiz(long quizId, Map<Long, String> answers, int violations)
            throws ApiException, IOException, InterruptedException {
        Map<String, Object> payload = new java.util.HashMap<>();
        Map<String, String> answerPayload = new java.util.HashMap<>();
        answers.forEach((questionId, letter) -> answerPayload.put(String.valueOf(questionId), letter));
        payload.put("answers", answerPayload);
        payload.put("violations", violations);
        requireSuccess(send("POST", "/quizzes/" + quizId + "/submit", payload, true));
    }

    /**
     * Fails with a 409 ({@link ApiException#statusCode}) if a student who
     * hasn't attempted yet asks for the result of a quiz that's still live —
     * the caller should send them to take it instead.
     */
    public QuizResultData getQuizResult(long quizId) throws ApiException, IOException, InterruptedException {
        ApiResponse response = send("GET", "/quizzes/" + quizId + "/result", null, true);
        requireSuccess(response);
        return mapper.readValue(response.body(), QuizResultData.class);
    }

    public void confirmQuizMarks(long quizId) throws ApiException, IOException, InterruptedException {
        requireSuccess(send("POST", "/quizzes/" + quizId + "/confirm-marks", null, true));
    }

    /**
     * Polls whether a quiz is live for the current student right now.
     * Deliberately uncached and swallows all failures (returning null) —
     * this backs a frequent background poller that should stay silent
     * through a transient connectivity blip rather than surface an error
     * or, worse, act on stale cached data and pop up a quiz that's no
     * longer actually live.
     */
    public LiveQuizStatus getLiveQuizStatusOrNull() {
        try {
            ApiResponse response = send("GET", "/student/live-quiz", null, true);
            requireSuccess(response);
            return mapper.readValue(response.body(), LiveQuizStatus.class);
        } catch (Exception e) {
            return null;
        }
    }

    // ---- Settings ----

    public User updateProfile(String name, String email) throws ApiException, IOException, InterruptedException {
        Map<String, String> payload = Map.of("name", name, "email", email);
        ApiResponse response = send("PATCH", "/me", payload, true);
        requireSuccess(response);
        return mapper.readValue(response.body(), User.class);
    }

    public void changePassword(String currentPassword, String newPassword, String confirmPassword)
            throws ApiException, IOException, InterruptedException {
        Map<String, String> payload = Map.of(
                "current_password", currentPassword,
                "password", newPassword,
                "password_confirmation", confirmPassword);
        requireSuccess(send("PUT", "/password", payload, true));
    }

    /**
     * Performs a GET, caching the raw response on success. On a genuine
     * connectivity failure (the request never got a response at all, as
     * opposed to {@link ApiException} which {@link #requireSuccess} throws
     * for a real non-2xx server response and which must keep propagating
     * normally), falls back to the last cached value for this key if one
     * exists.
     */
    private <T> T fetchCached(String cacheKey, String path, Class<T> type) throws ApiException, InterruptedException {
        try {
            ApiResponse response = send("GET", path, null, true);
            requireSuccess(response);
            offline = false;
            if (cache != null) {
                cache.put(cacheKey, response.body());
            }
            return mapper.readValue(response.body(), type);
        } catch (IOException networkFailure) {
            return readFromCacheOrThrow(cacheKey, type);
        }
    }

    private <T> T fetchCached(String cacheKey, String path, TypeReference<T> type) throws ApiException, InterruptedException {
        try {
            ApiResponse response = send("GET", path, null, true);
            requireSuccess(response);
            offline = false;
            if (cache != null) {
                cache.put(cacheKey, response.body());
            }
            return mapper.readValue(response.body(), type);
        } catch (IOException networkFailure) {
            return readFromCacheOrThrow(cacheKey, type);
        }
    }

    private <T> T readFromCacheOrThrow(String cacheKey, Class<T> type) throws ApiException {
        offline = true;
        String cached = cache == null ? null : cache.get(cacheKey).orElse(null);
        if (cached == null) {
            throw new ApiException("No internet connection and no saved data available yet.", 0);
        }
        try {
            return mapper.readValue(cached, type);
        } catch (IOException corrupted) {
            throw new ApiException("No internet connection and no saved data available yet.", 0);
        }
    }

    private <T> T readFromCacheOrThrow(String cacheKey, TypeReference<T> type) throws ApiException {
        offline = true;
        String cached = cache == null ? null : cache.get(cacheKey).orElse(null);
        if (cached == null) {
            throw new ApiException("No internet connection and no saved data available yet.", 0);
        }
        try {
            return mapper.readValue(cached, type);
        } catch (IOException corrupted) {
            throw new ApiException("No internet connection and no saved data available yet.", 0);
        }
    }

    private void requireSuccess(ApiResponse response) throws ApiException {
        if (response.status() >= 200 && response.status() < 300) {
            return;
        }
        throw new ApiException(extractErrorMessage(response.body()), response.status());
    }

    private String extractErrorMessage(String body) {
        try {
            JsonNode node = mapper.readTree(body);
            if (node.has("message")) {
                return node.get("message").asText();
            }
            if (node.has("errors")) {
                return node.get("errors").toString();
            }
        } catch (IOException ignored) {
            // fall through to raw body below
        }
        return body == null || body.isBlank() ? "Request failed" : body;
    }

    private ApiResponse send(String method, String path, Object payload, boolean authRequired)
            throws IOException, InterruptedException, ApiException {
        if (authRequired && token == null) {
            throw new ApiException("Not logged in", 401);
        }

        HttpRequest.Builder builder = HttpRequest.newBuilder()
                .uri(URI.create(baseUrl + path))
                .timeout(Duration.ofSeconds(15))
                .header("Accept", "application/json");

        if (payload != null) {
            String json = mapper.writeValueAsString(payload);
            builder.header("Content-Type", "application/json")
                    .method(method, HttpRequest.BodyPublishers.ofString(json));
        } else {
            builder.method(method, HttpRequest.BodyPublishers.noBody());
        }

        if (authRequired) {
            builder.header("Authorization", "Bearer " + token);
        }

        HttpResponse<String> response = httpClient.send(builder.build(), HttpResponse.BodyHandlers.ofString());
        return new ApiResponse(response.statusCode(), response.body());
    }

    private record ApiResponse(int status, String body) {
    }

    @JsonIgnoreProperties(ignoreUnknown = true)
    private static class LoginResponse {
        public String accessToken;
        public User user;
    }
}
