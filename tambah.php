<?php
include '../koneksi.php';
include '../auth/check_auth.php';
session_start();
require_admin('../auth/login.php');

$error="";$success="";
if($_SERVER['REQUEST_METHOD']=='POST'){
  $nama_event=trim($_POST['nama_event']??'');
  $deskripsi=trim($_POST['deskripsi']??'');
  $tanggal=$_POST['tanggal']??'';
  $lokasi=trim($_POST['lokasi']??'');
  $kuota=(int)($_POST['kuota']??0);
  $harga=(int)($_POST['harga']??0);
  $jenis_harga=$_POST['jenis_harga']??'gratis';
  $user_id=(int)$_SESSION['user_id'];

  if(empty($nama_event)||empty($tanggal)||empty($lokasi)||$kuota<=0){
    $error="Event name, date, location, and quota are required.";
  } else {
    $s=mysqli_prepare($conn,"INSERT INTO events(nama_event,deskripsi,tanggal,lokasi,kuota,harga,jenis_harga,user_id) VALUES(?,?,?,?,?,?,?,?)");
    mysqli_stmt_bind_param($s,"ssssiisi",$nama_event,$deskripsi,$tanggal,$lokasi,$kuota,$harga,$jenis_harga,$user_id);
    $success=mysqli_stmt_execute($s)?"Event added successfully!":"Failed to save event.";
    mysqli_stmt_close($s);
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Add Event — EVENTLOG</title>
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
  <div class="sec-label">ADMIN PANEL</div>
  <h2 class="sec-title" style="margin-bottom:32px">Add New Event</h2>

  <?php if($error): ?><div class="alert alert-error"><?=$error?></div><?php endif; ?>
  <?php if($success): ?><div class="alert alert-success"><?=$success?> <a href="kelola_event.php" style="color:#86efac">← Back to events</a></div><?php endif; ?>

  <div class="table-container" style="max-width:640px">
    <form method="POST">
      <div class="form-group"><label>Event Name</label><input type="text" name="nama_event" placeholder="e.g. Laravel Workshop"></div>
      <div class="form-group"><label>Description</label><textarea name="deskripsi" rows="4" placeholder="Brief description of the event..."></textarea></div>
      <div class="form-group"><label>Date</label><input type="date" name="tanggal"></div>
      <div class="form-group"><label>Location</label><input type="text" name="lokasi" placeholder="e.g. Main Hall, Building A"></div>
      <div class="form-group"><label>Quota (attendees)</label><input type="number" name="kuota" placeholder="e.g. 50" min="1"></div>
      <div class="form-group">
        <label>Pricing Type</label>
        <select name="jenis_harga">
          <option value="gratis">Free</option>
          <option value="berbayar">Paid</option>
        </select>
      </div>
      <div class="form-group"><label>Price (set 0 if free)</label><input type="number" name="harga" placeholder="e.g. 150000" min="0" value="0"></div>
      <div style="display:flex;gap:12px;margin-top:8px">
        <button type="submit" class="btn-ticket">💾 Save Event</button>
        <a href="kelola_event.php" class="btn btn-danger" style="padding:11px 22px">Cancel</a>
      </div>
    </form>
  </div>
</div>

<footer class="footer">
  <div class="footer-inner">
    <div class="footer-bottom" style="border-top:none;padding-top:0"><span>&copy; <?=date('Y')?> EVENTLOG Admin Panel</span></div>
  </div>
</footer>
</body>
</html>
