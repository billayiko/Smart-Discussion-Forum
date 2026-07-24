package com.academicpulse.desktop.cache;

import java.nio.file.Path;
import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.time.Instant;
import java.util.Optional;

/**
 * A persistent on-disk cache of raw API response bodies, keyed by a
 * deterministic string per endpoint+resource. This lets the desktop client
 * keep showing the last-known data (topics, threads, conversations) when
 * offline, without the app talking to anything other than the Laravel REST
 * API — SQLite here is purely a local read cache, not a database the app
 * queries directly for its own model.
 */
public class LocalCache {
    private final Connection connection;

    public LocalCache() throws SQLException {
        this(Path.of(System.getProperty("user.home"), ".academic-pulse", "cache.db"));
    }

    public LocalCache(Path dbPath) throws SQLException {
        try {
            java.nio.file.Files.createDirectories(dbPath.getParent());
        } catch (java.io.IOException e) {
            throw new SQLException("Could not create cache directory: " + dbPath.getParent(), e);
        }

        try {
            // DriverManager's ServiceLoader-based auto-registration doesn't
            // reliably pick this up in this app's mixed module-path/classpath
            // launch, so force the driver's static registration explicitly.
            Class.forName("org.sqlite.JDBC");
        } catch (ClassNotFoundException e) {
            throw new SQLException("SQLite JDBC driver not found on the classpath", e);
        }

        connection = DriverManager.getConnection("jdbc:sqlite:" + dbPath);
        try (var statement = connection.createStatement()) {
            statement.execute(
                    "CREATE TABLE IF NOT EXISTS cache_entries ("
                            + "cache_key TEXT PRIMARY KEY, "
                            + "json_blob TEXT NOT NULL, "
                            + "cached_at TEXT NOT NULL)");
        }
    }

    public synchronized Optional<String> get(String key) {
        String sql = "SELECT json_blob FROM cache_entries WHERE cache_key = ?";
        try (PreparedStatement statement = connection.prepareStatement(sql)) {
            statement.setString(1, key);
            try (ResultSet result = statement.executeQuery()) {
                return result.next() ? Optional.of(result.getString("json_blob")) : Optional.empty();
            }
        } catch (SQLException e) {
            return Optional.empty();
        }
    }

    public synchronized void put(String key, String jsonBlob) {
        String sql = "INSERT INTO cache_entries (cache_key, json_blob, cached_at) VALUES (?, ?, ?) "
                + "ON CONFLICT(cache_key) DO UPDATE SET json_blob = excluded.json_blob, cached_at = excluded.cached_at";
        try (PreparedStatement statement = connection.prepareStatement(sql)) {
            statement.setString(1, key);
            statement.setString(2, jsonBlob);
            statement.setString(3, Instant.now().toString());
            statement.executeUpdate();
        } catch (SQLException ignored) {
            // Caching is best-effort: a write failure shouldn't break the live request it came from.
        }
    }
}
