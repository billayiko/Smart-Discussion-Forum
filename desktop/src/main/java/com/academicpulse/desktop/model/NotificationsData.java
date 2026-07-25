package com.academicpulse.desktop.model;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;

import java.util.Collections;
import java.util.List;
import java.util.Map;

@JsonIgnoreProperties(ignoreUnknown = true)
public class NotificationsData {
    public long unreadCount;
    public List<Item> notifications = Collections.emptyList();

    @JsonIgnoreProperties(ignoreUnknown = true)
    public static class Item {
        public String id;
        public String type;
        public Map<String, Object> data = Collections.emptyMap();
        public boolean read;
        public String createdAt;
    }
}
