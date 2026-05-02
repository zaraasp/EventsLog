<?php
include '../koneksi.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

if (!isset($_GET['reg_id']) || empty($_GET['reg_id'])) {
    header("Location: ../registrasi_saya.php?msg=invalid");
    exit();
}

$reg_id  = (int)$_GET['reg_id'];
$user_id = (int)$_SESSION['user_id'];

// Verify that this registration belongs to the logged-in user
$cek = mysqli_prepare($conn, "SELECT r.id, e.nama_event FROM registrations r JOIN events e ON r.event_id = e.id WHERE r.id = ? AND r.user_id = ?");
mysqli_stmt_bind_param($cek, "ii", $reg_id, $user_id);
mysqli_stmt_execute($cek);
$result = mysqli_stmt_get_result($cek);
mysqli_stmt_close($cek);

if (mysqli_num_rows($result) == 0) {
    header("Location: ../registrasi_saya.php?msg=not_found");
    exit();
}

$reg = mysqli_fetch_assoc($result);

// Delete the registration
$del = mysqli_prepare($conn, "DELETE FROM registrations WHERE id = ? AND user_id = ?");
mysqli_stmt_bind_param($del, "ii", $reg_id, $user_id);
$ok = mysqli_stmt_execute($del);
mysqli_stmt_close($del);

if ($ok) {
    $_SESSION['cancel_success'] = $reg['nama_event'];
    header("Location: ../registrasi_saya.php?cancelled=1");
} else {
    header("Location: ../registrasi_saya.php?msg=cancel_failed");
}
exit();
?>
