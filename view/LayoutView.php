<?php

namespace view;

/**
 * LayoutView — Coordinates login, register, dateTime, and loggedIn views.
 */
class LayoutView {
  
  private $LoginView;
  private $DateTimeView;
  private $RegisterView;
  private $LoggedInView;

  private $registerTag;
  private $registerOrLogin;
  private $dateTime;
  private $loggedInOrNotTag;
  private $backButton;
  private $addWord;

  public function __construct(\view\LoginView $v, \view\DateTimeView $dtv, \view\RegisterView $rv, \view\LoggedInView $liv) {
    $this->LoginView = $v;
    $this->DateTimeView = $dtv;
    $this->RegisterView = $rv;
    $this->LoggedInView = $liv;
  }
  
  public function render() {
    $this->checkWhatToRender();

    return '
      <div class="auth-navigation" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
          <div>' . $this->registerTag . '</div>
          <div>' . $this->loggedInOrNotTag . '</div>
          <div>' . $this->backButton . '</div>
      </div>
      ' . $this->addWord . '
      <div class="auth-body">
          ' . $this->registerOrLogin . '
      </div>
      <div class="datetime-footer" style="text-align: center; margin-top: 30px; font-size: 0.85rem; color: #888;">
          ' . $this->dateTime . '
      </div>
    ';
  }

  private function checkWhatToRender() {
    $this->registerOrLogin = $this->checkWhichResponseToShow();
    $this->dateTime = $this->DateTimeView->show();
    $this->loggedInOrNotTag = $this->renderIsLoggedIn();
    
    if(isset($_GET["login"])) {
      $this->registerTag = $this->RegisterView->showRegisterTag();
      $this->backButton = $this->renderBackButton();
      if(isset($_SESSION["isLoggedIn"])) {
        $this->addWord = $this->LoggedInView->renderLoggedInView();
      } else {
        $this->addWord = "";
      }
    } else {
      $this->registerTag = "";
      $this->backButton = "";
      $this->addWord = "";
    }
  }

  private function checkWhichResponseToShow() {
    if(isset($_GET["login?register"])) {
      return $this->RegisterView->response();
    } else {
       return $this->LoginView->response();
    }
  }

  private function renderIsLoggedIn() {
    if (isset($_SESSION["isLoggedIn"])) {
      return '<span class="status-badge logged-in" style="background: rgba(46, 204, 113, 0.2); color: #2ecc71; padding: 6px 14px; border-radius: 12px; font-weight: 600; font-size: 0.9rem;">● Logged in (' . e($_SESSION["username"] ?? '') . ')</span>';
    } else {
      return '<span class="status-badge logged-out" style="background: rgba(231, 76, 60, 0.2); color: #e74c3c; padding: 6px 14px; border-radius: 12px; font-weight: 600; font-size: 0.9rem;">○ Not logged in</span>';
    }
  }

  private function renderBackButton() {
    return '
    <form method="post" action="?" style="display: inline;">
        <input type="submit" name="goToHangman" value="Back to Game" class="btn btn-secondary" />
    </form>';
  }
}
