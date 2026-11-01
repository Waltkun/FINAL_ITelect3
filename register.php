<?php
include 'connect2.php';
session_start();

// SIGN UP
if (isset($_POST['SignUp'])) {
    $firstName = trim($_POST['fName']);
    $lastName  = trim($_POST['lName']);
    $email     = trim($_POST['email']);
    $password  = $_POST['password'];
    $confirm   = $_POST['confirm_password'];

    if ($password !== $confirm) {
        header("Location: index2.php?error=match&type=register");
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        header("Location: index2.php?error=exists&type=register");
        exit();
    } else {
        $stmt = $conn->prepare("INSERT INTO users (firstName, lastName, email, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $firstName, $lastName, $email, $hashed_password);

        if ($stmt->execute()) {
            header("Location: index2.php?registered=1");
            exit();
        } else {
            header("Location: index2.php?error=db&type=register");
            exit();
        }
    }
}

// SIGN IN
if (isset($_POST['SignIn'])) {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, email, firstName, lastName, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if ($row && password_verify($password, $row['password'])) {
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['email'] = $row['email'];
        $_SESSION['firstName'] = $row['firstName'];
        $_SESSION['lastName'] = $row['lastName'];
        header("Location: homepage2.php");
        exit();
    } else {
        // Redirect back with an invalid error
        header("Location: index2.php?error=invalid");
        exit();
    }
}
?>