package com.academicpulse.desktop.model;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;

import java.util.Collections;
import java.util.List;

@JsonIgnoreProperties(ignoreUnknown = true)
public class TopicActivityData {
    public List<LeaderboardRow> participationLeaderboard = Collections.emptyList();
    public List<ActivityEvent> recentActivity = Collections.emptyList();

    @JsonIgnoreProperties(ignoreUnknown = true)
    public static class LeaderboardRow {
        public String userName;
        public long posts;
        public int score;
    }

    @JsonIgnoreProperties(ignoreUnknown = true)
    public static class ActivityEvent {
        public String icon;
        public String text;
        public String at;
    }
}
