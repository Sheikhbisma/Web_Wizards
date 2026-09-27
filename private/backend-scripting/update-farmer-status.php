<?php

session_start();
require_once "../config/dbconnect.php";
require_once "../config/functions.php";
if(isset($_POST['update_status'])){
    verify_csrf();
    $farmer_id = $_POST['farmer_id'];
    $status = $_POST['approval_status'];
    if(!empty($farmer_id)){
        $updateStatus = $pdo->prepare("Update farmers set approval_status = ? where farmer_id = ?");
        $updateStatus->execute([$status,$farmer_id]);
        if($updateStatus){
            set_flash("success" , "status updated");
            redirect("manage-farmers");
        }else{
            set_flash("error" , "status not Approved");
            redirect("manage-farmers");
        }
    }else{
        set_flash("error" , "farmer doesnot exist");
            redirect("manage-farmers");
    }
}


?>