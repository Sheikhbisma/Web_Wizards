<?php
session_start();
require_once "../config/dbconnect.php";
require_once "../config/functions.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('contact');
}
verify_csrf();

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

if ($name === '' || $email === '' || $message === '') {
    set_flash('error', 'Please fill in your name, email and message.');
    redirect('contact');
}
if (isset($_POST['website']) && $_POST['website'] !== '') {
    redirect('contact');
}

try {
    $pdo->prepare("INSERT INTO contact_messages (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)")
        ->execute([$name, $email, $phone ?: null, $subject ?: null, $message]);
    set_flash('success', 'Thank you! Your message has been received. Our team will reply soon.');
} catch (Exception $e) {
    set_flash('error', 'Sorry, your message could not be sent. Please try again.');
}
redirect('contact');