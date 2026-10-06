<?php

namespace model;

class Highscore{

    private $link;
    private $highscores;
    private $dbConnection;

    public function __construct(\model\DatabaseConnection $dbc){
        $this->dbConnection = $dbc;
    }

    /**
	 * Connects to DB using DB model and adds a highscore entry based on player name.
     *
	 * @param string $playerName name of the current logged in player
	 * @param int $solvedWords amount of solved words
	 * @param int $totalAmountOfTries total amount of tries during the whole game
	 * @return bool
    **/
    public function addHighscore($playerName, $solvedWords, $totalAmountOfTries){
        $this->link = $this->dbConnection->connection();
        if (!$this->link) {
            return false;
        }

        $query = "INSERT INTO highscore (playerName, solvedWords, totalAmountOfTries) VALUES (?,?,?)";
        $stmt = mysqli_prepare($this->link, $query);
        if (!$stmt) {
            return false;
        }

        mysqli_stmt_bind_param($stmt, "sii", $param_playerName, $param_solvedWords, $param_totalAmountOfTries);
        $param_playerName = $playerName;
        $param_solvedWords = (int)$solvedWords;
        $param_totalAmountOfTries = (int)$totalAmountOfTries;

        $res = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        return $res;
    }

    /**
	 * Connects to DB and receives top 3 highscores for player.
     *
     * @param string $username name of the player to receive highscores for
	 * @return array
	 **/
    public function getPlayerHighscore($username){
        $this->highscores = [];
        $this->link = $this->dbConnection->connection();
        if (!$this->link) {
            return [];
        }

        $stmt = mysqli_prepare($this->link, "SELECT solvedWords, totalAmountOfTries FROM highscore WHERE playerName = ? ORDER BY solvedWords DESC, totalAmountOfTries ASC LIMIT 3");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            while($obj = mysqli_fetch_object($result)) {
                array_push($this->highscores, $obj);
            }
            mysqli_stmt_close($stmt);
        }

        return $this->highscores;
    }
}
