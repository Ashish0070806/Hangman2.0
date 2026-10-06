<?php

namespace view;

class MainLayoutView {

    private $hangmanController;
    private $versusController;
    private $loginController;
    private $loginLayoutView;
    private $gameView;

    public function __construct(
        \controller\HangmanController $hangmanController, 
        \controller\VersusController $versusController,
        \controller\LoginController $loginController
    ) {
        $this->hangmanController = $hangmanController;
        $this->versusController  = $versusController;
        $this->loginController   = $loginController;
    }
  
    public function render() {
        $mode = $_GET['mode'] ?? 'solo';
        $this->checkWhatLoginToShow($mode);

        // Timer calculation
        $timerHtml = '';
        if ($mode === '1v1') {
            $vsState = $_SESSION['versus'] ?? null;
            if ($vsState && ($vsState['state'] ?? '') === 'play') {
                $elapsed = time() - ($vsState['start_time'] ?? time());
                $remaining = max(0, ($vsState['time_limit'] ?? 60) - $elapsed);
                $timerHtml = '
                <div class="timer" id="timer" data-remaining="' . $remaining . '">
                    <span class="timer-label">⏱️ Time Remaining</span>
                    <span id="timer-display">' . sprintf('%02d:%02d', floor($remaining / 60), $remaining % 60) . '</span>
                </div>';
            }
        } elseif (!empty($this->gameView)) {
            $timerHtml = '
            <div class="timer" id="timer" data-remaining="60">
                <span class="timer-label">⏱️ Time Remaining</span>
                <span id="timer-display">01:00</span>
            </div>';
        }

        // Active mode tab classes
        $soloActive = ($mode !== '1v1') ? 'btn-primary' : 'btn-secondary';
        $versusActive = ($mode === '1v1') ? 'btn-primary' : 'btn-secondary';

        return '<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🎮 Word Guess — Hangman MVC</title>
    <!-- Responsive Dark Theme UI -->
    <link rel="stylesheet" href="public/styles.css">
  </head>
  <body>
    <div class="container">
      <h1>🎮 Lets Play Hangman!</h1>
      <p class="subtitle">Secure MVC Architecture · Responsive Dark Theme</p>
      
      <!-- Mode Selection Tabs -->
      <div class="btn-group" style="display: flex; gap: 10px; justify-content: center; margin-bottom: 25px;">
        <a href="?" class="btn ' . $soloActive . '" style="padding: 10px 20px; font-size: 0.95rem;">
            🤖 Solo (vs AI)
        </a>
        <a href="?mode=1v1" class="btn ' . $versusActive . '" style="padding: 10px 20px; font-size: 0.95rem;">
            👥 1v1 Versus (2-Player)
        </a>
      </div>

      ' . $timerHtml . '

      <div class="game">
        ' . $this->gameView . '
      </div>

      ' . $this->loginLayoutView . '
    </div>

    <!-- Integrated JavaScript Engine: Countdown Timer & Physical Desktop Input -->
    <script>
      (function() {
        // Countdown Timer Engine
        var timerEl = document.getElementById("timer");
        var displayEl = document.getElementById("timer-display");
        if (timerEl && displayEl) {
          var remaining = parseInt(timerEl.getAttribute("data-remaining"), 10) || 60;
          function updateTimerDisplay() {
            var mins = Math.floor(remaining / 60);
            var secs = remaining % 60;
            displayEl.textContent = String(mins).padStart(2, "0") + ":" + String(secs).padStart(2, "0");
            timerEl.classList.toggle("danger", remaining <= 10);
            timerEl.classList.toggle("warning", remaining > 10 && remaining <= 20);
          }
          function tick() {
            if (remaining <= 0) {
              var currentMode = "' . $mode . '";
              window.location.href = (currentMode === "1v1") ? "?mode=1v1&timeout=1" : "?timeout=1";
              return;
            }
            remaining--;
            updateTimerDisplay();
          }
          updateTimerDisplay();
          setInterval(tick, 1000);
        }

        // Physical Desktop Keyboard Input
        document.addEventListener("keydown", function(e) {
          var active = document.activeElement;
          var tag = active ? active.tagName.toLowerCase() : "";
          var type = active ? (active.type || "").toLowerCase() : "";
          if (tag === "input" && (type === "text" || type === "password")) {
            return; // Allow natural typing inside focused form fields
          }
          var key = e.key;
          if (/^[a-zA-Z]$/.test(key)) {
            // Solo Mode Input
            var letterInput = document.querySelector(\'input[name="letter"]\');
            var guessBtn = document.querySelector(\'input[name="Guess"]\');
            if (letterInput && guessBtn) {
              letterInput.value = key.toUpperCase();
              guessBtn.click();
              return;
            }

            // 1v1 Mode Alphabet Grid Button Click
            var buttons = document.querySelectorAll(\'.alphabet-grid button\');
            for (var i = 0; i < buttons.length; i++) {
              if (buttons[i].textContent.trim().toUpperCase() === key.toUpperCase()) {
                buttons[i].click();
                break;
              }
            }
          }
        });
      })();
    </script>
  </body>
</html>';
    }

    private function checkWhatLoginToShow($mode) {
        if(isset($_POST["goToLogin"])) {
            $this->loginLayoutView = $this->loginController->renderPage();
            $this->gameView = "";
        } else if(isset($_GET["login?register"]) || isset($_GET["login"])) {
            $this->loginLayoutView = $this->loginController->renderPage();
            $this->gameView = "";
        } else {
            if ($mode === '1v1') {
                $this->gameView = $this->versusController->render();
            } else {
                $this->gameView = $this->hangmanController->renderHangmanPage();
            }
            $this->loginLayoutView = $this->loginController->renderPage();
        }
    }
}
