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

    /** Mirrors User::initials() on the server — up to two uppercase initials from the name. */
    public String initials() {
        if (name == null || name.isBlank()) {
            return "?";
        }
        StringBuilder initials = new StringBuilder();
        for (String word : name.split(" ")) {
            if (!word.isEmpty() && initials.length() < 2) {
                initials.append(Character.toUpperCase(word.charAt(0)));
            }
        }
        return initials.toString();
    }

    @Override
    public String toString() {
        return name;
    }
}
