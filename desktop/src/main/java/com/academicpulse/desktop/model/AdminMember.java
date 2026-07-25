package com.academicpulse.desktop.model;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;

@JsonIgnoreProperties(ignoreUnknown = true)
public class AdminMember {
    public long id;
    public String name;
    public String email;
    public String role;
    public int warningCount;
    public boolean blacklisted;
    public String blacklistedUntil;
}
