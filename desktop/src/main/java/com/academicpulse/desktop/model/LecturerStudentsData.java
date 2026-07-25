package com.academicpulse.desktop.model;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;

import java.util.List;

@JsonIgnoreProperties(ignoreUnknown = true)
public class LecturerStudentsData {
    public List<TopicSummary> topics;
    public List<StudentRow> students;

    @JsonIgnoreProperties(ignoreUnknown = true)
    public static class TopicSummary {
        public long id;
        public String title;
        public long subscribersCount;
    }

    @JsonIgnoreProperties(ignoreUnknown = true)
    public static class StudentRow {
        public long id;
        public String name;
        public String email;
        public boolean isOnline;
        public List<String> subscribedTopics;
    }
}
