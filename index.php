<?php
session_start();

// Load global security escaping helper and environment
require_once(__DIR__ . '/helpers.php');
@include_once(__DIR__ . '/environment.php');

// Require View Layers
require_once(__DIR__ . '/view/LoginView.php');
require_once(__DIR__ . '/view/DateTimeView.php');
require_once(__DIR__ . '/view/LayoutView.php');
require_once(__DIR__ . '/view/RegisterView.php');
require_once(__DIR__ . '/view/LoggedInView.php');
require_once(__DIR__ . '/view/HangmanView.php');
require_once(__DIR__ . '/view/VersusView.php');
require_once(__DIR__ . '/view/MainLayoutView.php');

// Require Model Layers
require_once(__DIR__ . '/model/DatabaseConnection.php');
require_once(__DIR__ . '/model/Login.php');
require_once(__DIR__ . '/model/Register.php');
require_once(__DIR__ . '/model/HangmanStates.php');
require_once(__DIR__ . '/model/GetHangmanWords.php');
require_once(__DIR__ . '/model/AddHangmanWords.php');
require_once(__DIR__ . '/model/Highscore.php');
require_once(__DIR__ . '/model/VersusGame.php');
require_once(__DIR__ . '/model/WordHints.php');

// Require Controller Layers
require_once(__DIR__ . '/controller/LoginController.php');
require_once(__DIR__ . '/controller/HangmanController.php');
require_once(__DIR__ . '/controller/VersusController.php');
require_once(__DIR__ . '/controller/MainController.php');

// Production error reporting configuration
error_reporting(E_ALL);
ini_set('display_errors', '0');

// Instantiate Model Layer
$dbc = new \model\DatabaseConnection();
$login = new \model\Login($dbc);
$register = new \model\Register($dbc);
$hs = new \model\HangmanStates();
$hw = new \model\GetHangmanWords($dbc);
$ahw = new \model\AddHangmanWords($dbc);
$high = new \model\Highscore($dbc);
$versusGame = new \model\VersusGame();

// Instantiate View Layer
$v = new \view\LoginView($login);
$dtv = new \view\DateTimeView();
$rv = new \view\RegisterView($register);
$liv = new \view\LoggedInView($ahw, $high);
$lv = new \view\LayoutView($v, $dtv, $rv, $liv);
$hv = new \view\HangmanView($hs, $hw, $high);
$vv = new \view\VersusView($hs);

// Instantiate Controller Layer
$loginController = new \controller\LoginController($lv);
$hangmanController = new \controller\HangmanController($hv);
$versusController = new \controller\VersusController($versusGame, $vv);

// Main Layout Composite View
$mlv = new \view\MainLayoutView($hangmanController, $versusController, $loginController);
$mainController = new \controller\MainController($mlv);

// Execute Main Controller Request Pipeline
$mainController->renderMainPage();
