<?php
session_start();
include("connect2.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: index2.php");
    exit();
}

$uid = $_SESSION['user_id'];

// Fetch User Data (Includes Gender and Birthday now)
$stmt = $conn->prepare("SELECT * FROM users WHERE Id = ?");
$stmt->bind_param("i", $uid);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$firstName = $user['firstName'];
$lastName = $user['lastName'];
$gender = $user['gender'] ?? '';
$birthday = $user['birthday'] ?? '';

// Fetch Profile Data
$stmt2 = $conn->prepare("SELECT * FROM student_profiles WHERE user_id = ?");
$stmt2->bind_param("i", $uid);
$stmt2->execute();
$profile = $stmt2->get_result()->fetch_assoc() ?: [];

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
    $gndr = $_POST['gender'];
    $bday = $_POST['birthday'];
    $mid = trim($_POST['middle_name']);
    $ph = trim($_POST['phone_no']);
    $prog = trim($_POST['program']);
    $yr = trim($_POST['year_level']);

    if (!empty($new_s_id) && (!ctype_digit($new_s_id) || strlen($new_s_id) > 9)) {
        $error = "Student ID must be numbers only and maximum 9 digits.";
    } elseif (!empty($ph) && (!ctype_digit($ph) || strlen($ph) > 11)) {
        $error = "Phone number must be numbers only and maximum 11 digits.";
    } else {
        // Update users table
        $u = $conn->prepare("UPDATE users SET firstName = ?, lastName = ?, gender = ?, birthday = ? WHERE Id = ?");
        $u->bind_param("ssssi", $fname, $lname, $gndr, $bday, $uid);
        $u->execute();
        $_SESSION['firstName'] = $fname;
        $_SESSION['lastName'] = $lname;

        // Update profiles table
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
                        
                        <!-- STUDENT ID -->
                        <div class="field" style="grid-column: span 2;">
                            <label>Student ID (Max 9 digits)</label>
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

                        <!-- NEW: GENDER AND BIRTHDAY -->
                        <div class="field">
                            <label>Gender</label>
                            <select name="gender" required>
                                <option value="" disabled <?= empty($gender) ? 'selected' : '' ?>>Select Gender</option>
                                <option value="Male" <?= $gender === 'Male' ? 'selected' : '' ?>>Male</option>
                                <option value="Female" <?= $gender === 'Female' ? 'selected' : '' ?>>Female</option>
                                <option value="Other" <?= $gender === 'Other' ? 'selected' : '' ?>>Other</option>
                            </select>
                        </div>
                        <div class="field">
                            <label>Birthday (mm/dd/yy)</label>
                            <input type="text" name="birthday" value="<?= htmlspecialchars($birthday) ?>" 
                                placeholder="mm/dd/yy" maxlength="10" required
                                oninput="let v=this.value.replace(/\D/g,''); if(v.length>2)v=v.slice(0,2)+'/'+v.slice(2); if(v.length>5)v=v.slice(0,5)+'/'+v.slice(5); this.value=v;">
                        </div>
                        
                        <!-- PHONE NUMBER -->
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
