package com.academicpulse.desktop.model;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;

import java.util.List;

@JsonIgnoreProperties(ignoreUnknown = true)
public class QuizzesData {
    public Stats stats;
    public List<QuizSummary> quizzes;

    @JsonIgnoreProperties(ignoreUnknown = true)
    public static class Stats {
        public long activeCount;
        public long publishedThisWeek;
    }
}
