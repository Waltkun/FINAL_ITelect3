<?php
session_start();
include("connect2.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: index2.php");
    exit();
}

$uid = $_SESSION['user_id'];
$firstName = $_SESSION['firstName'];
$lastName = $_SESSION['lastName'];

// Create student_profiles table if it doesn't exist
$conn->query("CREATE TABLE IF NOT EXISTS student_profiles (
    profile_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL UNIQUE,
    student_id VARCHAR(50) NULL,
    middle_name VARCHAR(50)  NULL,
    phone_no    VARCHAR(20)  NULL,
    program     VARCHAR(100) NULL,
    year_level  VARCHAR(20)  NULL,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");

$stmt = $conn->prepare("SELECT * FROM student_profiles WHERE user_id = ?");
$stmt->bind_param("i", $uid);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc() ?: [];

$s_id = $profile['student_id'] ?? '';
$middle = $profile['middle_name'] ?? '';
$phone = $profile['phone_no'] ?? '';
$program = $profile['program'] ?? '';
$year = $profile['year_level'] ?? '';

$error = "";

// SAVE PROFILE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_s_id = trim($_POST['student_id']);
    $fname = trim($_POST['first_name']);
    $lname = trim($_POST['last_name']);
    $mid = trim($_POST['middle_name']);
    $ph = trim($_POST['phone_no']);
    $prog = trim($_POST['program']);
    $yr = trim($_POST['year_level']);

    // Server-side validation for Student ID and Phone Number
    if (!empty($new_s_id) && (!ctype_digit($new_s_id) || strlen($new_s_id) > 9)) {
        $error = "Student ID must be numbers only and maximum 9 digits.";
    } elseif (!empty($ph) && (!ctype_digit($ph) || strlen($ph) > 11)) {
        $error = "Phone number must be numbers only and maximum 11 digits.";
    } else {
        $u = $conn->prepare("UPDATE users SET firstName = ?, lastName = ? WHERE id = ?");
        $u->bind_param("ssi", $fname, $lname, $uid);
        $u->execute();
        $_SESSION['firstName'] = $fname;
        $_SESSION['lastName'] = $lname;

        $conn->query("INSERT IGNORE INTO student_profiles (user_id) VALUES ($uid)");
        
        $p = $conn->prepare("UPDATE student_profiles SET student_id = ?, middle_name = ?, phone_no = ?, program = ?, year_level = ? WHERE user_id = ?");
        $p->bind_param("sssssi", $new_s_id, $mid, $ph, $prog, $yr, $uid);
        $p->execute();

        header("Location: homepage2.php?saved=1");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style2.css">
</head>
<body class="home-page">

    <div class="profile-card" style="max-width: 95%; margin: 20px auto;">
        <header class="pc-header">
            <div class="logo">
                <div class="logo-mark"><img src="gsc_logo.png" alt="GSC Logo"></div>
                <div class="logo-text">Goldenstate College<small>Student Portal</small></div>
            </div>
            <p class="logout"><a href="logout2.php">Logout</a></p>
        </header>

        <div class="pc-body">
            <aside class="pc-side">
                <h1 class="pc-title"><span>Student</span>Profile</h1>
                
                <p class="pc-welcome" style="margin-top: 15px;">Hello, Welcome <?= htmlspecialchars($firstName . ' ' . $lastName) ?> :3</p>
                <div class="avatar"><?= strtoupper(substr($firstName,0,1) . substr($lastName,0,1)) ?></div>
            </aside>

            <section class="pc-form">
                <?php if (isset($_GET['saved'])): ?>
                    <div class="message success">Profile saved successfully.</div>
                <?php endif; ?>
                
                <?php if (!empty($error)): ?>
                    <div class="message error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="post" action="homepage2.php">
                    <div class="grid">
                        
                        <!-- STUDENT ID (EDITABLE) -->
                        <div class="field" style="grid-column: span 2;">
                            <label>Student ID (Max 9 digits)</label>
                            <!-- maxlength=9 prevents typing more than 9. oninput replaces any non-number -->
                            <input type="text" name="student_id" value="<?= htmlspecialchars($s_id) ?>" 
                                   placeholder="e.g. 202400123" maxlength="9" 
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        </div>

                        <div class="field">
                            <label>First Name</label>
                            <input type="text" name="first_name" value="<?= htmlspecialchars($firstName) ?>" required>
                        </div>
                        <div class="field">
                            <label>Last Name</label>
                            <input type="text" name="last_name" value="<?= htmlspecialchars($lastName) ?>" required>
                        </div>
                        <div class="field">
                            <label>Middle Name</label>
                            <input type="text" name="middle_name" value="<?= htmlspecialchars($middle) ?>" placeholder="Optional">
                        </div>
                        
                        <!-- PHONE NUMBER (MAX 11) -->
                        <div class="field">
                            <label>Phone Number (Max 11 digits)</label>
                            <input type="tel" name="phone_no" value="<?= htmlspecialchars($phone) ?>" 
                                   placeholder="e.g. 09171234567" maxlength="11" 
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        </div>

                        <div class="field">
                            <label>Course / Program</label>
                            <input type="text" name="program" value="<?= htmlspecialchars($program) ?>" placeholder="e.g. BSIT">
                        </div>
                        <div class="field">
                            <label>Year Level</label>
                            <select name="year_level">
                                <option value="">Select year</option>
                                <?php foreach(["1st Year", "2nd Year", "3rd Year", "4th Year"] as $y): ?>
                                    <option value="<?= $y ?>" <?= $year === $y ? 'selected' : '' ?>><?= $y ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="save-btn">Save Profile</button>
                    </div>
                </form>
            </section>
        </div>
    </div>
</body>
</html>