<?php
/**
 * Environment Configuration
 * 
 * This file contains environment-specific configurations for the application.
 * Define environment variables and settings here.
 */

// Define environment: 'development' or 'production'
$_ENV['APP_ENV'] = 'development'; // Change to 'production' when deploying

// URLs based on environment
if ($_ENV['APP_ENV'] === 'development') {
    $_ENV['APP_URL'] = 'http://localhost/kelulusan2025';
} else {
    $_ENV['APP_URL'] = 'https://kelulusan.smknu1slawi.sch.id';
}

// Other environment-specific configurations
$_ENV['DEBUG'] = $_ENV['APP_ENV'] === 'development';
?>
