package com.academicpulse.desktop.model;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;

import java.util.Collections;
import java.util.List;

@JsonIgnoreProperties(ignoreUnknown = true)
public class TopicBrowseData {
    public List<Item> topics = Collections.emptyList();
    public List<Long> subscribedTopicIds = Collections.emptyList();

    @JsonIgnoreProperties(ignoreUnknown = true)
    public static class Item {
        public long id;
        public String title;
        public String description;
        public String lecturerName;
        public long subscribersCount;
    }
}
