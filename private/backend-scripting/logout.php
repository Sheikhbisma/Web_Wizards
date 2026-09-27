<?php


session_start();
session_unset();
session_destroy();
redirect("signup");
$_SESSION['signupactive']=false;
