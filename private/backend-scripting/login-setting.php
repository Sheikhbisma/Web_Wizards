<?php
session_start();
require_once "../config/dbconnect.php";
require_once "../config/functions.php";
   
if (isset($_POST['login'])) {
    verify_csrf();

    $username = $_POST['username'];
    $password = $_POST['password'];
 
$selectQuery = "SELECT * FROM users WHERE (email = ? OR username = ?) AND is_verified = ?";   
 $bindParams = [$username, $username, 1];
    $usersDetails =  selectData($pdo, $selectQuery, $bindParams);
    if (!empty($usersDetails)) {
        $loginEmail = $usersDetails[0]["email"];
        $basERole = $usersDetails[0]['role'];
        if ($usersDetails[0]['status'] === 'deactivated') {
            set_flash("error", "Your account has been deactivated. Please contact support.");
            $_SESSION['signupactive'] = false;
            redirect("signup");
        }
        if (password_verify($password, $usersDetails[0]['password'])) {
            session_regenerate_id(true);
            $_SESSION['loggedIn']=true;
            $_SESSION['role'] = $basERole;
            $_SESSION['email']=$loginEmail;
            $_SESSION['user_id']=$usersDetails[0]['id'];
            $_SESSION['username']=$usersDetails[0]['username'];
            if (!empty($_POST['remember'])) {
                $token = bin2hex(random_bytes(24));
                $pdo->prepare("UPDATE users SET remember_token = ? WHERE id = ?")->execute([$token, $usersDetails[0]['id']]);
                setcookie('ml_remember', $usersDetails[0]['id'] . ':' . $token, time() + 60 * 60 * 24 * 30, '/', '', false, true);
            }
            if ($basERole == "customer") {
                redirect("customer-dashboard");
            } elseif ($basERole == "farmer") {
                redirect("farmer-dashboard");
            } elseif ($basERole == "admin") {
                redirect("admin-dashboard");
            } else {
                set_flash("error", "Invalid Role");
                $_SESSION['signupactive'] = false;
                redirect("signup");
            }
        } else {
            set_flash("error", "Invalid Password");
            $_SESSION['signupactive'] = false;
            redirect("signup");
        }
    } else {
        set_flash("error", "Email Not Exist Or you Are Not Verified!");
        $_SESSION['signupactive'] = false;
        redirect("signup");
    }
}


?>