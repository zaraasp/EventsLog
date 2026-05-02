<?php
include '../koneksi.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

if (!isset($_GET['event_id']) || empty($_GET['event_id'])) {
    header("Location: ../index.php?msg=event_not_found");
    exit();
}

$event_id = (int)$_GET['event_id'];
$user_id  = (int)$_SESSION['user_id'];

// Get event details
$cek_stmt = mysqli_prepare($conn, "SELECT e.*, u.email as user_email, u.nama as user_nama FROM events e LEFT JOIN users u ON u.id=? WHERE e.id=?");
mysqli_stmt_bind_param($cek_stmt, "ii", $user_id, $event_id);
mysqli_stmt_execute($cek_stmt);
$cek_event = mysqli_stmt_get_result($cek_stmt);
mysqli_stmt_close($cek_stmt);

if (mysqli_num_rows($cek_event) == 0) {
    header("Location: ../index.php?msg=event_not_found");
    exit();
}

$event = mysqli_fetch_assoc($cek_event);

// Check already registered
$cek_reg = mysqli_prepare($conn, "SELECT id FROM registrations WHERE user_id=? AND event_id=?");
mysqli_stmt_bind_param($cek_reg, "ii", $user_id, $event_id);
mysqli_stmt_execute($cek_reg);
mysqli_stmt_store_result($cek_reg);
$already = mysqli_stmt_num_rows($cek_reg) > 0;
mysqli_stmt_close($cek_reg);

if ($already) {
    header("Location: ../index.php?msg=already_registered");
    exit();
}

$status = ($event['jenis_harga'] === 'berbayar') ? 'pending' : 'approved';
$insert_stmt = mysqli_prepare($conn, "INSERT INTO registrations (user_id, event_id, status) VALUES (?,?,?)");
mysqli_stmt_bind_param($insert_stmt, "iis", $user_id, $event_id, $status);
$ok = mysqli_stmt_execute($insert_stmt);
mysqli_stmt_close($insert_stmt);

// Pass data to success page via session
if ($ok) {
    $_SESSION['reg_success'] = [
        'event_name'  => $event['nama_event'],
        'event_date'  => $event['tanggal'],
        'event_loc'   => $event['lokasi'],
        'jenis_harga' => $event['jenis_harga'],
        'harga'       => $event['harga'],
        'user_nama'   => $event['user_nama'],
        'user_email'  => $event['user_email'],
        'status'      => $status,
    ];
    header("Location: ../registrasi_saya.php?registered=1");
} else {
    header("Location: ../index.php?msg=failed");
}
exit();
?>
