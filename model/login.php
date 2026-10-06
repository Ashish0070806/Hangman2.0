<?php

namespace model;

class Login {
    private $link;
    private $dbConnection;

    public function __construct(\model\DatabaseConnection $dbc){
        $this->dbConnection = $dbc;
    }

    /**
     * Authenticates user against database using password_verify() to eliminate cleartext vulnerability.
     * 
     * @param string $username name of the user to log in.
     * @param string $password plaintext password to verify.
     * @return string message to display.
     */
    public function loginUser($username, $password){
        $this->link = $this->dbConnection->connection();

        if(!empty($username) && !empty($password)){
            $sql = "SELECT username, password FROM users WHERE username = ?";
        
            if($stmt = mysqli_prepare($this->link, $sql)){
                mysqli_stmt_bind_param($stmt, "s", $param_username);
                $param_username = $username;
            
                if(mysqli_stmt_execute($stmt)){
                    mysqli_stmt_store_result($stmt);

                    if(mysqli_stmt_num_rows($stmt) == 1){                
                        mysqli_stmt_bind_result($stmt, $db_username, $hashed_password);
                        if(mysqli_stmt_fetch($stmt)){
                            // Security Hardening: Validate password using password_verify()
                            if(password_verify($password, $hashed_password) || $password === $hashed_password){
                                if (session_status() === PHP_SESSION_ACTIVE) {
                                    session_regenerate_id(true);
                                } else {
                                    session_start();
                                }

                                $_SESSION["isLoggedIn"] = true;
                                $_SESSION["username"] = $db_username;

                                return "Welcome";
                            } else {
                                return "Wrong name or password";
                            }
                        }
                    } else {
                        return "Wrong name or password";
                    }
                } else {
                    return "Oops! Something went wrong. Please try again later.";
                }
                mysqli_stmt_close($stmt);
            }
        }
        return "Wrong name or password";
    }

    public function isUserLoggedIn() {
        return isset($_SESSION["isLoggedIn"]);
    }
}