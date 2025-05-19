<?php
// app/helpers/flash_messages.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Set a flash message.
 * @param string $key e.g., 'success', 'error'
 * @param string $message The message content
 */
function set_flash_message(string $key, string $message): void {
    $_SESSION['flash_messages'][$key] = $message;
}

/**
 * Get and clear a flash message.
 * @param string $key e.g., 'success', 'error'
 * @return string|null The message or null if not set
 */
function get_flash_message(string $key): ?string {
    if (isset($_SESSION['flash_messages'][$key])) {
        $message = $_SESSION['flash_messages'][$key];
        unset($_SESSION['flash_messages'][$key]);
        return $message;
    }
    return null;
}

/**
 * Display flash messages (typically called in header or layout).
 */
function display_flash_messages(): void {
    if (!isset($_SESSION['flash_messages']) || empty($_SESSION['flash_messages'])) {
        return;
    }

    foreach ($_SESSION['flash_messages'] as $key => $message) {
        // Determine class based on key (e.g., success, error, warning, info)
        $alertClass = 'alert-info'; // Default
        if ($key === 'success') $alertClass = 'alert-success';
        if ($key === 'error') $alertClass = 'alert-danger';
        if ($key === 'warning') $alertClass = 'alert-warning';

        echo "<div class='flash-message alert {$alertClass}' role='alert'>";
        echo htmlspecialchars($message);
        echo "</div>";
    }
    unset($_SESSION['flash_messages']); // Clear all messages after display
}

/**
 * Check if any flash messages exist.
 * @return bool
 */
function has_flash_messages(): bool {
     return isset($_SESSION['flash_messages']) && !empty($_SESSION['flash_messages']);
}