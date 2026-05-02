<?php
include '../koneksi.php';
session_start();

// Redirect if already logged in
if(isset($_SESSION['user_id'])){
    header("Location: ../index.php");
    exit;
}

$error = "";

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if(empty($email) || empty($password)){
        $error = "Email and password are required.";
    } else {
        // Use prepared statements to fetch the user by email
        $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        // SECURE CHECK: Use password_verify instead of ===
        if($row && password_verify($password, $row['password'])){
            // Login Success
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['nama'] = $row['nama'];
            $_SESSION['role'] = $row['role'];

            // Redirect based on role
            if($row['role'] == 'admin') {
                header("Location: ../events/dashboard.php");
            } else {
                header("Location: ../index.php");
            }
            exit;
        } else {
            // Login Failure
            $error = "Incorrect email or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Sign In — EVENTLOG</title>
    <link rel="stylesheet" href="../style/style.css">
</head>
<body class="auth-page">
<div class="form-container">
    <div class="form-brand"><a href="../index.php">EVENTLOG</a></div>
    <h2>Welcome Back</h2>
    <p>Sign in to register for your favorite events</p>
    
    <?php if($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <label>Email</label>
        <input type="email" name="email" placeholder="you@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
        
        <label>Password</label>
        <input type="password" name="password" placeholder="••••••••" required>
        
        <button type="submit">Sign In →</button>
    </form>
    <div class="link-alt">Don't have an account? <a href="register.php">Sign up here</a></div>
</div>
</body>
</html>