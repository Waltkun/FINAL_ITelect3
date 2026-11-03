<?php
include 'connect2.php';
session_start();

if (!isset($_SESSION['admin_email'])) {
    header("Location: admin_login.php");
    exit();
}

$msg = "";

// ADD STUDENT
if (isset($_POST['addStudent'])) {
    $s_id = trim($_POST['student_id']);
    $fname = trim($_POST['firstName']);
    $lname = trim($_POST['lastName']);
    $email = trim($_POST['email']);
    $ph = trim($_POST['phone_no']);
    $prog = trim($_POST['program']);
    $yr = trim($_POST['year_level']);
    
    if (!ctype_digit($s_id) || strlen($s_id) > 9) {
        $msg = "Error: Student ID must be numbers only and maximum 9 digits.";
    } elseif (!empty($ph) && (!ctype_digit($ph) || strlen($ph) > 11)) {
        $msg = "Error: Phone number must be numbers only and maximum 11 digits.";
    } else {
        $default_pass = password_hash('Student@123', PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (firstName, lastName, email, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $fname, $lname, $email, $default_pass);
        
        if($stmt->execute()){
            $new_user_id = $conn->insert_id;
            
            // Insert into profiles with the new fields
            $profile_stmt = $conn->prepare("INSERT INTO student_profiles (user_id, student_id, phone_no, program, year_level) VALUES (?, ?, ?, ?, ?)");
            $profile_stmt->bind_param("issss", $new_user_id, $s_id, $ph, $prog, $yr);
            $profile_stmt->execute();

            $msg = "Student added successfully! Default password: Student@123";
        } else {
            $msg = "Error adding student. Email might already exist.";
        }
    }
}

// DELETE STUDENT
if (isset($_POST['deleteStudent'])) {
    $uid = (int)$_POST['user_id'];
    $conn->query("DELETE FROM users WHERE Id = $uid");
    $msg = "Student deleted.";
}

// FETCH ALL STUDENTS
$students = $conn->query("
    SELECT u.Id, u.firstName, u.lastName, u.email, sp.student_id, sp.phone_no, sp.program, sp.year_level 
    FROM users u 
    LEFT JOIN student_profiles sp ON u.Id = sp.user_id 
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style2.css">
</head>
<body class="home-page">
    <div class="profile-card" style="margin-top: 20px; max-width: 95%;">
        <header class="pc-header">
            <div class="logo">
                <div class="logo-mark"><img src="gsc_logo.png" alt="GSC Logo"></div>
                <div class="logo-text">Admin Dashboard<small>Manage Students</small></div>
            </div>
            <p class="logout"><a href="logout2.php">Logout</a></p>
        </header>

        <?php if(!empty($msg)): ?>
            <div class="message <?= strpos($msg, 'Error') !== false ? 'error' : 'success' ?>" style="margin-top:20px;"><?= $msg ?></div>
        <?php endif; ?>

        <div style="padding: 20px 0;">
            <h2 style="color:var(--gold); margin-bottom:15px;">Add New Student</h2>
            <form method="post" class="grid" style="grid-template-columns: 1fr 1fr 1fr; margin:0;">
                
                <!-- STUDENT ID -->
                <div class="field">
                    <input type="text" name="student_id" placeholder="Student ID (Max 9 digits)" 
                        required maxlength="9" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                </div>
                
                <div class="field"><input type="text" name="firstName" placeholder="First Name" required></div>
                <div class="field"><input type="text" name="lastName" placeholder="Last Name" required></div>
                
                <div class="field" style="grid-column: span 2;"><input type="email" name="email" placeholder="Email Address" required></div>
                <div class="field"><input type="tel" name="phone_no" placeholder="Phone (Max 11 digits)" maxlength="11" oninput="this.value = this.value.replace(/[^0-9]/g, '')"></div>
                
                <div class="field"><input type="text" name="program" placeholder="Course / Program"></div>
                <div class="field">
                    <select name="year_level">
                        <option value="">Select Year Level</option>
                        <option value="1st Year">1st Year</option>
                        <option value="2nd Year">2nd Year</option>
                        <option value="3rd Year">3rd Year</option>
                        <option value="4th Year">4th Year</option>
                    </select>
                </div>

                <div style="grid-column: span 3; text-align: right;">
                    <button type="submit" name="addStudent" class="save-btn">Add Student</button>
                </div>
            </form>
        </div>

        <div style="margin-top: 30px;">
            <h2 style="color:var(--gold); margin-bottom:15px;">Registered Students</h2>
            <div style="overflow-x: auto;">
                <table style="width:100%; border-collapse: collapse; text-align: left; color: var(--text); white-space: nowrap;">
                    <tr style="border-bottom: 1px solid #333;">
                        <th style="padding:10px;">Student ID</th>
                        <th style="padding:10px;">Name</th>
                        <th style="padding:10px;">Email</th>
                        <th style="padding:10px;">Phone</th>
                        <th style="padding:10px;">Course/Year</th>
                        <th style="padding:10px;">Action</th>
                    </tr>
                    <?php while($row = $students->fetch_assoc()): ?>
                    <tr style="border-bottom: 1px solid #222;">
                        <td style="padding:10px;"><?= htmlspecialchars($row['student_id'] ?? 'N/A') ?></td>
                        <td style="padding:10px;"><?= htmlspecialchars($row['firstName'] . ' ' . $row['lastName']) ?></td>
                        <td style="padding:10px;"><?= htmlspecialchars($row['email']) ?></td>
                        <td style="padding:10px;"><?= htmlspecialchars($row['phone_no'] ?? 'N/A') ?></td>
                        <td style="padding:10px;">
                            <?= htmlspecialchars($row['program'] ?? 'N/A') ?> 
                            <?= !empty($row['year_level']) ? '- ' . htmlspecialchars($row['year_level']) : '' ?>
                        </td>
                        <td style="padding:10px;">
                            <form method="post" onsubmit="return confirm('Delete this student?');">
                                <input type="hidden" name="user_id" value="<?= $row['Id'] ?>">
                                <button type="submit" name="deleteStudent" style="color:#ff6b6b; background:none; border:none; cursor:pointer;">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
