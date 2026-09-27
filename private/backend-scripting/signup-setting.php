<?php
session_start();
require_once "../config/dbconnect.php";
require_once "../config/functions.php";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $otp = random_int(100000, 999999);

    $name = $_POST['name'];
    $email = $_POST['email'];
    $contact = $_POST['contact'];
    $raw_password = $_POST['password'];
    $role = $_POST['role'];
    $confirm_password = $_POST['confirm_password'] ?? '';
    $otp_expire = time() + 60;
    $hashed_password = password_hash($raw_password, PASSWORD_DEFAULT);

    if ($raw_password !== $confirm_password) {
        $_SESSION['old_input'] = $_POST;
        $_SESSION['signupactive'] = true;
        set_flash("error", "Password and confirm password do not match");
        redirect("signup");
    }
    if (empty($_POST['terms'])) {
        $_SESSION['old_input'] = $_POST;
        $_SESSION['signupactive'] = true;
        set_flash("error", "Please accept the terms and conditions");
        redirect("signup");
    }

    if ($role == "customer") {
        $address = trim($_POST['address'] ?? '');
        if ($address == "") {
            $_SESSION['old_input'] = $_POST;
            $_SESSION['signupactive'] = true;
            set_flash("error", "Please provide your delivery area / address");
            redirect("signup");
        }
    }
    if ($role == "farmer") {
        $stallName = trim($_POST['stall_name'] ?? '');
        $contactPerson = trim($_POST['contact_person'] ?? '');
        $address = trim($_POST['address'] ?? '');
        if ($stallName == "" || $contactPerson == "" || $address == "") {
            $_SESSION['old_input'] = $_POST;
            $_SESSION['signupactive'] = true;
            set_flash("error", "Farmers must provide stall name, contact person and address");
            redirect("signup");
        }
    }
    $check_stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $check_stmt->execute(['email' => $email]);
    $db_user = $check_stmt->fetch(PDO::FETCH_ASSOC);
    if ($db_user) {
        $_SESSION['old_input'] = $_POST;
        $_SESSION['signupactive'] = true;
        set_flash("error", "Email Already Exist Try With New Email");
        redirect("signup");
    }
    $insertData = [
        "username" => $name,
        "contact" => $contact,
        "email" => $email,
        "password" => $hashed_password,
        "otp_code" => $otp,
        "role" => $role,
        "is_verified" => 0
    ];

    if (insertData($pdo, "users", $insertData)) {

        $_SESSION['email'] = $email;
        $_SESSION['otp'] = $otp;
        $_SESSION['otp_expire'] = $otp_expire;

        if ($role == "farmer") {
            $newUser = selectData($pdo, "SELECT id FROM users WHERE email = ?", [$email]);
            if (!empty($newUser)) {
                insertData($pdo, "farmers", [
                    "user_id" => $newUser[0]['id'],
                    "stall_name" => $stallName,
                    "contact_person" => $contactPerson,
                    "address" => $address,
                    "approval_status" => "pending"
                ]);
            }
        }
        if ($role == "customer") {
            $newUser = selectData($pdo, "SELECT id FROM users WHERE email = ?", [$email]);
            if (!empty($newUser)) {
                insertData($pdo, "customers", [
                    "user_id" => $newUser[0]['id'],
                    "full_name" => $name,
                    "address" => $address
                ]);
                addNotif($pdo, $newUser[0]['id'], 'Welcome to MarketLink', 'Your account is created. Verify your email to start pre-ordering fresh produce.', 'announcement');
            }
        }
        if (sentOtp($email, $otp)) {
            $_SESSION['signupactive'] = false;
            redirect("verify-otp");
        } else {
            $_SESSION['old_input'] = $_POST;
            $_SESSION['signupactive'] = true;
            set_flash("error", "Email Not send");
            redirect("signup");
        }
    } else {
        $_SESSION['old_input'] = $_POST;
        $_SESSION['signupactive'] = true;
        set_flash("error", "Registration Failed Try Again!");
        redirect("signup");
    }
} else {
    $_SESSION['old_input'] = $_POST;
    $isSignUpActive = true;
    redirect("signup");
}



