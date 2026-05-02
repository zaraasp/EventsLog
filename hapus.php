<?php
include '../koneksi.php';
include '../auth/check_auth.php';
session_start();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('Event not found!'); window.location='kelola_event.php';</script>"; exit();
}
$id=(int)$_GET['id'];
require_admin_or_owner($conn,$id,'../auth/login.php');

$s=mysqli_prepare($conn,"DELETE FROM events WHERE id=?");
mysqli_stmt_bind_param($s,"i",$id);
if(mysqli_stmt_execute($s)){
    echo "<script>alert('Event deleted successfully.'); window.location='kelola_event.php';</script>";
} else {
    echo "<script>alert('Failed to delete event.'); window.location='kelola_event.php';</script>";
}
mysqli_stmt_close($s);
?>
