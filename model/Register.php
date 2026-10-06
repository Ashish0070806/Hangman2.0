<?php

namespace model;

@include_once(__DIR__ . "/environment.php");

class Register {

    private $link;
    private $dbConnection;

    public function __construct(\model\DatabaseConnection $dbc){
        $this->dbConnection = $dbc;
    }

    /**
     * Connects to DB using DB model and adds the user to the DB.
     * Enforces strong BCRYPT password hashing.
     * 
     * @param string $userName name of the user to register.
     * @param string $password plaintext password to hash and store.
     * @return string message to display.
     */
    public function addUserToDatabase($userName, $password){
        $this->link = $this->dbConnection->connection();

        $query = "INSERT INTO users (username, password) VALUES (?,?)";
        $stmt = mysqli_prepare($this->link, $query);

        // Security Hardening: Enforce PASSWORD_BCRYPT
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        mysqli_stmt_bind_param($stmt, "ss", $param_userName, $param_password);
        $param_password = $hashed_password;
        $param_userName = $userName;
        
        if (mysqli_stmt_execute($stmt)){
            $msg = "Registered new user.";
        } else {
            $msg = "User exists, pick another username.";
        }

        mysqli_stmt_close($stmt);
        return $msg;
    }

    public function isUserLoggedIn() {
        return isset($_SESSION["isLoggedIn"]);
    }
}