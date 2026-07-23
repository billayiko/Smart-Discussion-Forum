package com.academicpulse.desktop.model;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;

@JsonIgnoreProperties(ignoreUnknown = true)
public class ChatMessage {
    public long id;
    public String body;
    public String createdAt;
    public User user;

    @Override
    public String toString() {
        return body;
    }
}
