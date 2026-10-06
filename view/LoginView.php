<?php

namespace view;

class LoginView {
	private $loginModel;

	private $login = 'LoginView::Login';
	private $logout = 'LoginView::Logout';
	private $name = 'LoginView::UserName';
	private $password = 'LoginView::Password';
	private $cookieName = 'LoginView::CookieName';
	private $cookiePassword = 'LoginView::CookiePassword';
	private $keep = 'LoginView::KeepMeLoggedIn';
	private $messageId = 'LoginView::Message';

	private $getUsername = '';

	public function __construct(\model\Login $loginModel){
		$this->loginModel = $loginModel;
	}

	/**
	 * Create HTTP response
	 *
	 * @return string HTML output
	 */
	public function response() {
		$message = '';

		if(($_SERVER["REQUEST_METHOD"] ?? '') == "POST" && (isset($_POST[$this->login]) || isset($_POST[$this->logout]))) {

			if(isset($_POST[$this->logout])) {
				session_destroy();
				$_SESSION = [];

				$message = "Bye bye!";
				$response = $this->generateLoginFormHTML($message);
			} else {
				$username = $this->postRequestUsername();
				$password = $this->postRequestPassword();
				$this->getUsername = $this->postRequestUsername();

			    if(empty($username)) {
					$message = "Username is missing";
					$response = $this->generateLoginFormHTML($message);
			    } else if(empty($password)) {
					$message = "Password is missing";
					$response = $this->generateLoginFormHTML($message);
			    } else {
				   // Secure execution pipeline: verifies password against BCRYPT hash in Model
				   $message = $this->loginModel->loginUser($username, $password);
				   
				   if($this->loginModel->isUserLoggedIn()) {
					    $response = $this->generateLogoutButtonHTML($message);
				   } else {
					    $response = $this->generateLoginFormHTML($message);
				   }
			    }
			}
		} else {
			if(isset($_GET["login"])) {
				if($this->loginModel->isUserLoggedIn()) {
					$response = $this->generateLogoutButtonHTML($message);
				} else {
					$response = $this->generateLoginFormHTML($message);
				}
			} else {
				$response = $this->renderGoToLogin();
			}
		}
		return $response;
	}

	/**
	* Generate HTML code for logout button
	* @param string $message
	* @return string
	*/
	private function generateLogoutButtonHTML($message) {
        $msgHtml = !empty($message) ? '<p id="' . $this->messageId . '" class="message-text success">' . e($message) .'</p>' : '<p id="' . $this->messageId . '"></p>';

		return '
			<div class="auth-box">
				<form method="post">
					' . $msgHtml . '
					<div class="btn-group" style="margin-top: 15px;">
						<input type="submit" name="' . $this->logout . '" value="Logout" class="btn btn-secondary" />
					</div>
				</form>
			</div>
		';
	}
	
	/**
	* Generate HTML code for login form
	* @param string $message
	* @return string
	*/
	private function generateLoginFormHTML($message) {
        $msgHtml = !empty($message) ? '<p id="' . $this->messageId . '" class="message-text error">' . e($message) . '</p>' : '<p id="' . $this->messageId . '"></p>';

		return '
			<div class="auth-box">
				<form method="post"> 
					<fieldset>
						<legend>Login — Enter Username and Password</legend>
						' . $msgHtml . '
						
						<div class="form-group">
							<label for="' . $this->name . '">Username :</label>
							<input type="text" id="' . $this->name . '" name="' . $this->name . '" value="' . e($this->getUsername) .'" maxlength="20" required />
						</div>

						<div class="form-group">
							<label for="' . $this->password . '">Password :</label>
							<input type="password" id="' . $this->password . '" name="' . $this->password . '" required />
						</div>

						<div class="form-group" style="margin: 15px 0;">
							<label for="' . $this->keep . '" style="display: inline-flex; align-items: center; gap: 8px; cursor: pointer;">
								<input type="checkbox" id="' . $this->keep . '" name="' . $this->keep . '" style="width: auto;" />
								Keep me logged in
							</label>
						</div>
						
						<div class="btn-group" style="margin-top: 20px;">
							<input type="submit" name="' . $this->login . '" value="Login" class="btn btn-primary btn-block" />
						</div>
					</fieldset>
				</form>
			</div>
		';
	}

	private function postRequestPassword() {
		if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST[$this->password])){
			return $_POST[$this->password];
		}
		return "";
	}

	private function postRequestUsername(){
		if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST[$this->name])){
			return trim($_POST[$this->name]);
		}
		return "";
	}
	
	public function renderGoToLogin() {
		if($this->loginModel->isUserLoggedIn()) {
			return '
			<div style="text-align: right; margin-bottom: 15px;">
				<form method="post" action="?login" style="display: inline;">
					<input type="submit" name="goToLogin" value="MyPage" class="btn btn-secondary" />
				</form>
			</div>';
		} else {
			return '
			<div style="text-align: right; margin-bottom: 15px;">
				<form method="post" action="?login" style="display: inline;">
					<input type="submit" name="goToLogin" value="Login" class="btn btn-secondary" />
				</form>
			</div>';
		}
	}
}