<?php
include '../koneksi.php';
include '../auth/check_auth.php';
session_start();
require_admin('../auth/login.php');

$total_event         = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM events"))[0];
$total_user          = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM users"))[0];
$total_registrations = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM registrations"))[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin Dashboard — EVENTLOG</title>
<link rel="stylesheet" href="../style/style.css">
</head>
<body>
<nav class="navbar">
  <a href="dashboard.php" class="logo">EVENTLOG</a>
  <div class="nav-links">
    <a href="dashboard.php" style="border-bottom: 2px solid #fbbf24;">Dashboard</a>
    <a href="kelola_event.php">Manage Events</a>
    <a href="add_admin.php" style="color: #fbbf24; font-weight: bold;">Manage Admin</a> 
    <a href="../index.php">View Site</a>
    <a href="../auth/logout.php" class="nav-cta">Logout</a>
  </div>
</nav>

<div class="container">
  <div class="sec-label">ADMIN PANEL</div>
  <h2 class="sec-title" style="margin-bottom:8px">Dashboard</h2>
  <p style="color:var(--muted);margin-bottom:36px">Welcome back, <?=htmlspecialchars($_SESSION['nama']??'Admin')?>! Here's a summary of today's data.</p>

  <div class="stats-grid">
    <div class="stat-card">
      <div class="angka"><?=$total_event?></div>
      <div class="label">Total Events</div>
    </div>
    <div class="stat-card">
      <div class="angka"><?=$total_user?></div>
      <div class="label">Total Users</div>
    </div>
    <div class="stat-card">
      <div class="angka" style="background:linear-gradient(135deg,#22c55e,#16a34a);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text"><?=$total_registrations?></div>
      <div class="label">Total Registrations</div>
    </div>
  </div>

  <div style="display:flex;gap:12px;flex-wrap:wrap">
    <a href="kelola_event.php" class="btn btn-primary" style="padding:12px 24px">📋 Manage Events</a>
    <a href="add_admin.php" class="btn btn-warning" style="padding:12px 24px; background: #fbbf24; color: #000;">🛡️ Add New Admin</a>
    <a href="tambah.php" class="btn btn-success" style="padding:12px 24px">➕ Add New Event</a>
  </div>
</div>

<footer class="footer">
  <div class="footer-inner">
    <div class="footer-bottom" style="border-top:none;padding-top:0">
      <span>&copy; <?=date('Y')?> EVENTLOG Admin Panel</span>
    </div>
  </div>
</footer>
</body>
</html>