package com.academicpulse.desktop.model;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;

import java.util.List;

/** Site-wide analytics, matching Api\AnalyticsController::index()'s JSON shape. */
@JsonIgnoreProperties(ignoreUnknown = true)
public class AnalyticsSummary {
    public Summary summary;
    public List<User> topAskers;
    public List<User> topAnswerers;
    public List<Topic> topTopics;
    public List<User> topLecturers;

    @JsonIgnoreProperties(ignoreUnknown = true)
    public static class Summary {
        public long students;
        public long lecturers;
        public long topics;
        public long questions;
        public long unansweredQuestions;
        public long answers;
        public long pendingComplaints;
    }
}
