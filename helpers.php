<?php
/**
 * Global Output Sanitization Helper (XSS Protection)
 */
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
}
