package com.academicpulse.desktop.model;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;

import java.util.Collections;
import java.util.List;

@JsonIgnoreProperties(ignoreUnknown = true)
public class ChatMessage {
    public long id;
    public String body;
    public String createdAt;
    public User user;
    public List<String> excludedNames = Collections.emptyList();

    @Override
    public String toString() {
        return body;
    }
}
