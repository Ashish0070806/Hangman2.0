<?php

namespace view;

class RegisterView {

    private $registerModel;

    private $username = 'RegisterView::UserName';
    private $password = 'RegisterView::Password';
    private $repeatPassword = 'RegisterView::PasswordRepeat';
    private $register = 'RegisterView::Register';
    private $message = 'RegisterView::Message';

    private $usernameValue = '';
    private $inputIsCorrect = false;

    public function __construct(\model\Register $registerModel){
        $this->registerModel = $registerModel;
    }

    /**
     * Create HTTP response for Registration
     *
     * @return string HTML output
     */
    public function response(){
        $message = '';

        if(($_SERVER["REQUEST_METHOD"] ?? '') == "POST" && isset($_POST[$this->register])) {
            $this->usernameValue = $this->postRequestUsername();
            $validationError = $this->checkRegisterInput();

            if(!$this->inputIsCorrect) {
                $message = $validationError;
            } else {
                // Secure Execution Pipeline: passes sanitized username & password to Model
                $message = $this->registerModel->addUserToDatabase($this->postRequestUsername(), $this->postRequestPassword());
            }

            $response = $this->generateRegisterHTML($message);
        } else {
            $response = $this->generateRegisterHTML($message);
        }

        return $response;
    }

    private function checkRegisterInput() {
        $username = $this->postRequestUsername();
        $password = $this->postRequestPassword();
        $repeatPassword = $this->postRequestRepeatPassword();

        if(empty($username) && empty($password) && empty($repeatPassword)) {
            return "Username has too few characters, at least 3 characters.";
        } else if(empty($username) || empty($password) || empty($repeatPassword)) {
            return "Password has too few characters, at least 6 characters.";
        } else if(strlen(trim($username)) < 3) {
            return "Username has too few characters, at least 3 characters.";
        } else if(!(strip_tags($username) == $username)) {
            return "Username contains invalid characters.";
        } else if(strlen(trim($password)) < 6) {
            return "Password has too few characters, at least 6 characters.";
        } else if($password !== $repeatPassword) {
            return "Passwords do not match.";
        }

        $this->inputIsCorrect = true;
        return "";
    }

    private function postRequestUsername(){
        if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST[$this->username])) {
            return trim($_POST[$this->username]);
        }
        return "";
    }
    
    private function postRequestPassword() {
        if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST[$this->password])) {
            return $_POST[$this->password];
        }
        return "";
    }

    private function postRequestRepeatPassword() {
        if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST[$this->repeatPassword])) {
            return $_POST[$this->repeatPassword];
        }
        return "";
    }
    
    private function generateRegisterHTML($message) {
        $msgHtml = !empty($message) ? '<p id="' . $this->message . '" class="message-text error">' . e($message) . '</p>' : '<p id="' . $this->message . '"></p>';

        return '
            <div class="auth-box">
                <h2>👤 Register New User</h2>
                <div style="margin-bottom: 20px;">
                    <a href="?login" class="btn btn-secondary">Back to login</a>
                </div>
                <form method="post"> 
                    <fieldset>
                        <legend>Write username and password</legend>
                        ' . $msgHtml . '

                        <div class="form-group">
                            <label for="' . $this->username . '">Username :</label>
                            <input type="text" id="' . $this->username . '" name="' . $this->username . '" value="'. e($this->usernameValue) . '" maxlength="20" required />
                        </div>

                        <div class="form-group">
                            <label for="' . $this->password . '">Password :</label>
                            <input type="password" id="' . $this->password . '" name="' . $this->password . '" required />
                        </div>
                        
                        <div class="form-group">
                            <label for="' . $this->repeatPassword . '">Repeat Password :</label>
                            <input type="password" id="' . $this->repeatPassword . '" name="' . $this->repeatPassword . '" required />
                        </div>
                        
                        <div class="btn-group" style="margin-top: 20px;">
                            <input type="submit" name="' . $this->register . '" value="Register" class="btn btn-primary btn-block" />
                        </div>
                    </fieldset>
                </form>
            </div>
        ';
    }

    public function showRegisterTag() {
        if(isset($_GET["login?register"])) {
            return '<a href="?login" class="btn btn-secondary">Back to login</a>';
        } else if($this->registerModel->isUserLoggedIn()) {
            return "";
        } else {
            return '<a href="?login?register" class="btn btn-secondary">Register a new user</a>';
        }
    }
}