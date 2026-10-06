<?php

namespace model;

@include_once(__DIR__ . "/environment.php");

/**
 * Class that connects to DB using env variables and checks the connection.
 */
class DatabaseConnection {

    private static $link = null;

    public function __construct() {
        if (!function_exists('mysqli_connect')) {
            return;
        }

        $server = $_ENV["DB_SERVER"] ?? '127.0.0.1';
        $user = $_ENV["DB_USERNAME"] ?? 'root';
        $pass = $_ENV["DB_PASSWORD"] ?? '';
        $db = $_ENV["DB_NAME"] ?? 'hangman';

        // Connect to MySQL server
        $conn = @mysqli_connect($server, $user, $pass);
        if ($conn) {
            @mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            @mysqli_select_db($conn, $db);
            
            // Auto-provision tables if they do not exist
            @mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `users` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `username` VARCHAR(50) NOT NULL UNIQUE,
                `password` VARCHAR(255) NOT NULL
            )");

            @mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `words` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `word` VARCHAR(50) NOT NULL UNIQUE
            )");

            @mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `highscore` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `playerName` VARCHAR(50) NOT NULL,
                `solvedWords` INT NOT NULL,
                `totalAmountOfTries` INT NOT NULL
            )");

            // Seed initial words if table is empty
            $countRes = @mysqli_query($conn, "SELECT COUNT(*) as cnt FROM `words`");
            $count = $countRes ? (int)mysqli_fetch_assoc($countRes)['cnt'] : 0;
            if ($count === 0) {
                @mysqli_query($conn, "INSERT IGNORE INTO `words` (`word`) VALUES 
                    ('HANGMAN'), ('DEVELOPER'), ('SECURITY'), ('ARCHITECT'), ('PHP'), ('APPLICATION'), ('DATABASE')");
            }

            self::$link = $conn;
        }
    }
    
    public function connection() {
        if (!function_exists('mysqli_connect')) {
            return false;
        }
        if (self::$link === null || self::$link === false) {
            $server = $_ENV["DB_SERVER"] ?? '127.0.0.1';
            $user = $_ENV["DB_USERNAME"] ?? 'root';
            $pass = $_ENV["DB_PASSWORD"] ?? '';
            $db = $_ENV["DB_NAME"] ?? 'hangman';
            self::$link = @mysqli_connect($server, $user, $pass, $db);
        }
        return self::$link;
    }
}