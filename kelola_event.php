<?php
include '../koneksi.php';
include '../auth/check_auth.php';
session_start();
require_admin('../auth/login.php');
$result=mysqli_query($conn,"SELECT * FROM events ORDER BY tanggal ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Manage Events — EVENTLOG</title>
<link rel="stylesheet" href="../style/style.css">
</head>
<body>
<nav class="navbar">
  <a href="dashboard.php" class="logo">EVENTLOG</a>
  <div class="nav-links">
    <a href="dashboard.php">Dashboard</a>
    <a href="kelola_event.php" style="border-bottom: 2px solid #fbbf24;">Manage Events</a>
    <a href="add_admin.php" style="color: #fbbf24; font-weight: bold;">Manage Admin</a>
    <a href="../index.php">View Site</a>
    <a href="../auth/logout.php" class="nav-cta">Logout</a>
  </div>
</nav>

<div class="container">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:32px;flex-wrap:wrap;gap:12px">
    <div>
      <div class="sec-label">ADMIN PANEL</div>
      <h2 class="sec-title">Manage Events</h2>
    </div>
    <a href="tambah.php" class="btn-ticket">➕ Add Event</a>
  </div>

  <div class="table-container">
    <table>
      <tr><th>#</th><th>Event Name</th><th>Date</th><th>Location</th><th>Quota</th><th>Actions</th></tr>
      <?php if(mysqli_num_rows($result)==0): ?>
        <tr><td colspan="6" style="text-align:center;color:var(--muted);padding:40px">No events yet.</td></tr>
      <?php else: $no=1; while($row=mysqli_fetch_assoc($result)): ?>
        <tr>
          <td><?=$no++?></td>
          <td style="font-weight:600;color:var(--white)"><?=htmlspecialchars($row['nama_event'])?></td>
          <td><?=date('d M Y',strtotime($row['tanggal']))?></td>
          <td style="color:var(--muted)"><?=htmlspecialchars($row['lokasi'])?></td>
          <td><?=$row['kuota']?> pax</td>
          <td>
            <a href="edit.php?id=<?=$row['id']?>" class="btn btn-warning">✏️ Edit</a>
            <a href="hapus.php?id=<?=$row['id']?>" class="btn btn-danger" onclick="return confirm('Delete this event?')">🗑️ Delete</a>
          </td>
        </tr>
      <?php endwhile; endif; ?>
    </table>
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
