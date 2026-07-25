package com.academicpulse.desktop.model;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;

import java.util.Collections;
import java.util.List;

@JsonIgnoreProperties(ignoreUnknown = true)
public class AdminQuestionsData {
    public long unansweredCount;
    public List<Item> questions = Collections.emptyList();

    @JsonIgnoreProperties(ignoreUnknown = true)
    public static class Item {
        public long id;
        public String title;
        public String topicTitle;
        public String userName;
        public long answersCount;
        public boolean flaggedOffTopic;
    }
}
