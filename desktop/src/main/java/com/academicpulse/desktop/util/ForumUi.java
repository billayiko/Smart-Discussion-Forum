package com.academicpulse.desktop.util;

import com.academicpulse.desktop.model.User;
import javafx.geometry.Pos;
import javafx.scene.control.Label;
import javafx.scene.layout.StackPane;

/** Shared avatar-bubble and role-badge builders used across the Discussion Forum screens. */
public final class ForumUi {
    private ForumUi() {
    }

    public static StackPane avatar(String initials) {
        Label label = new Label(initials == null ? "?" : initials);
        label.getStyleClass().add("app-avatar-label");
        StackPane avatar = new StackPane(label);
        avatar.getStyleClass().add("app-avatar");
        avatar.setAlignment(Pos.CENTER);
        return avatar;
    }

    /** Mirrors User::initials() for names that don't come attached to a User (e.g. a conversation's display name). */
    public static String initials(String name) {
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

    public static Label roleBadge(User user) {
        String role = user == null || user.role == null ? "member" : user.role;
        Label badge = new Label(user == null ? "User" : user.roleLabel());
        badge.getStyleClass().add("app-role-badge");
        badge.getStyleClass().add(switch (role) {
            case "student" -> "app-role-badge-student";
            case "lecturer" -> "app-role-badge-lecturer";
            case "admin" -> "app-role-badge-admin";
            default -> "app-role-badge-member";
        });
        return badge;
    }
}
