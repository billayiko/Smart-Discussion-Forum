package com.academicpulse.desktop.model;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;

import java.util.Collections;
import java.util.List;
import java.util.Map;

@JsonIgnoreProperties(ignoreUnknown = true)
public class QuizResultData {
    public long id;
    public String title;
    public String subject;
    public boolean marksConfirmed;
    public String marksConfirmedAt;
    public boolean canConfirm;
    public List<QuizQuestion> questions = Collections.emptyList();
    public Attempt attempt;
    public Report report;

    @JsonIgnoreProperties(ignoreUnknown = true)
    public static class Attempt {
        public int score;
        public int total;
        public Map<String, String> answers;
        public int proctoringViolations;
    }

    @JsonIgnoreProperties(ignoreUnknown = true)
    public static class Report {
        public long attemptsCount;
        public Integer averageScorePercent;
        public List<TopScorer> topScorers = Collections.emptyList();
    }

    @JsonIgnoreProperties(ignoreUnknown = true)
    public static class TopScorer {
        public String userName;
        public int score;
        public int total;
    }
}
