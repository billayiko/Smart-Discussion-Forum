package com.academicpulse.desktop.model;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;

import java.util.Collections;
import java.util.List;

@JsonIgnoreProperties(ignoreUnknown = true)
public class Conversation {
    public long id;
    public String displayName;
    public String updatedAt;
    public LastMessage lastMessage;
    public List<ChatMessage> messages = Collections.emptyList();

    @Override
    public String toString() {
        return displayName;
    }

    @JsonIgnoreProperties(ignoreUnknown = true)
    public static class LastMessage {
        public String body;
        public String userName;
        public String createdAt;
    }
}
