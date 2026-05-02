<?php
include '../koneksi.php';
include '../auth/check_auth.php';
session_start();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('Event not found!'); window.location='kelola_event.php';</script>"; exit();
}
$id = (int)$_GET['id'];
require_admin_or_owner($conn, $id, '../auth/login.php');

$stmt = mysqli_prepare($conn, "SELECT * FROM events WHERE id=?");
mysqli_stmt_bind_param($stmt,"i",$id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);

if (mysqli_num_rows($result)==0) {
    echo "<script>alert('Event not found!'); window.location='kelola_event.php';</script>"; exit();
}
$event=$mysqli_fetch_assoc($result);
$event=mysqli_fetch_assoc($result);
$error="";$success="";

if ($_SERVER['REQUEST_METHOD']=='POST') {
    $nama_event=trim($_POST['nama_event']??'');
    $deskripsi=trim($_POST['deskripsi']??'');
    $tanggal=$_POST['tanggal']??'';
    $lokasi=trim($_POST['lokasi']??'');
    $kuota=(int)($_POST['kuota']??0);
    if(empty($nama_event)||empty($tanggal)||empty($lokasi)||$kuota<=0){
        $error="Event name, date, location, and quota are required (quota > 0).";
    } else {
        $u=mysqli_prepare($conn,"UPDATE events SET nama_event=?,deskripsi=?,tanggal=?,lokasi=?,kuota=? WHERE id=?");
        mysqli_stmt_bind_param($u,"ssssii",$nama_event,$deskripsi,$tanggal,$lokasi,$kuota,$id);
        if(mysqli_stmt_execute($u)){
            $success="Event updated successfully!";
            $r2=mysqli_query($conn,"SELECT * FROM events WHERE id=$id");
            $event=mysqli_fetch_assoc($r2);
        } else { $error="Failed to update event."; }
        mysqli_stmt_close($u);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Event — EVENTLOG</title>
<link rel="stylesheet" href="../style/style.css">
</head>
<body>
<nav class="navbar">
  <a href="dashboard.php" class="logo">EVENTLOG</a>
  <div class="nav-links">
    <a href="dashboard.php">Dashboard</a>
    <a href="kelola_event.php">Manage Events</a>
    <a href="../auth/logout.php" class="nav-cta">Logout</a>
  </div>
</nav>
<div class="container">
  <h2 class="sec-title" style="margin-bottom:28px">✏️ Edit Event</h2>
  <?php if($error): ?><div class="alert alert-error"><?=$error?></div><?php endif; ?>
  <?php if($success): ?><div class="alert alert-success"><?=$success?> <a href="kelola_event.php" style="color:#86efac">← Back to list</a></div><?php endif; ?>
  <div class="table-container" style="max-width:600px">
    <form method="POST">
      <div class="form-group"><label>Event Name</label><input type="text" name="nama_event" value="<?=htmlspecialchars($event['nama_event'])?>"></div>
      <div class="form-group"><label>Description</label><textarea name="deskripsi" rows="4"><?=htmlspecialchars($event['deskripsi'])?></textarea></div>
      <div class="form-group"><label>Date</label><input type="date" name="tanggal" value="<?=$event['tanggal']?>"></div>
      <div class="form-group"><label>Location</label><input type="text" name="lokasi" value="<?=htmlspecialchars($event['lokasi'])?>"></div>
      <div class="form-group"><label>Quota</label><input type="number" name="kuota" value="<?=$event['kuota']?>" min="1"></div>
      <div style="display:flex;gap:12px;margin-top:8px">
        <button type="submit" class="btn btn-success" style="padding:10px 24px">💾 Save Changes</button>
        <a href="kelola_event.php" class="btn btn-danger" style="padding:10px 24px">Cancel</a>
      </div>
    </form>
  </div>
</div>
<footer class="footer"><div class="footer-inner"><div class="footer-bottom" style="border-top:none;padding-top:0"><span>&copy; <?=date('Y')?> EVENTLOG Admin Panel</span></div></div></footer>
</body>
</html>
