<?php

namespace model;

class VersusGame {

    private $difficultyConfig = [
        'easy'   => ['lives' => 8, 'time' => 90, 'hints' => 2],
        'medium' => ['lives' => 6, 'time' => 60, 'hints' => 1],
        'hard'   => ['lives' => 4, 'time' => 30, 'hints' => 0]
    ];

    public function initGame($p1, $p2, $difficulty) {
        $_SESSION['versus'] = [
            'player1'         => $p1,
            'player2'         => $p2,
            'score_p1'        => 0,
            'score_p2'        => 0,
            'setter'          => 1, // 1 = Player 1 sets, 2 = Player 2 sets
            'difficulty'      => $difficulty,
            'state'           => 'word_entry', // 'setup', 'word_entry', 'play', 'result'
            'round_history'   => []
        ];
    }

    public function setWord($word, $category, $hint) {
        if (!isset($_SESSION['versus'])) return;

        $diff = $_SESSION['versus']['difficulty'] ?? 'medium';
        $cfg = $this->difficultyConfig[$diff] ?? $this->difficultyConfig['medium'];

        $_SESSION['versus']['word']            = strtoupper(trim($word));
        $_SESSION['versus']['category']        = $category;
        $_SESSION['versus']['hint_text']       = trim($hint);
        $_SESSION['versus']['guessed']         = [];
        $_SESSION['versus']['lives']           = $cfg['lives'];
        $_SESSION['versus']['max_lives']       = $cfg['lives'];
        $_SESSION['versus']['time_limit']      = $cfg['time'];
        $_SESSION['versus']['start_time']      = time();
        $_SESSION['versus']['hints_remaining'] = $cfg['hints'];
        $_SESSION['versus']['state']           = 'play';
        $_SESSION['versus']['hint_revealed']   = false;
    }

    public function guessLetter($letter) {
        if (!isset($_SESSION['versus']) || $_SESSION['versus']['state'] !== 'play') return;

        $letter = strtoupper(trim($letter));
        if ($letter === '' || in_array($letter, $_SESSION['versus']['guessed'])) return;

        $_SESSION['versus']['guessed'][] = $letter;

        if (strpos($_SESSION['versus']['word'], $letter) === false) {
            $_SESSION['versus']['lives']--;
        }

        $this->evaluateRoundStatus();
    }

    public function useHint() {
        if (!isset($_SESSION['versus']) || $_SESSION['versus']['state'] !== 'play') return;
        if (($_SESSION['versus']['hints_remaining'] ?? 0) <= 0) return;

        $wordLetters = array_unique(str_split($_SESSION['versus']['word']));
        $unrevealed = array_values(array_diff($wordLetters, $_SESSION['versus']['guessed']));

        if (!empty($unrevealed)) {
            $randomLetter = $unrevealed[array_rand($unrevealed)];
            $_SESSION['versus']['guessed'][] = $randomLetter;
            $_SESSION['versus']['hints_remaining']--;
            $_SESSION['versus']['lives']--;
            $_SESSION['versus']['hint_revealed'] = true;
        }

        $this->evaluateRoundStatus();
    }

    public function triggerTimeout() {
        if (!isset($_SESSION['versus']) || $_SESSION['versus']['state'] !== 'play') return;
        $this->endRound('timeout');
    }

    public function evaluateRoundStatus() {
        if (!isset($_SESSION['versus']) || $_SESSION['versus']['state'] !== 'play') return;

        $elapsed = time() - $_SESSION['versus']['start_time'];
        if ($elapsed >= $_SESSION['versus']['time_limit']) {
            $this->endRound('timeout');
            return;
        }

        $wordLetters = array_unique(str_split($_SESSION['versus']['word']));
        $allRevealed = empty(array_diff($wordLetters, $_SESSION['versus']['guessed']));

        if ($allRevealed) {
            $this->endRound('win');
            return;
        }

        if ($_SESSION['versus']['lives'] <= 0) {
            $this->endRound('lose');
            return;
        }
    }

    private function endRound($result) {
        $setter = $_SESSION['versus']['setter'];
        $setterName = ($setter === 1) ? $_SESSION['versus']['player1'] : $_SESSION['versus']['player2'];
        $guesserName = ($setter === 1) ? $_SESSION['versus']['player2'] : $_SESSION['versus']['player1'];

        $timeTaken = time() - $_SESSION['versus']['start_time'];

        if ($result === 'win') {
            $winner = $guesserName;
            $winKey = ($setter === 1) ? 'score_p2' : 'score_p1';
            $_SESSION['versus'][$winKey]++;
        } else {
            $winner = $setterName;
            $winKey = ($setter === 1) ? 'score_p1' : 'score_p2';
            $_SESSION['versus'][$winKey]++;
        }

        $_SESSION['versus']['last_result'] = [
            'winner'     => $winner,
            'result'     => $result,
            'word'       => $_SESSION['versus']['word'],
            'time_taken' => $timeTaken,
            'guesser'    => $guesserName,
            'setter'     => $setterName
        ];

        $_SESSION['versus']['round_history'][] = [
            'winner'   => $winner,
            'word'     => $_SESSION['versus']['word'],
            'category' => $_SESSION['versus']['category'],
            'time'     => ($result === 'timeout') ? "Time's up!" : "{$timeTaken}s"
        ];

        $_SESSION['versus']['state'] = 'result';
    }

    public function nextRoundSwap() {
        if (!isset($_SESSION['versus'])) return;
        $_SESSION['versus']['setter'] = ($_SESSION['versus']['setter'] === 1) ? 2 : 1;
        $_SESSION['versus']['state']  = 'word_entry';
        unset($_SESSION['versus']['word'], $_SESSION['versus']['guessed'], $_SESSION['versus']['last_result']);
    }

    public function resetGame() {
        unset($_SESSION['versus']);
    }

    public function getState() {
        return $_SESSION['versus'] ?? null;
    }
}
