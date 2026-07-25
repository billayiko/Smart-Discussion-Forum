package com.academicpulse.desktop.model;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;

import java.util.List;

/** JSON shape of GET /api/admin/dashboard — mirrors DashboardController::admin() on the web. */
@JsonIgnoreProperties(ignoreUnknown = true)
public class AdminDashboard {
    public Bubbles bubbles;
    public List<QuizRow> quizzes;

    @JsonIgnoreProperties(ignoreUnknown = true)
    public static class Bubbles {
        public long topics;
        public long unassignedTopics;
        public long questions;
        public long unansweredQuestions;
        public long pendingComplaints;
        public long quizzes;
        public long publishedQuizzes;
        public long students;
        public long lecturers;
    }

    @JsonIgnoreProperties(ignoreUnknown = true)
    public static class QuizRow {
        public long id;
        public String title;
        public String subject;
        public int totalQuestions;
        public int durationMinutes;
        public long attemptsCount;
        public Integer averageScorePercent;
        public String stage;
    }
}
