<?php

namespace view;

class LoggedInView {

    private $highScore;
    private $addHangmanWords;
    private $highscores;
    private $add = "LoggedInView::Add";
    private $word = "LoggedInView::Word";

    public function __construct(\model\AddHangmanWords $ahw, \model\Highscore $hs) {
        $this->addHangmanWords = $ahw;
        $this->highScore = $hs;
    }

    public function renderLoggedInView() {
        $response = $this->response();
        $highscore = $this->renderPlayerHighscores();

        return '
        <div class="user-dashboard">
            '. $response .'
            '. $highscore . '
        </div>
        ';
    }

    public function response() {
        $message = "";
        $response = "";

        if(($_SERVER["REQUEST_METHOD"] ?? '') == "POST" && isset($_POST[$this->word])) {
            $word = $this->getRequestWord();
            if(strlen($word) < 2) {
                $message = "You need at least two letters to form a word, try again.";
            } else {
                $message = $this->addHangmanWords->addWord($word);
            }
            $response = $this->addWordForm($message);
        } else {
            if(isset($_SESSION["isLoggedIn"])) {
                $response = $this->addWordForm($message);
            }
        }
        return $response;
    }

    private function getRequestWord() {
        if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST[$this->word])){
			return trim($_POST[$this->word]);
		}
        return "";
    }
     
    private function addWordForm($message) {
        $msgHtml = !empty($message) ? '<p id="message" class="message-text">' . e($message) . '</p>' : '<p id="message"></p>';

        return '
        <div class="form-card" style="margin: 20px 0;">
            <form method="post">
               <fieldset>
                 <legend>➕ Add a Word to Hangman Collection</legend>
                 ' . $msgHtml . '
                 <div class="form-group" style="display: flex; gap: 10px; align-items: center; justify-content: center; margin-top: 15px;">
                     <input type="text" name="'. $this->word .'" placeholder="Enter secret word" maxlength="20" autofocus required style="max-width: 280px;" />
                     <input type="submit" name="'. $this->add .'" value="Add" class="btn btn-primary" /> 
                 </div>
                </fieldset>
            </form>
        </div>';
    }

    private function renderPlayerHighscores() {
        $highscores = [];

        if(isset($_SESSION["isLoggedIn"])) {
            if(isset($_SESSION["username"])) {
                $highscores = $this->highScore->getPlayerHighscore($_SESSION["username"]);
            }  

            $highscoreTags = '
            <div class="highscore-block" style="margin-top: 25px;">
                <h3>🏆 My Highscores (' . e($_SESSION["username"] ?? '') . ')</h3>
            ';

            if (!empty($highscores)) {
                $highscoreTags .= '<ol class="highscore-list" style="padding-left: 20px; margin-top: 15px;">';
                foreach($highscores as $highscore) {
                    $highscoreTags .= '<li style="margin-bottom: 10px; font-size: 1rem;"> 
                        <strong>Solved Words:</strong> ' . e($highscore->solvedWords) . ' · 
                        <strong>Total Fails:</strong> ' . e($highscore->totalAmountOfTries) . '
                    </li>';
                }
                $highscoreTags .= '</ol>';
            } else {
                $highscoreTags .= '<p style="color: #888; margin-top: 10px;">No highscores recorded yet. Play a game to set your score!</p>';
            }

            $highscoreTags .= '</div>';
            return $highscoreTags;
        } else {
            return "";
        }
    }
}