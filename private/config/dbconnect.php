<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$host = 'localhost';
$db   = 'techwiz7'; 
$user = 'root';       
$pass = '';           

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE role = ?");
$stmt->execute(["admin"]);
$selectAdmin = $stmt->fetchAll();

if (empty($selectAdmin)) {
    if (file_exists(__DIR__ . '/../emailCredentials/credentials.php')) {
        include_once __DIR__ . '/../emailCredentials/credentials.php';
        
        $insertData = [
            "username" => ADMIN_NAME,
            "email" => ADMIN_EMAIL,
            "password" => password_hash(ADMIN_PASSWORD, PASSWORD_DEFAULT),
            "otp_code" => ADMIN_OTP ?? '123456',
            "role" => "admin",
            "is_verified" => ADMIN_VERIFIED ?? 1
        ];
        
        $columns = implode(", ", array_keys($insertData));
        $placeholders = ":" . implode(", :", array_keys($insertData));
        
        $sql = "INSERT INTO users ($columns) VALUES ($placeholders)";
        $insertStmt = $pdo->prepare($sql);
        $insertStmt->execute($insertData);
    }
}
$stmt = $pdo->prepare("SELECT * FROM users WHERE role = ?");
$stmt->execute(["farmer"]);
$selectfarmer = $stmt->fetchAll();

if (empty($selectfarmer)) {
        
      $seedFarmer =   $insertData = [
            "username" => "Bilal",
            "email" => "bilal@gmail.com",
            "password" => password_hash("bilal@123", PASSWORD_DEFAULT),
            "otp_code" => 987654,
            "role" => "farmer",
            "is_verified" =>  1
        ];
        
$columns = implode(", ", array_keys($seedFarmer));
        $placeholders = ":" . implode(", :", array_keys($seedFarmer));
        
        $sql = "INSERT INTO users ($columns) VALUES ($placeholders)";
        $insertStmt = $pdo->prepare($sql);
        $insertStmt->execute($seedFarmer);   
         }

?>
