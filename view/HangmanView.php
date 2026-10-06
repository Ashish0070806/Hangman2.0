<?php

namespace view;

/**
 * HangmanView — Handles Solo Mode (vs AI) with AI hints, letter guessing,
 * hangman art visualization, win/lose screens, and highscore integration.
 */
class HangmanView {

    private $hangmanStates;
    private $hangmanWords;
    private $highscore;
    private $wordsArray;
    private $randomNumber;
    private $wordToGuess;
    private $wrong;
    private $guessedLetters;
    private $guessed;
    private $which;

    public function __construct(\model\HangmanStates $hangmanStates, \model\GetHangmanWords $hangmanWords, \model\Highscore $highscore)
    {
        $this->hangmanStates = $hangmanStates;
        $this->hangmanWords = $hangmanWords;
        $this->highscore = $highscore;
        $this->wordsArray = $this->hangmanWords->getWords();

        if (empty($this->wordsArray)) {
            $this->wordsArray = ['HANGMAN', 'DEVELOPER', 'SECURITY', 'ARCHITECT', 'PHP', 'APPLICATION', 'DATABASE'];
        }
    }

    /**
     * Main display function returning collected HTML for game interface.
     */
	private function show($guessedLetter, $wordAsUnderscore, $wrong, $currentWordAsIndex) {
        $guessForm = $this->guessForm($guessedLetter, $wrong, $currentWordAsIndex);
        $hangArt = $this->hangmanStates->hang[$wrong] ?? ($this->hangmanStates->hang[0] ?? '');
        $currentWord = $this->wordsArray[$currentWordAsIndex] ?? 'HANGMAN';

        $hintsRemaining = $_SESSION['solo_hints_remaining'] ?? 2;
        $clueUnlocked = $_SESSION['solo_clue_unlocked'] ?? false;

        // AI Hint Display
        $aiHintHtml = '';
        if ($clueUnlocked) {
            $clueText = \model\WordHints::getHintForWord($currentWord);
            $aiHintHtml = '
            <div class="hint-text" style="background: rgba(233, 69, 96, 0.15); border-left: 4px solid #e94560; padding: 12px 16px; border-radius: 10px; margin: 15px 0; text-align: left;">
                🤖 <strong>AI Meaning Clue:</strong> "' . e($clueText) . '"
            </div>';
        }

        // Action Buttons for AI Hints
        $hintButtons = '
        <div class="hint-section" style="display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; margin-top: 15px;">';
        
        if (!$clueUnlocked) {
            $hintButtons .= '
            <a href="?ai_clue=1" class="btn-hint" style="text-decoration:none;">
                💡 Ask AI for Meaning Clue
            </a>';
        }

        if ($hintsRemaining > 0) {
            $hintButtons .= '
            <a href="?ai_letter=1" class="btn-hint" style="text-decoration:none;">
                ✨ Reveal a Letter (' . $hintsRemaining . ' left)
            </a>';
        } else {
            $hintButtons .= '
            <button class="btn-hint" disabled>✨ No letter reveals left</button>';
        }
        $hintButtons .= '</div>';

        return '
        <div class="hangman-wrapper">
            <div class="hangman-art">
                <pre>' . e($hangArt) . '</pre>
            </div>
        </div>

        <p style="text-align: center; color: #a0a0b0; margin-bottom: 8px;">Word to guess:</p>
        <div class="word-display">
            ' . $this->formatWordDisplay($wordAsUnderscore) . '
        </div>

        ' . $aiHintHtml . '
        ' . $hintButtons . '

        <div class="game-info" style="margin-top: 15px; text-align: center;">
            <p>Letters guessed already: <strong style="letter-spacing: 2px;">' . e($this->guessedLetters) . '</strong></p>
            <p style="color: #888; font-size: 0.9rem; margin-top: 5px;">Mistakes: <strong>' . e($wrong) . '</strong> / 6</p>
        </div>
        ' . $guessForm;
    }

    private function formatWordDisplay($wordAsUnderscore) {
        $chars = explode(' ', trim($wordAsUnderscore));
        $output = '';
        foreach ($chars as $ch) {
            if ($ch === '_' || $ch === '') {
                $output .= '<span class="letter">_</span> ';
            } else {
                $output .= '<span class="letter revealed">' . e($ch) . '</span> ';
            }
        }
        return $output;
    }
    
    private function guessForm($guessedLetter, $wrong, $currentWordAsIndex) {
        return '
        <div class="form-card" style="margin-top: 20px;">
            <form method="post" action="?" id="guess-form">
               <input type="hidden" name="wrong" value="'. e($wrong) .'"/>
               <input type="hidden" name="lettersGuessed" value="'. e($guessedLetter) .'" />
               <input type="hidden" name="word" value="' . e($currentWordAsIndex) . '"/>
               <fieldset>
                 <legend>🔤 Guess a Letter</legend>
                 <div class="form-group" style="display: flex; gap: 10px; justify-content: center; align-items: center; margin-top: 10px;">
                     <input type="text" name="letter" id="guess-letter-input" maxlength="1" autofocus autocomplete="off" placeholder="?" style="max-width: 90px; text-align: center; font-size: 1.4rem; text-transform: uppercase;" required />
                     <input type="submit" name="Guess" value="Guess" class="btn btn-primary" /> 
                 </div>
                </fieldset>
            </form>
        </div>';
    }

    private function checkGuess() {
        if(($_SERVER["REQUEST_METHOD"] ?? '') == "POST"){
            if(isset($_SESSION["wordsArray"])) {
                $this->wordsArray = $_SESSION["wordsArray"];
            }

            $currentGuess = $_POST["letter"] ?? '';
            $letter = strtoupper(trim($currentGuess));
            $amountOfWrongGuesses = (int)($_POST["wrong"] ?? 0);

            $this->guessedLetters = $_POST["lettersGuessed"] ?? '';
            if ($letter !== '' && !strstr($this->guessedLetters, $letter)) {
                $this->guessedLetters .= $letter;
            }

            $this->randomNumber = $_POST["word"] ?? 0;
            $currentWordAsIndex = (int)($_POST["word"] ?? 0);
            
            $currentWordInGame = $this->wordsArray[$currentWordAsIndex] ?? 'HANGMAN';
            $currentWordInGame = strtoupper($currentWordInGame);
            
            $wordAsUnderscore = $this->checkGuessedLetter($this->guessedLetters, $currentWordInGame);

            if($letter !== '' && !strstr($currentWordInGame, $letter)) {
               $amountOfWrongGuesses++;
            }

            if(!strstr($wordAsUnderscore, "_")) {
                if(isset($_SESSION["isLoggedIn"])) {
                    if(isset($_SESSION["solvedWords"], $_SESSION["totalAmountOfTries"])) {
                        $_SESSION["solvedWords"] += 1;
                        $_SESSION["totalAmountOfTries"] += $amountOfWrongGuesses;
                    } else {
                        $_SESSION["solvedWords"] = 1;
                        $_SESSION["totalAmountOfTries"] = $amountOfWrongGuesses;
                    }
                    $_SESSION["playerHasWon"] = true;
                    if(isset($_SESSION["wordsArray"])) {
                        array_splice($_SESSION["wordsArray"], $this->randomNumber, 1);  
                    } else {
                        array_splice($this->wordsArray, $this->randomNumber, 1);
                        $_SESSION["wordsArray"] = $this->wordsArray;
                    }
                    if(count($_SESSION["wordsArray"]) == 0) {
                        $this->gameHasEndedAddHighscore();
                        return $this->playerHasWonAndNoMoreWords($currentWordInGame, $amountOfWrongGuesses);
                    }
                }
                return $this->playerHasWon($currentWordInGame, $amountOfWrongGuesses);
            } else if($amountOfWrongGuesses >= 6) {
                if(isset($_SESSION["totalAmountOfTries"], $_SESSION["solvedWords"])) {
                    $_SESSION["totalAmountOfTries"] += $amountOfWrongGuesses;
                } else {
                    $_SESSION["totalAmountOfTries"] = $amountOfWrongGuesses;
                    $_SESSION["solvedWords"] = 0;
                }
                $this->gameHasEndedAddHighscore();
                return $this->playerHasLost($currentWordInGame, $letter);
            } else {
                unset($_SESSION["playerHasWon"]);
                return $this->show($this->guessedLetters, $wordAsUnderscore, $amountOfWrongGuesses, $currentWordAsIndex);
            }
        }
    }

    private function useLetterHint() {
        $idx = $_SESSION['solo_current_word_idx'] ?? 0;
        $currentWord = strtoupper($this->wordsArray[$idx] ?? 'HANGMAN');
        $guessed = $_SESSION['solo_guessed'] ?? '';
        $hintsLeft = $_SESSION['solo_hints_remaining'] ?? 2;

        if ($hintsLeft > 0) {
            $wordLetters = array_unique(str_split($currentWord));
            $guessedArr = str_split($guessed);
            $unrevealed = array_values(array_diff($wordLetters, $guessedArr));

            if (!empty($unrevealed)) {
                $revealChar = $unrevealed[array_rand($unrevealed)];
                $guessed .= $revealChar;
                $_SESSION['solo_guessed'] = $guessed;
                $_SESSION['solo_hints_remaining'] = $hintsLeft - 1;
            }
        }

        $wordAsUnderscore = $this->checkGuessedLetter($guessed, $currentWord);
        $wrongCount = (int)($_SESSION['solo_wrong'] ?? 0);

        if (!strstr($wordAsUnderscore, "_")) {
            return $this->playerHasWon($currentWord, $wrongCount);
        }

        return $this->show($guessed, $wordAsUnderscore, $wrongCount, $idx);
    }

    private function checkGuessedLetter($guessedLetters, $currentWordInGame) {
        $lengthOfTheWord = strlen($currentWordInGame);
        $currentGuess = str_repeat("_ ", $lengthOfTheWord);

        for($i = 0; $i < $lengthOfTheWord ; $i++) {
            $ch = $currentWordInGame[$i];
            if(strstr($guessedLetters, $ch)) {
                $pos = 2 * $i;
                $currentGuess[$pos] = $ch;
            }
        }
        return $currentGuess;
    }

    public function checkTheGame() {
        if(isset($_GET["timeout"])) {
            return $this->handleTimeout();
        } else if(isset($_GET["ai_clue"])) {
            $_SESSION["solo_clue_unlocked"] = true;
            $idx = $_SESSION['solo_current_word_idx'] ?? 0;
            $currentWord = strtoupper($this->wordsArray[$idx] ?? 'HANGMAN');
            $guessed = $_SESSION['solo_guessed'] ?? '';
            $wrongCount = (int)($_SESSION['solo_wrong'] ?? 0);
            $wordAsUnderscore = $this->checkGuessedLetter($guessed, $currentWord);
            return $this->show($guessed, $wordAsUnderscore, $wrongCount, $idx);
        } else if(isset($_GET["ai_letter"])) {
            return $this->useLetterHint();
        } else if(isset($_POST["Guess"])) {
            return $this->checkGuess();
        } else if(isset($_POST["playNext"]) || isset($_POST["playAgain"])) {
            return $this->startGame();
        } else {
            return $this->startGame();
        }
    }

    private function handleTimeout() {
        $currentWordInGame = "UNKNOWN";
        if (isset($_SESSION["wordsArray"]) && !empty($_SESSION["wordsArray"])) {
            $currentWordInGame = strtoupper($_SESSION["wordsArray"][0]);
        } else if (!empty($this->wordsArray)) {
            $currentWordInGame = strtoupper($this->wordsArray[0]);
        }

        if (isset($_SESSION["isLoggedIn"])) {
            $_SESSION["totalAmountOfTries"] = ($_SESSION["totalAmountOfTries"] ?? 0) + 6;
            $_SESSION["solvedWords"] = $_SESSION["solvedWords"] ?? 0;
            $this->gameHasEndedAddHighscore();
        }

        return $this->playerHasLost($currentWordInGame, 'TIME_OUT');
    }

    private function startGame() {
        if(isset($_SESSION["playerHasWon"], $_SESSION["wordsArray"])) {
            $this->wordsArray = $_SESSION["wordsArray"];
        } else {
            $this->wordsArray = $this->hangmanWords->getWords();
            if (empty($this->wordsArray)) {
                $this->wordsArray = ['HANGMAN', 'DEVELOPER', 'SECURITY', 'ARCHITECT', 'PHP', 'APPLICATION', 'DATABASE'];
            }
        }

        $this->guessedLetters = '';
        $amountOfWords = count($this->wordsArray);
        $this->randomNumber = rand(0, max(0, $amountOfWords - 1));
        $randomWord = $this->wordsArray[$this->randomNumber] ?? 'HANGMAN';
        $len = strlen($randomWord);
        
        $theWordtoGuess = str_repeat('_ ', $len);
        $this->wordToGuess = $theWordtoGuess;

        // Reset solo AI hint state for the new round
        $_SESSION['solo_hints_remaining'] = 2;
        $_SESSION['solo_clue_unlocked'] = false;
        $_SESSION['solo_current_word_idx'] = $this->randomNumber;
        $_SESSION['solo_guessed'] = '';
        $_SESSION['solo_wrong'] = 0;
    
        return $this->show("", $theWordtoGuess, 0, $this->randomNumber);
    }

    private function playerHasWon($word, $amountOfWrongGuesses) {
        $clue = \model\WordHints::getHintForWord($word);
        return '
        <div class="result-message win" style="text-align: center; margin: 25px 0;">
            <h2>🎉 You Win!</h2>
            <p style="margin-top: 10px;">You correctly guessed: <strong class="revealed-word">' . e($word) . '</strong></p>
            <p style="color: #a0a0b0; margin-top: 5px;">Mistakes made: <strong>' . e($amountOfWrongGuesses) . '</strong>. Great job!</p>
            <div class="hint-text" style="background: rgba(46, 204, 113, 0.15); border-left: 4px solid #2ecc71; padding: 10px 15px; border-radius: 8px; margin: 15px auto; max-width: 450px;">
                💡 <em>"' . e($clue) . '"</em>
            </div>
            <form method="post" style="margin-top: 20px;">
                <input type="submit" name="playNext" value="Next word" class="btn btn-primary" />
            </form>
        </div>';
    }

    private function playerHasLost($word, $letter) {
        $cause = ($letter === 'TIME_OUT') ? '⏱️ Time expired!' : 'Hung by letter: <strong>' . e($letter) . '</strong>';
        $clue = \model\WordHints::getHintForWord($word);
        return '
        <div class="result-message lose" style="text-align: center; margin: 25px 0;">
            <h2>💀 You Lost!</h2>
            <p style="margin-top: 10px;">' . $cause . '</p>
            <p style="margin-top: 5px;">The secret word was: <span class="revealed-word">' . e($word) . '</span></p>
            <div class="hint-text" style="background: rgba(231, 76, 60, 0.15); border-left: 4px solid #e74c3c; padding: 10px 15px; border-radius: 8px; margin: 15px auto; max-width: 450px;">
                💡 <em>"' . e($clue) . '"</em>
            </div>
            <form method="post" style="margin-top: 20px;">
                <input type="submit" name="playAgain" value="Restart" class="btn btn-secondary" />
            </form>
        </div>';
    }

    private function playerHasWonAndNoMoreWords($word, $amountOfWrongGuesses) {
        return '
        <div class="result-message win" style="text-align: center; margin: 25px 0;">
            <h2>🏆 You Beat the Entire Game!</h2>
            <p style="margin-top: 10px;">You guessed the final word: <strong class="revealed-word">' . e($word) . '</strong> with ' . e($amountOfWrongGuesses) . ' wrong guesses!</p>
            <p style="color: #a0a0b0; margin-top: 5px;">There are no more words in this collection.</p>
            <form method="post" style="margin-top: 20px;">
                <input type="submit" name="playAgain" value="Restart" class="btn btn-secondary" />
            </form>
        </div>';
    }

    private function gameHasEndedAddHighscore() {
        if (isset($_SESSION["username"])) {
            $this->highscore->addHighscore($_SESSION["username"], $_SESSION["solvedWords"] ?? 0, $_SESSION["totalAmountOfTries"] ?? 0);
        }
        unset($_SESSION["solvedWords"], $_SESSION["totalAmountOfTries"], $_SESSION["playerHasWon"], $_SESSION["wordsArray"]);
    }
}