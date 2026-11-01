<?php
session_start();
if (isset($_SESSION['email'])) {
    header("Location: homepage2.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal - Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style2.css">
</head>
<body>

<div class="wrapper" id="wrapper">

    <!-- LOG IN -->
    <div class="form-box login" id="signIn">
        <h1 class="form-title">Student Log In</h1>
        
        <!-- ERROR MESSAGE CONTAINER -->
        <div id="loginError" class="message error" style="display: none;"></div>

        <form method="post" action="register.php" id="loginForm">
            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" id="log-email" placeholder="Email" required>
                <label for="log-email">Email</label>
            </div>

            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" id="log-password" placeholder="Password" required>
                <label for="log-password">Password</label>
                <button type="button" class="toggle-pw" data-target="log-password">
                    <i class="fas fa-eye"></i>
                </button>
            </div>

            <input type="submit" class="button" value="Sign In" name="SignIn">
        </form>
        
        <div style="text-align: center; margin-top: 15px;">
            <a href="admin_login.php" style="color: var(--muted); font-size: 0.9rem;">Login as Administrator</a>
        </div>
    </div>

    <!-- REGISTER -->
    <div class="form-box register" id="signUp" inert>
        <h1 class="form-title">Student Register</h1>
        
        <!-- ERROR MESSAGE CONTAINER -->
        <div id="registerError" class="message error" style="display: none;"></div>

        <form method="post" action="register.php" id="registerForm">
            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" name="fName" id="fName" placeholder="First Name" required>
                <label for="fName">First Name</label>
            </div>

            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" name="lName" id="lName" placeholder="Last Name" required>
                <label for="lName">Last Name</label>
            </div>

            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" id="reg-email" placeholder="Email" required>
                <label for="reg-email">Email</label>
            </div>

            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" id="reg-password" placeholder="Password" required minlength="6">
                <label for="reg-password">Password</label>
                <button type="button" class="toggle-pw" data-target="reg-password">
                    <i class="fas fa-eye"></i>
                </button>
            </div>

            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="confirm_password" id="reg-confirm" placeholder="Confirm Password" required minlength="6">
                <label for="reg-confirm">Confirm Password</label>
                <button type="button" class="toggle-pw" data-target="reg-confirm">
                    <i class="fas fa-eye"></i>
                </button>
            </div>

            <input type="submit" class="button" value="Sign Up" name="SignUp">
        </form>
    </div>

    <!-- OVERLAY -->
    <div class="overlay">
        <div class="overlay-panel for-login">
            <h2>New here?</h2>
            <p>Create an account and get started.</p>
            <button type="button" class="ghost" id="signUpButton">Sign Up</button>
        </div>
        <div class="overlay-panel for-register">
            <h2>Welcome back!</h2>
            <p>Already have an account? Log in instead.</p>
            <button type="button" class="ghost" id="signInButton">Sign In</button>
        </div>
    </div>

</div>

<script src="script2.js"></script>
</body>
</html>