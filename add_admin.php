<?php
include '../koneksi.php';
include '../auth/check_auth.php';
session_start();
require_admin('../auth/login.php');

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($nama) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        // Check if email already exists
        $cek = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($cek, "s", $email);
        mysqli_stmt_execute($cek);
        mysqli_stmt_store_result($cek);

        if (mysqli_stmt_num_rows($cek) > 0) {
            $error = "This email is already registered to a user or admin.";
        } else {
            // Hash and set role to admin
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = 'admin';

            $ins = mysqli_prepare($conn, "INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($ins, "ssss", $nama, $email, $hashed_password, $role);

            if (mysqli_stmt_execute($ins)) {
                $success = "New administrator account created successfully!";
            } else {
                $error = "Failed to create admin account. System error.";
            }
            mysqli_stmt_close($ins);
        }
        mysqli_stmt_close($cek);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Add Admin — EVENTLOG</title>
    <link rel="stylesheet" href="../style/style.css">
</head>
<body>
<nav class="navbar">
    <a href="dashboard.php" class="logo">EVENTLOG</a>
    <div class="nav-links">
        <a href="dashboard.php">Dashboard</a>
        <a href="kelola_event.php">Manage Events</a>
        <a href="add_admin.php" style="border-bottom: 2px solid #fbbf24;">Manage Admin</a>
        <a href="../index.php">View Site</a>
        <a href="../auth/logout.php" class="nav-cta">Logout</a>
    </div>
</nav>

<div class="container" style="max-width: 600px; margin-top: 50px;">
    <div class="sec-label">PRIVILEGED ACCESS</div>
    <h2 class="sec-title">Create New Admin</h2>
    <p style="color:var(--muted);margin-bottom:24px">Warning: New accounts created here will have full administrative access to the platform.</p>

    <?php if ($error): ?><div class="alert alert-error" style="background:#fef2f2; color:#b91c1c; padding:15px; border-radius:8px; margin-bottom:20px; border:1px solid #fee2e2;"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success" style="background:#f0fdf4; color:#15803d; padding:15px; border-radius:8px; margin-bottom:20px; border:1px solid #dcfce7;"><?= htmlspecialchars($success) ?></div><?php endif; ?>

    <form method="POST" style="background: #111; padding: 30px; border-radius: 12px; border: 1px solid #222;">
        <div style="margin-bottom: 15px;">
            <label style="display:block; margin-bottom: 8px; color: #888;">Full Name</label>
            <input type="text" name="nama" style="width:100%; padding:12px; background:#1a1a1a; border:1px solid #333; color:white; border-radius:6px;" required>
        </div>
        
        <div style="margin-bottom: 15px;">
            <label style="display:block; margin-bottom: 8px; color: #888;">Admin Email</label>
            <input type="email" name="email" style="width:100%; padding:12px; background:#1a1a1a; border:1px solid #333; color:white; border-radius:6px;" required>
        </div>
        
        <div style="margin-bottom: 25px;">
            <label style="display:block; margin-bottom: 8px; color: #888;">Set Password</label>
            <input type="password" name="password" style="width:100%; padding:12px; background:#1a1a1a; border:1px solid #333; color:white; border-radius:6px;" required>
        </div>
        
        <button type="submit" class="btn" style="width:100%; padding:14px; background:#fbbf24; color:black; border:none; font-weight:bold; border-radius:6px; cursor:pointer;">
            Create Admin Account
        </button>
        <a href="dashboard.php" style="display:block; text-align:center; margin-top:20px; color:#666; text-decoration:none;">Cancel and return</a>
    </form>
</div>
</body>
</html>