<?php
include '../koneksi.php';
session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

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
        // Check for existing email
        $cek = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($cek, "s", $email);
        mysqli_stmt_execute($cek);
        mysqli_stmt_store_result($cek);

        if (mysqli_stmt_num_rows($cek) > 0) {
            $error = "This email is already registered.";
        } else {
            // HASH THE PASSWORD
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // DEFAULT ROLE IS ALWAYS USER
            $role = 'user';

            $ins = mysqli_prepare($conn, "INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($ins, "ssss", $nama, $email, $hashed_password, $role);

            if (mysqli_stmt_execute($ins)) {
                $success = "Account created successfully! You can now sign in.";
            } else {
                $error = "Registration failed. Please try again.";
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
    <title>Create Account — EVENTLOG</title>
    <link rel="stylesheet" href="../style/style.css">
</head>
<body class="auth-page">
<div class="form-container">
    <div class="form-brand"><a href="../index.php">EVENTLOG</a></div>
    <h2>Create Account</h2>
    <p>Free to join. Start discovering amazing events!</p>
    
    <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?> <a href="login.php" style="color:#86efac">Sign In →</a></div><?php endif; ?>
    
    <form method="POST">
        <label>Full Name</label>
        <input type="text" name="nama" placeholder="Your name" value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>" required>
        
        <label>Email</label>
        <input type="email" name="email" placeholder="you@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
        
        <label>Password</label>
        <input type="password" name="password" placeholder="Min. 6 characters" required>
        
        <button type="submit">Create Account →</button>
    </form>
    <div class="link-alt">Already have an account? <a href="login.php">Sign in here</a></div>
</div>
</body>
</html>