package com.academicpulse.desktop.model;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;

@JsonIgnoreProperties(ignoreUnknown = true)
public class User {
    public long id;
    public String name;
    public String email;
    public String role;

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
