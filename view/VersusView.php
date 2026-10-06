<?php

namespace view;

class VersusView {

    private $hangmanStates;

    public function __construct(\model\HangmanStates $hangmanStates) {
        $this->hangmanStates = $hangmanStates;
    }

    public function render($state, $error = '') {
        if (!$state || ($state['state'] ?? '') === 'setup') {
            return $this->renderSetupForm($error);
        }

        switch ($state['state']) {
            case 'word_entry':
                return $this->renderWordEntryForm($state, $error);
            case 'play':
                return $this->renderPlayScreen($state);
            case 'result':
                return $this->renderResultScreen($state);
            default:
                return $this->renderSetupForm($error);
        }
    }

    private function renderSetupForm($error) {
        $diffs = [
            'easy'   => ['label' => '😊 Easy',   'detail' => '8 lives · 90s · 2 hints'],
            'medium' => ['label' => '😐 Medium', 'detail' => '6 lives · 60s · 1 hint'],
            'hard'   => ['label' => '😈 Hard',   'detail' => '4 lives · 30s · 0 hints']
        ];
        $selectedDiff = $_POST['difficulty'] ?? 'medium';

        $errorHtml = $error ? '<div class="error" style="margin-bottom: 15px;">' . e($error) . '</div>' : '';

        $diffOptions = '';
        foreach ($diffs as $key => $d) {
            $checked = ($selectedDiff === $key) ? 'checked' : '';
            $diffOptions .= '
            <div class="difficulty-option">
                <input type="radio" id="diff-' . $key . '" name="difficulty" value="' . $key . '" ' . $checked . '>
                <label for="diff-' . $key . '">
                    ' . $d['label'] . '
                    <span class="diff-detail">' . $d['detail'] . '</span>
                </label>
            </div>';
        }

        return '
        <div class="auth-box">
            <h2>👥 1v1 Two-Player Versus Setup</h2>
            <p class="subtitle">Take turns setting secret words and guessing them!</p>
            ' . $errorHtml . '
            <form method="post" action="?mode=1v1">
                <input type="hidden" name="action" value="start_versus">

                <div class="form-group">
                    <label for="p1">🔴 Player 1 Name :</label>
                    <input type="text" id="p1" name="player1" value="' . e($_POST['player1'] ?? 'Player 1') . '" maxlength="20" required>
                </div>

                <div class="form-group">
                    <label for="p2">🔵 Player 2 Name :</label>
                    <input type="text" id="p2" name="player2" value="' . e($_POST['player2'] ?? 'Player 2') . '" maxlength="20" required>
                </div>

                <div class="form-group" style="margin-top: 15px;">
                    <label>🎯 Round Difficulty :</label>
                    <div class="difficulty-options">
                        ' . $diffOptions . '
                    </div>
                </div>

                <div class="btn-group" style="margin-top: 25px;">
                    <input type="submit" value="▶ Begin 1v1 Match" class="btn btn-primary btn-block">
                </div>
            </form>
        </div>';
    }

    private function renderWordEntryForm($state, $error) {
        $setterName = ($state['setter'] === 1) ? $state['player1'] : $state['player2'];
        $guesserName = ($state['setter'] === 1) ? $state['player2'] : $state['player1'];
        $categories = ['🐾 Animals', '🍎 Fruits', '🌍 Countries', '💻 Technology', '⚽ Sports', '✏️ Custom'];

        $errorHtml = $error ? '<div class="error" style="margin-bottom: 15px;">' . e($error) . '</div>' : '';

        $catOptions = '';
        foreach ($categories as $cat) {
            $catOptions .= '<option value="' . e($cat) . '">' . e($cat) . '</option>';
        }

        return '
        <div class="auth-box">
            <h2>🔤 Set Secret Word</h2>
            <p class="subtitle">🎯 <strong>' . e($setterName) . '</strong>, enter your secret word for <strong>' . e($guesserName) . '</strong>!</p>

            <div class="warning-box" style="margin-bottom: 20px;">
                ⚠️ <strong>' . e($guesserName) . '</strong>, look away while the secret word is being entered!
            </div>

            ' . $errorHtml . '

            <form method="post" action="?mode=1v1">
                <input type="hidden" name="action" value="set_word">

                <div class="form-group">
                    <label for="category">📂 Category :</label>
                    <select id="category" name="category">
                        ' . $catOptions . '
                    </select>
                </div>

                <div class="form-group">
                    <label for="word">🔒 Secret Word (3–15 letters) :</label>
                    <input type="password" id="word" name="word" placeholder="Enter word" maxlength="15" autocomplete="off" required>
                </div>

                <div class="form-group">
                    <label for="hint">💡 Optional Hint for Guesser :</label>
                    <input type="text" id="hint" name="hint" placeholder="e.g. It is yellow and tropical" maxlength="80">
                </div>

                <div class="btn-group" style="margin-top: 25px;">
                    <input type="submit" value="🎮 Start Round" class="btn btn-primary btn-block">
                </div>
            </form>
        </div>';
    }

    private function renderPlayScreen($state) {
        $setterName  = ($state['setter'] === 1) ? $state['player1'] : $state['player2'];
        $guesserName = ($state['setter'] === 1) ? $state['player2'] : $state['player1'];
        $word        = $state['word'];
        $guessed     = $state['guessed'];
        $lives       = $state['lives'];
        $maxLives    = $state['max_lives'];
        $category    = $state['category'];
        $hintsLeft   = $state['hints_remaining'];

        // Calculate wrong guesses
        $wrongGuesses = array_filter($guessed, fn($l) => strpos($word, $l) === false);
        $mistakes = count($wrongGuesses);

        // Word display
        $wordLetters = str_split($word);
        $wordHtml = '';
        foreach ($wordLetters as $ch) {
            if (in_array($ch, $guessed)) {
                $wordHtml .= '<span class="letter revealed">' . e($ch) . '</span> ';
            } else {
                $wordHtml .= '<span class="letter">_</span> ';
            }
        }

        // Lives display
        $hearts = '';
        for ($i = 0; $i < $maxLives; $i++) {
            $hearts .= ($i < $lives) ? '❤️ ' : '<span class="heart-lost">🤍 </span>';
        }

        // Hangman art
        $hangArt = $this->hangmanStates->hang[min($mistakes, count($this->hangmanStates->hang) - 1)] ?? '';

        // Alphabet grid
        $alphabetHtml = '';
        for ($i = 0; $i < 26; $i++) {
            $char = chr(65 + $i);
            $isGuessed = in_array($char, $guessed);
            $cls = 'letter-btn';
            if ($isGuessed) {
                $cls .= (strpos($word, $char) !== false) ? ' correct' : ' wrong';
                $alphabetHtml .= '<span class="' . $cls . '">' . $char . '</span>';
            } else {
                $alphabetHtml .= '
                <form method="post" action="?mode=1v1" style="display:inline;">
                    <input type="hidden" name="action" value="guess_letter">
                    <input type="hidden" name="letter" value="' . $char . '">
                    <button type="submit" class="' . $cls . '">' . $char . '</button>
                </form>';
            }
        }

        // Hint button and hint text
        $hintBtnHtml = ($hintsLeft > 0)
            ? '<a href="?mode=1v1&action=use_hint" class="btn-hint">💡 Use Hint (' . $hintsLeft . ' remaining · costs 1 ❤️)</a>'
            : '<button class="btn-hint" disabled>💡 No hints left</button>';

        $hintRevealedHtml = ($state['hint_revealed'] ?? false && !empty($state['hint_text']))
            ? '<div class="hint-text" style="margin-top:10px;">💬 Clue: "' . e($state['hint_text']) . '"</div>'
            : '';

        return '
        <div class="scoreboard" style="margin-bottom: 20px;">
            <div class="score-card">
                <div class="player-name-label">🔴 ' . e($state['player1']) . '</div>
                <div class="score-value">' . $state['score_p1'] . '</div>
            </div>
            <div class="score-card">
                <div class="player-name-label">🔵 ' . e($state['player2']) . '</div>
                <div class="score-value">' . $state['score_p2'] . '</div>
            </div>
        </div>

        <div class="game-info" style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 15px;">
            <span class="player-name">🎯 <strong>' . e($guesserName) . '\'s</strong> turn to guess</span>
            <span class="category-badge">' . e($category) . '</span>
            <span class="difficulty-badge ' . e($state['difficulty']) . '">' . ucfirst(e($state['difficulty'])) . '</span>
        </div>

        <div class="hangman-wrapper">
            <div class="hangman-art">
                <pre>' . e($hangArt) . '</pre>
            </div>
        </div>

        <div class="word-display">
            ' . $wordHtml . '
        </div>

        <div class="lives" style="text-align: center; margin: 15px 0;">
            ' . $hearts . '
            <div style="font-size: 0.85rem; color: #888; margin-top: 5px;">' . $lives . ' / ' . $maxLives . ' lives</div>
        </div>

        <div class="hint-section" style="margin-bottom: 20px;">
            ' . $hintBtnHtml . '
            ' . $hintRevealedHtml . '
        </div>

        <div class="alphabet-grid">
            ' . $alphabetHtml . '
        </div>';
    }

    private function renderResultScreen($state) {
        $last = $state['last_result'];
        $isWin = ($last['result'] === 'win');
        $messageClass = $isWin ? 'win' : 'lose';
        $icon = $isWin ? '🎉' : '💀';

        $messageText = ($last['result'] === 'win')
            ? e($last['winner']) . ' solved the secret word!'
            : (($last['result'] === 'timeout') ? "Time's up! " . e($last['winner']) . ' wins!' : e($last['guesser']) . ' ran out of lives! ' . e($last['winner']) . ' wins!');

        $nextSetter = ($state['setter'] === 1) ? 2 : 1;
        $nextSetterName = ($nextSetter === 1) ? $state['player1'] : $state['player2'];

        $historyHtml = '';
        if (!empty($state['round_history'])) {
            $historyHtml .= '
            <h3 style="text-align: center; margin-top: 25px;">📜 Round History</h3>
            <table class="history-table" style="width: 100%; margin-top: 10px;">
                <thead>
                    <tr><th>#</th><th>Winner</th><th>Word</th><th>Category</th><th>Time</th></tr>
                </thead>
                <tbody>';
            foreach ($state['round_history'] as $idx => $round) {
                $historyHtml .= '
                <tr>
                    <td>' . ($idx + 1) . '</td>
                    <td>' . e($round['winner']) . '</td>
                    <td style="font-family:monospace; letter-spacing:2px;">' . e($round['word']) . '</td>
                    <td>' . e($round['category']) . '</td>
                    <td>' . e($round['time']) . '</td>
                </tr>';
            }
            $historyHtml .= '</tbody></table>';
        }

        return '
        <div class="result-message ' . $messageClass . '" style="text-align: center; margin-bottom: 20px;">
            <h2>' . $icon . ' ' . $messageText . '</h2>
        </div>

        <p style="text-align: center; color: #888; margin-bottom: 5px;">The word was:</p>
        <div class="revealed-word" style="text-align: center; font-size: 2rem; font-weight: bold; letter-spacing: 4px; color: #e94560; margin-bottom: 20px;">
            ' . e($last['word']) . '
        </div>

        <div class="scoreboard" style="margin-bottom: 20px;">
            <div class="score-card">
                <div class="player-name-label">🔴 ' . e($state['player1']) . '</div>
                <div class="score-value">' . $state['score_p1'] . '</div>
            </div>
            <div class="score-card">
                <div class="player-name-label">🔵 ' . e($state['player2']) . '</div>
                <div class="score-value">' . $state['score_p2'] . '</div>
            </div>
        </div>

        ' . $historyHtml . '

        <div class="btn-group" style="display: flex; gap: 15px; justify-content: center; margin-top: 30px;">
            <a href="?mode=1v1&action=next_round" class="btn btn-primary">
                🔄 Next Round (' . e($nextSetterName) . ' sets word)
            </a>
            <a href="?mode=1v1&action=reset" class="btn btn-secondary">
                🏠 New 1v1 Game
            </a>
        </div>';
    }
}
