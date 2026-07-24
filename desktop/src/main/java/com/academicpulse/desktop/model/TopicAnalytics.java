package com.academicpulse.desktop.model;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;

import java.util.List;

/** Per-topic analytics, matching Api\AnalyticsController::show()'s JSON shape. */
@JsonIgnoreProperties(ignoreUnknown = true)
public class TopicAnalytics {
    public Topic topic;
    public Summary summary;
    public List<User> topAskers;
    public List<User> topAnswerers;

    @JsonIgnoreProperties(ignoreUnknown = true)
    public static class Summary {
        public long subscribers;
        public long questions;
        public long unansweredQuestions;
        public long answers;
        public long pendingComplaints;
        public long quizzes;
        public Integer averageQuizScore;
    }
}
