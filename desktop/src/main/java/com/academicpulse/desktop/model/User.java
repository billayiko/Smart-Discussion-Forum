package com.academicpulse.desktop.model;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;

@JsonIgnoreProperties(ignoreUnknown = true)
public class User {
    public long id;
    public String name;
    public String email;
    public String role;

    // Only populated on analytics responses (withCount columns) — 0 elsewhere.
    public long questionsCount;
    public long answersCount;
    public long assignedTopicsCount;

    public String roleLabel() {
        if (role == null || role.isEmpty()) {
            return "User";
        }
        return Character.toUpperCase(role.charAt(0)) + role.substring(1);
    }

    @Override
    public String toString() {
        return name;
    }
}
