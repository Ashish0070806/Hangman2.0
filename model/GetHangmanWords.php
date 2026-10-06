<?php

namespace model;

class GetHangmanWords {
    private $link;
    private $words;
    private $dbConnection;

    public function __construct(\model\DatabaseConnection $dbc){
        $this->dbConnection = $dbc;
    }
 
    /**
	 * Connects to DB using DB model and gets all the words in the DB.
	 * Returns default fallback words if database is empty or offline.
	 *
	 * @return array containing all words that can be used in the game.
	 */
    public function getWords() {
        $this->words = [];
        $link = $this->dbConnection->connection();
       
        if ($link) {
            $sql = "SELECT * FROM words";
            $result = @mysqli_query($link, $sql);

            if ($result) {
                while($row = mysqli_fetch_assoc($result)) {
                    array_push($this->words, $row["word"]);
                }
                mysqli_free_result($result);
            }
        }

        if (empty($this->words)) {
            $this->words = ['HANGMAN', 'DEVELOPER', 'SECURITY', 'ARCHITECT', 'PHP', 'APPLICATION', 'DATABASE'];
        }

        return $this->words;
    }
}
