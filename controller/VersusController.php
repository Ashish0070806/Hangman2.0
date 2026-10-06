<?php

namespace controller;

class VersusController {

    private $versusModel;
    private $versusView;

    public function __construct(\model\VersusGame $versusModel, \view\VersusView $versusView) {
        $this->versusModel = $versusModel;
        $this->versusView  = $versusView;
    }

    public function render() {
        $error = '';
        $action = $_POST['action'] ?? ($_GET['action'] ?? '');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($action === 'start_versus') {
                $p1 = trim($_POST['player1'] ?? '');
                $p2 = trim($_POST['player2'] ?? '');
                $diff = $_POST['difficulty'] ?? 'medium';

                if (empty($p1) || empty($p2)) {
                    $error = 'Both player names are required!';
                } elseif (strcasecmp($p1, $p2) === 0) {
                    $error = 'Player names must be different!';
                } else {
                    $this->versusModel->initGame($p1, $p2, $diff);
                    header('Location: ?mode=1v1');
                    exit;
                }
            } elseif ($action === 'set_word') {
                $word = trim($_POST['word'] ?? '');
                $category = $_POST['category'] ?? 'Custom';
                $hint = trim($_POST['hint'] ?? '');

                if (empty($word)) {
                    $error = 'Please enter a secret word!';
                } elseif (!preg_match('/^[a-zA-Z]+$/', $word)) {
                    $error = 'Secret word must contain only letters (A-Z)!';
                } elseif (strlen($word) < 3 || strlen($word) > 15) {
                    $error = 'Secret word must be between 3 and 15 letters long!';
                } else {
                    $this->versusModel->setWord($word, $category, $hint);
                    header('Location: ?mode=1v1');
                    exit;
                }
            } elseif ($action === 'guess_letter') {
                $letter = $_POST['letter'] ?? '';
                $this->versusModel->guessLetter($letter);
                header('Location: ?mode=1v1');
                exit;
            }
        }

        // GET actions
        if ($action === 'use_hint') {
            $this->versusModel->useHint();
            header('Location: ?mode=1v1');
            exit;
        } elseif ($action === 'next_round') {
            $this->versusModel->nextRoundSwap();
            header('Location: ?mode=1v1');
            exit;
        } elseif ($action === 'reset') {
            $this->versusModel->resetGame();
            header('Location: ?mode=1v1');
            exit;
        } elseif (isset($_GET['timeout'])) {
            $this->versusModel->triggerTimeout();
            header('Location: ?mode=1v1');
            exit;
        }

        $state = $this->versusModel->getState();
        return $this->versusView->render($state, $error);
    }
}
