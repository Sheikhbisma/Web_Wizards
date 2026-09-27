<?php
/**
 * Template for private/emailCredentials/credentials.php
 *
 * The real file is git-ignored because it holds a Gmail app password
 * and the seeded admin login. Copy this file, fill in the blanks, and
 * keep the copy out of git:
 *
 *     copy private/emailCredentials/credentials.example.php \
 *          private/emailCredentials/credentials.php
 *
 * Constant names must match the real file. private/config/dbconnect.php
 * and private/config/functions.php include this path directly, so a
 * missing file causes a fatal error on any page that loads the config.
 */

define("EMAIL_ADDRESS", "your-gmail-address@gmail.com");

// Gmail app password, not the account password. Needs 2FA on.
// https://myaccount.google.com/apppasswords
define("PASS_KEY", "your-16-char-app-password");

// Seeded admin account created by the signup flow.
define("ADMIN_NAME", "Admin");
define("ADMIN_EMAIL", "admin@example.com");
define("ADMIN_OTP", "000000");
define("ADMIN_VERIFIED", 1);
define("ADMIN_PASSWORD", password_hash("change-me", PASSWORD_DEFAULT));
