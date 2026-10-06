<?php

namespace model;

class WordHints {

    private static $hints = [
        'HANGMAN'     => 'A classic word-guessing game where every wrong guess draws another part of the gallows!',
        'DEVELOPER'   => 'A software engineer who designs, writes, and debugs computer code.',
        'SECURITY'    => 'Measures taken to protect systems and data against cyber threats and attacks.',
        'ARCHITECT'   => 'A senior engineer who designs high-level software blueprints and systems.',
        'PHP'         => 'A popular open-source server-side scripting language for web development.',
        'APPLICATION' => 'A complete software program designed to perform tasks for end users.',
        'DATABASE'    => 'An organized system for storing, managing, and retrieving structured data.',
        'JAVASCRIPT'  => 'The universal scripting language that powers interactive frontend web pages.',
        'COMPUTER'    => 'An electronic programmable machine that calculates, stores, and processes information.',
        'ALGORITHM'   => 'A finite sequence of step-by-step instructions to solve a specific problem.',
        'NETWORK'     => 'A collection of interconnected computers and devices that share resources.',
        'ENCRYPTION'  => 'The cryptographic process of encoding messages so only authorized parties can read them.',
        'PASSWORD'    => 'A confidential secret string used to authenticate a user identity.'
    ];

    /**
     * Returns an intelligent contextual hint or generates a structural clue for custom words.
     * 
     * @param string $word
     * @return string
     */
    public static function getHintForWord($word) {
        $upper = strtoupper(trim($word));
        if (isset(self::$hints[$upper])) {
            return self::$hints[$upper];
        }

        // Structural smart fallback clue for custom words added to the database:
        $len = strlen($upper);
        $vowels = preg_match_all('/[AEIOU]/', $upper);
        $first = $upper[0] ?? '';
        $last = ($len > 1) ? $upper[$len - 1] : $first;

        return "Word length: {$len} letters. Starts with '{$first}', ends with '{$last}', containing {$vowels} vowel(s).";
    }
}
