# 🎮 Hangman Game — Secure PHP MVC Web Application

![PHP](https://img.shields.io/badge/PHP-8.0%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0%2B-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Architecture](https://img.shields.io/badge/Architecture-MVC-orange?style=for-the-badge)
![Security](https://img.shields.io/badge/Security-BCRYPT%20%7C%20Prepared%20Stmts%20%7C%20XSS%20Shield-green?style=for-the-badge)
![License](https://img.shields.io/badge/License-MIT-blue?style=for-the-badge)

A full-stack, secure **Hangman Game** built with **PHP (OOP & MVC)**, **MySQL**, and **Vanilla JavaScript**. Designed for Project-Based Learning (PBL) with high security standards, a responsive dark theme, and dual gameplay modes (Solo vs. AI & 1v1 Two-Player Versus).

---

## 🌟 Key Highlights & Features

### 🤖 1. Solo Mode (vs AI)
* **Dynamic Vocabulary:** Pulls words directly from MySQL with fallback dictionary support.
* **Smart AI Hints System:**
  * 💡 **Meaning Clue:** Contextual definitions generated for programming, science, and computer concepts.
  * ✨ **Letter Reveal:** Reveals an unrevealed letter (limited uses per round with state tracking).
  * 🧠 **Structural Clue Fallback:** Analyzes custom user-added words to calculate word length, vowels, and boundary letters.

### 👥 2. 1v1 Two-Player Versus Mode
* **Player Roles:** Players alternate between the **Word Setter** and the **Guesser**.
* **Masked Entry:** Screen-safe password field allows the Word Setter to enter secret words without the Guesser peeking.
* **Difficulty Presets:**
  * 😊 **Easy:** 8 lives · 90s timer · 2 hints
  * 😐 **Medium:** 6 lives · 60s timer · 1 hint
  * 😈 **Hard:** 4 lives · 30s timer · 0 hints
* **Live Scoreboard & Round History:** Tracks head-to-head points and round times across games.

### ⚡ 3. Modern Interactive Gameplay
* **⏱️ Real-time Countdown Timer:** Pure JavaScript countdown that syncs with session state and triggers an automatic timeout loss when the timer hits `00:00`.
* **⌨️ Physical Keyboard Support:** Type directly on your desktop/laptop keyboard to make letter guesses seamlessly.
* **🎨 Responsive Dark Theme UI:** Modern glassmorphism layout, clean typography, ASCII hangman animations, and mobile responsiveness.

---

## 🛡️ Security Architecture & Viva Defense

| Vulnerability | Mitigation Strategy | Implementation Details |
|---|---|---|
| **Plaintext Passwords** | Strong Password Hashing | Enforced `PASSWORD_BCRYPT` with salt via `password_hash()` in `Register.php` and `password_verify()` in `Login.php`. |
| **Cross-Site Scripting (XSS)** | Universal Output Sanitization | Global `e($value)` helper wraps all dynamic outputs using `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`. |
| **SQL Injection** | Prepared Statements | All parameterized queries use `mysqli_prepare()` and `mysqli_stmt_bind_param()`. |
| **Session Fixation** | Session Regeneration | Calls `session_regenerate_id(true)` upon successful user authentication. |
| **Missing DB / Tables** | Self-Healing Schema | `DatabaseConnection.php` auto-provisions the database, tables, and seed words on startup. |

---

## 📁 Project Structure (MVC Architecture)

```
Hangman/
├── index.php                   # Dependency injection container & bootstrap
├── helpers.php                 # Global XSS escaping function e()
├── environment.php             # Database credentials configuration
├── README.md                   # Comprehensive project documentation
├── .gitignore                  # Git exclusions for clean repository
│
├── model/                      # Business Logic & Database Layer
│   ├── DatabaseConnection.php  # Auto-provisions DB schema & tables
│   ├── Login.php               # User login with password_verify()
│   ├── Register.php            # User registration with password_hash()
│   ├── HangmanStates.php       # ASCII hangman drawing state frames
│   ├── GetHangmanWords.php     # Word retrieval from MySQL with fallbacks
│   ├── AddHangmanWords.php     # Adds user-submitted words to DB
│   ├── Highscore.php           # Highscore tracking using prepared statements
│   ├── VersusGame.php          # 1v1 state machine & scoring engine
│   └── WordHints.php           # AI vocabulary clues & structural analysis
│
├── view/                       # Presentation Layer (HTML / Layouts)
│   ├── MainLayoutView.php      # Base HTML shell, mode tabs, JS timer & keyboard listener
│   ├── HangmanView.php         # Solo game UI, AI hints buttons, letter underscores
│   ├── VersusView.php          # 1v1 setup, secret word entry, alphabet grid, scoreboard
│   ├── LayoutView.php          # Authentication view coordinator
│   ├── LoginView.php           # Login form & logout controls
│   ├── RegisterView.php        # User registration form
│   ├── LoggedInView.php        # User dashboard (add words & view top scores)
│   └── DateTimeView.php        # Real-time date and timestamp footer
│
├── controller/                 # Application Flow & Request Handlers
│   ├── MainController.php      # Main view orchestrator
│   ├── HangmanController.php   # Solo game loop controller
│   ├── VersusController.php    # 1v1 action router (guess, hint, timeout, next round)
│   └── LoginController.php     # User authentication controller
│
└── public/
    └── styles.css              # Responsive dark theme stylesheet
```

---

## 🚀 Getting Started & Installation

### Prerequisites
* **XAMPP / WampServer / LAMP** with:
  * **PHP 8.0+** (with `mysqli` extension enabled)
  * **MySQL / MariaDB**
  * **Apache Server**

### Step-by-Step Setup
1. **Clone or Download the Repository:**
   ```bash
   git clone https://github.com/your-username/Hangman.git
   ```
2. Move the `Hangman` folder into your local server root:
   * **XAMPP (Windows):** `C:\xampp\htdocs\Hangman`
   * **Linux:** `/var/www/html/Hangman`
   * **macOS:** `/Applications/XAMPP/htdocs/Hangman`

3. **Start Apache & MySQL:**
   * Open XAMPP Control Panel and click **Start** next to **Apache** and **MySQL**.

4. **Launch the Game:**
   * Open your web browser and navigate to:
     ```
     http://localhost/Hangman/
     ```
   * *Note:* You do **NOT** need to manually import any `.sql` file. `DatabaseConnection.php` automatically provisions the database (`hangman`), tables (`users`, `words`, `highscore`), and seed words on your first visit!

---

## 🎮 How to Play

### 🤖 Solo Mode
1. Click **🤖 Solo (vs AI)**.
2. Guess the word letter-by-letter using the on-screen input or your **physical keyboard**.
3. Use **💡 Ask AI for Meaning Clue** or **✨ Reveal a Letter** if you're stuck.
4. Watch out for the gallows—6 wrong guesses and the game is over!

### 👥 1v1 Versus Mode
1. Click **👥 1v1 Versus (2-Player)**.
2. Enter Player 1 & Player 2 names and choose a difficulty setting.
3. Player 1 enters a secret word and optional hint (masked for privacy).
4. Player 2 guesses the letters using the virtual alphabet grid or physical keyboard.
5. Solve the word before lives or time run out to score a point, then swap roles!

---

## 🛠️ Built With

* **Language:** PHP 8+ (Object-Oriented, Namespaced MVC)
* **Database:** MySQL / MariaDB (Prepared Statements)
* **Frontend:** HTML5, CSS3 (Glassmorphism, Flexbox, Grid), Vanilla JavaScript
* **Development Environment:** XAMPP on Windows

---

## 📜 License

This project is open-source and available under the [MIT License](LICENSE).
