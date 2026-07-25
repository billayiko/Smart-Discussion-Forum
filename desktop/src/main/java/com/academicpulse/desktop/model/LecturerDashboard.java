package com.academicpulse.desktop.model;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;

import java.util.List;

@JsonIgnoreProperties(ignoreUnknown = true)
public class LecturerDashboard {
    public Stats stats;
    public List<RecentQuestion> recentQuestions;
    public long unansweredQuestionsCount;
    public List<QuizStatusRow> quizzesByStatus;
    public ParticipationCriteria participationCriteria;
    public DiscussionStats discussionStats;

    @JsonIgnoreProperties(ignoreUnknown = true)
    public static class Stats {
        public long quizzes;
        public long activeQuizzes;
        public long publishedThisWeek;
        public long students;
        public long totalTopics;
        public long upcomingClasses;
        public String nextClassScheduledAt;
        public Integer averageScorePercent;
    }

    @JsonIgnoreProperties(ignoreUnknown = true)
    public static class RecentQuestion {
        public long id;
        public String title;
        public String topicTitle;
        public String userName;
        public long answersCount;
    }

    @JsonIgnoreProperties(ignoreUnknown = true)
    public static class QuizStatusRow {
        public String status;
        public String label;
        public long total;
        public int pct;
    }

    @JsonIgnoreProperties(ignoreUnknown = true)
    public static class ParticipationCriteria {
        public int pointsPerQuestion;
        public int pointsPerAnswer;
        public int pointsPerLikeReceived;
        public int targetPoints;
    }

    @JsonIgnoreProperties(ignoreUnknown = true)
    public static class DiscussionStats {
        public long newThreadsThisWeek;
        public long unresolvedCount;
        public long participantsCount;
        public String topTopicTitle;
    }
}
