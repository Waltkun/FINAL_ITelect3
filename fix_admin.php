<?php
include 'connect2.php';
$email = 'admin@school.edu';
// Make sure this matches EXACTLY what you type in the login box
$hash = password_hash('Admin@123', PASSWORD_DEFAULT); 

$stmt = $conn->prepare("UPDATE admins SET password=? WHERE email=?");
$stmt->bind_param("ss", $hash, $email);
$stmt->execute();

echo "Admin password successfully updated to <b>Admin@123</b>. <br>You can now log in. You may delete this file.";
?>