package com.academicpulse.desktop.model;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;

import java.util.List;

@JsonIgnoreProperties(ignoreUnknown = true)
public class StudentDashboard {
    public Stats stats;
    public List<UpcomingQuiz> upcomingQuizzes;
    public List<UpcomingQuiz> upcomingQuizAnnouncements;
    public List<RecentQuestion> recentQuestions;
    public long unansweredQuestionsCount;
    public List<SubjectRow> quizzesBySubject;
    public int answeredRate;

    @JsonIgnoreProperties(ignoreUnknown = true)
    public static class Stats {
        public long enrolledLectures;
        public long newSubscriptionsThisWeek;
        public long quizzes;
        public long upcomingClasses;
        public String nextClassScheduledAt;
        public Integer averageGradePercent;
        public long gradedQuizCount;
    }

    @JsonIgnoreProperties(ignoreUnknown = true)
    public static class UpcomingQuiz {
        public long id;
        public String title;
        public String subject;
        public int durationMinutes;
        public boolean hasStarted;
        public String scheduledAt;
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
    public static class SubjectRow {
        public String subject;
        public long total;
        public int pct;
    }
}
