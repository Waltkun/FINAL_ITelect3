<?php
include 'connect2.php';
session_start();

// If already logged in, go to dashboard
if (isset($_SESSION['admin_email'])) {
    header("Location: admin_home.php");
    exit();
}

$msg = "";
$msg_type = "error";

// REGISTER ADMIN
if (isset($_POST['RegisterAdmin'])) {
    $email = trim($_POST['reg_email']);
    $password = $_POST['reg_password'];
    $confirm = $_POST['reg_confirm'];

    if ($password !== $confirm) {
        $msg = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $msg = "Password must be at least 6 characters.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM admins WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $msg = "Admin email already exists!";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $insert = $conn->prepare("INSERT INTO admins (email, password) VALUES (?, ?)");
            $insert->bind_param("ss", $email, $hashed);
            if ($insert->execute()) {
                $msg = "Admin registered successfully! You can now log in.";
                $msg_type = "success";
            } else {
                $msg = "Error creating admin account.";
            }
        }
    }
}

// LOGIN ADMIN
if (isset($_POST['LoginAdmin'])) {
    $email = trim($_POST['log_email']);
    $password = $_POST['log_password'];

    $stmt = $conn->prepare("SELECT id, email, password FROM admins WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if ($row && password_verify($password, $row['password'])) {
        $_SESSION['admin_email'] = $row['email'];
        header("Location: admin_home.php");
        exit();
    } else {
        $msg = "Invalid Admin Credentials.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal - Login & Register</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style2.css">
</head>
<body>

<div class="wrapper" id="wrapper">
    <!-- ADMIN LOGIN -->
    <div class="form-box login" id="signIn">
        <h1 class="form-title">Admin Log In</h1>
        
        <?php if($msg && !isset($_POST['RegisterAdmin'])): ?>
            <div class="message <?= $msg_type ?>"><?= $msg ?></div>
        <?php endif; ?>

        <form method="post" action="">
            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="log_email" id="log-email" placeholder="Email" required>
                <label for="log-email">Admin Email</label>
            </div>

            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="log_password" id="log-password" placeholder="Password" required>
                <label for="log-password">Password</label>
                <button type="button" class="toggle-pw" data-target="log-password">
                    <i class="fas fa-eye"></i>
                </button>
            </div>

            <input type="submit" class="button" value="Sign In" name="LoginAdmin">
        </form>
        
        <div style="text-align: center; margin-top: 15px;">
            <a href="index2.php" style="color: var(--muted); font-size: 0.9rem;">Back to Student Portal</a>
        </div>
    </div>

    <!-- ADMIN REGISTER -->
    <div class="form-box register" id="signUp" inert>
        <h1 class="form-title">Register Admin</h1>
        
        <?php if($msg && isset($_POST['RegisterAdmin'])): ?>
            <div class="message <?= $msg_type ?>"><?= $msg ?></div>
        <?php endif; ?>

        <form method="post" action="">
            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="reg_email" id="reg-email" placeholder="Email" required>
                <label for="reg-email">Admin Email</label>
            </div>

            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="reg_password" id="reg-password" placeholder="Password" required minlength="6">
                <label for="reg-password">Password</label>
                <button type="button" class="toggle-pw" data-target="reg-password">
                    <i class="fas fa-eye"></i>
                </button>
            </div>

            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="reg_confirm" id="reg-confirm" placeholder="Confirm Password" required minlength="6">
                <label for="reg-confirm">Confirm Password</label>
                <button type="button" class="toggle-pw" data-target="reg-confirm">
                    <i class="fas fa-eye"></i>
                </button>
            </div>

            <input type="submit" class="button" value="Register" name="RegisterAdmin">
        </form>
    </div>

    <!-- OVERLAY -->
    <div class="overlay">
        <div class="overlay-panel for-login">
            <h2>New Admin?</h2>
            <p>Create an administrator account.</p>
            <button type="button" class="ghost" id="signUpButton">Register</button>
        </div>
        <div class="overlay-panel for-register">
            <h2>Already an Admin?</h2>
            <p>Log in to manage students.</p>
            <button type="button" class="ghost" id="signInButton">Log In</button>
        </div>
    </div>
</div>

<script src="script2.js"></script>
</body>
</html>