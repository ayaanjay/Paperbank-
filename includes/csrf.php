<?php
/**
 * CSRF Protection Helper Functions
 * Provides token generation and validation for security
 */

/**
 * Get or create a CSRF token
 * @return string The CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Get the CSRF token for use in forms/HTML
 * @return string The CSRF token value
 */
function getCSRFToken() {
    return generateCSRFToken();
}

/**
 * Validate a CSRF token from POST/GET request
 * @param string $tokenName The name of the token parameter to check
 * @return bool True if valid, false otherwise
 */
function validateCSRFToken($tokenName = 'csrf_token') {
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    
    if (!isset($_POST[$tokenName]) && !isset($_GET[$tokenName])) {
        return false;
    }
    
    $providedToken = $_POST[$tokenName] ?? $_GET[$tokenName];
    
    // Use hash_equals for timing-attack safe comparison
    return hash_equals($_SESSION['csrf_token'], $providedToken);
}

/**
 * Render a hidden CSRF token field for forms
 * @param string $fieldName The name attribute for the input (default: csrf_token)
 * @return string HTML for hidden input field
 */
function csrfField($fieldName = 'csrf_token') {
    $token = getCSRFToken();
    return '<input type="hidden" name="' . htmlspecialchars($fieldName) . '" value="' . htmlspecialchars($token) . '">';
}
?>
