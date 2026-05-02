<?php
include 'koneksi.php';
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: auth/login.php"); exit; }

$uid = (int)$_SESSION['user_id'];
$result = mysqli_query($conn,
    "SELECT r.*, e.nama_event, e.tanggal, e.lokasi, e.jenis_harga, e.harga
     FROM registrations r
     JOIN events e ON r.event_id = e.id
     WHERE r.user_id = $uid
     ORDER BY r.tgl_daftar DESC"
);

// Pop success data from session
$reg_success = null;
if (isset($_GET['registered']) && isset($_SESSION['reg_success'])) {
    $reg_success = $_SESSION['reg_success'];
    unset($_SESSION['reg_success']);
}

// Pop cancel success from session
$cancel_success = null;
if (isset($_GET['cancelled']) && isset($_SESSION['cancel_success'])) {
    $cancel_success = $_SESSION['cancel_success'];
    unset($_SESSION['cancel_success']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>My Registrations — EVENTLOG</title>
<link rel="stylesheet" href="style/style.css">
<style>
/* ── SUCCESS MODAL ── */
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.8);backdrop-filter:blur(8px);z-index:9999;align-items:center;justify-content:center;padding:20px}
.modal-overlay.active{display:flex}
.modal-box{background:linear-gradient(135deg,#141425,#1a1a2e);border:1px solid rgba(255,255,255,.1);border-radius:24px;padding:44px 40px;max-width:520px;width:100%;position:relative;box-shadow:0 32px 80px rgba(0,0,0,.7);animation:modalIn .35s cubic-bezier(.175,.885,.32,1.275)}
@keyframes modalIn{from{opacity:0;transform:scale(.9) translateY(24px)}to{opacity:1;transform:scale(1) translateY(0)}}

/* success variant */
.modal-box.success-modal{border-color:rgba(34,197,94,.3)}

.modal-confetti{text-align:center;font-size:52px;margin-bottom:4px;animation:bounce .6s ease infinite alternate}
@keyframes bounce{from{transform:scale(1)}to{transform:scale(1.15)}}

.modal-box h3{font-family:var(--font-display);font-size:28px;letter-spacing:2px;color:var(--white);text-align:center;margin-bottom:4px}
.modal-tag{display:inline-flex;align-items:center;gap:6px;padding:4px 14px;border-radius:50px;font-size:11px;font-weight:700;letter-spacing:.8px;margin:0 auto 24px;display:flex;justify-content:center}
.modal-tag.free{background:rgba(34,197,94,.15);color:#4ade80;border:1px solid rgba(34,197,94,.3)}
.modal-tag.paid{background:rgba(248,113,113,.1);color:#fca5a5;border:1px solid rgba(248,113,113,.25)}

.event-card-modal{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:14px;padding:20px 22px;margin-bottom:22px}
.event-card-modal .ev-name{font-size:16px;font-weight:700;color:var(--white);margin-bottom:14px;line-height:1.3}
.ev-detail-row{display:flex;align-items:flex-start;gap:10px;margin-bottom:10px;font-size:13px;color:var(--muted)}
.ev-detail-row:last-child{margin-bottom:0}
.ev-detail-row .icon{font-size:15px;flex-shrink:0;margin-top:1px}
.ev-detail-row .val{color:var(--text)}

.email-notice{background:linear-gradient(135deg,rgba(124,58,237,.12),rgba(224,64,170,.08));border:1px solid rgba(124,58,237,.25);border-radius:12px;padding:16px 18px;margin-bottom:24px;display:flex;gap:12px;align-items:flex-start}
.email-notice .icon{font-size:20px;flex-shrink:0}
.email-notice p{font-size:13px;color:var(--text-light);line-height:1.65;margin:0}
.email-notice strong{color:var(--white)}

.payment-notice{background:rgba(251,191,36,.07);border:1px solid rgba(251,191,36,.2);border-radius:12px;padding:16px 18px;margin-bottom:24px;display:flex;gap:12px;align-items:flex-start}
.payment-notice .icon{font-size:20px;flex-shrink:0}
.payment-notice p{font-size:13px;color:#fcd34d;line-height:1.65;margin:0}

.modal-btn-row{display:flex;gap:10px}
.btn-modal-primary{flex:1;padding:13px;background:var(--grad);color:var(--white);border:none;border-radius:50px;font-size:14px;font-weight:700;cursor:pointer;font-family:var(--font-body);text-decoration:none;display:flex;align-items:center;justify-content:center;gap:6px;transition:opacity .2s,transform .2s}
.btn-modal-primary:hover{opacity:.9;transform:scale(1.03)}
.btn-modal-ghost{padding:13px 20px;background:rgba(255,255,255,.05);color:var(--muted);border:1px solid var(--border2);border-radius:50px;font-size:14px;font-weight:600;cursor:pointer;font-family:var(--font-body);text-decoration:none;display:flex;align-items:center;justify-content:center;transition:all .2s}
.btn-modal-ghost:hover{color:var(--white);border-color:rgba(255,255,255,.3)}

/* ── CANCEL CONFIRM MODAL ── */
.modal-box.cancel-modal{border-color:rgba(248,113,113,.3)}
.modal-box.cancel-modal h3{color:#fca5a5}
.cancel-icon{text-align:center;font-size:52px;margin-bottom:8px}
.cancel-event-name{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:12px;padding:14px 18px;margin:16px 0 20px;font-size:14px;font-weight:600;color:var(--white);text-align:center;line-height:1.4}
.cancel-warn{background:rgba(248,113,113,.07);border:1px solid rgba(248,113,113,.2);border-radius:12px;padding:14px 18px;margin-bottom:22px;font-size:13px;color:#fca5a5;line-height:1.6}
.btn-modal-danger{flex:1;padding:13px;background:linear-gradient(135deg,#ef4444,#dc2626);color:#fff;border:none;border-radius:50px;font-size:14px;font-weight:700;cursor:pointer;font-family:var(--font-body);text-decoration:none;display:flex;align-items:center;justify-content:center;gap:6px;transition:opacity .2s,transform .2s}
.btn-modal-danger:hover{opacity:.88;transform:scale(1.03)}

/* ── CANCEL TOAST ── */
.toast-cancel{position:fixed;bottom:32px;left:50%;transform:translateX(-50%) translateY(80px);background:linear-gradient(135deg,#1c1c2e,#2a1a2e);border:1px solid rgba(248,113,113,.35);border-radius:50px;padding:14px 26px;display:flex;align-items:center;gap:10px;font-size:14px;font-weight:600;color:var(--white);box-shadow:0 12px 40px rgba(0,0,0,.5);z-index:9998;opacity:0;transition:all .45s cubic-bezier(.175,.885,.32,1.275)}
.toast-cancel.show{opacity:1;transform:translateX(-50%) translateY(0)}
.toast-cancel .t-icon{font-size:18px}
.toast-cancel .t-msg{color:var(--text)}
.toast-cancel .t-msg strong{color:#fca5a5}
</style>
</head>
<body>

<?php if ($reg_success): ?>
<!-- SUCCESS MODAL -->
<div class="modal-overlay active" id="successModal">
  <div class="modal-box success-modal">

    <div class="modal-confetti"><?= $reg_success['jenis_harga']==='gratis' ? '🎉' : '🎫' ?></div>
    <h3><?= $reg_success['jenis_harga']==='gratis' ? 'Registration Successful!' : 'Booking Received!' ?></h3>

    <div class="modal-tag <?= $reg_success['jenis_harga']==='gratis' ? 'free' : 'paid' ?>">
      <?= $reg_success['jenis_harga']==='gratis' ? '✓ FREE EVENT · STATUS: CONFIRMED' : '💳 PAID EVENT · STATUS: PENDING PAYMENT' ?>
    </div>

    <!-- Event Detail Card -->
    <div class="event-card-modal">
      <div class="ev-name"><?= htmlspecialchars($reg_success['event_name']) ?></div>
      <div class="ev-detail-row">
        <span class="icon">📅</span>
        <span class="val"><?= date('l, d F Y', strtotime($reg_success['event_date'])) ?></span>
      </div>
      <div class="ev-detail-row">
        <span class="icon">📍</span>
        <span class="val"><?= htmlspecialchars($reg_success['event_loc']) ?></span>
      </div>
      <div class="ev-detail-row">
        <span class="icon">👤</span>
        <span class="val"><?= htmlspecialchars($reg_success['user_nama']) ?></span>
      </div>
      <?php if ($reg_success['jenis_harga']==='berbayar'): ?>
      <div class="ev-detail-row">
        <span class="icon">💰</span>
        <span class="val">Rp <?= number_format($reg_success['harga'],0,',','.') ?></span>
      </div>
      <?php endif; ?>
    </div>

    <?php if ($reg_success['jenis_harga']==='gratis'): ?>
    <!-- Email notice for free events -->
    <div class="email-notice">
      <span class="icon">✉️</span>
      <p>A confirmation email has been sent to <strong><?= htmlspecialchars($reg_success['user_email']) ?></strong> with your registration details. Please check your inbox (and spam folder) for further instructions.</p>
    </div>
    <?php else: ?>
    <!-- Payment notice for paid events -->
    <div class="payment-notice">
      <span class="icon">⚠️</span>
      <p>Your spot is <strong>reserved</strong>. Complete payment via bank transfer to <strong>BRI 1234-5678-9012 (EVENTLOG)</strong>, then send proof to <strong>WhatsApp 0812-3456-7890</strong>. A confirmation email will be sent to <strong><?= htmlspecialchars($reg_success['user_email']) ?></strong> once verified.</p>
    </div>
    <?php endif; ?>

    <div class="modal-btn-row">
      <a href="index.php" class="btn-modal-ghost">← Browse More Events</a>
      <button class="btn-modal-primary" onclick="document.getElementById('successModal').classList.remove('active')">
        View My Registrations ✓
      </button>
    </div>

  </div>
</div>
<?php endif; ?>

<!-- CANCEL CONFIRM MODAL -->
<div class="modal-overlay" id="cancelModal">
  <div class="modal-box cancel-modal">
    <div class="cancel-icon">⚠️</div>
    <h3>Cancel Registration?</h3>
    <div class="cancel-event-name" id="cancelEventName">—</div>
    <div class="cancel-warn">
      Tindakan ini tidak dapat dibatalkan. Pendaftaran kamu untuk event ini akan dihapus permanen dan slot kamu akan dilepaskan.
    </div>
    <div class="modal-btn-row">
      <button class="btn-modal-ghost" onclick="closeCancelModal()">← Kembali</button>
      <a href="#" id="cancelConfirmBtn" class="btn-modal-danger">🗑️ Ya, Batalkan</a>
    </div>
  </div>
</div>

<!-- CANCEL SUCCESS TOAST -->
<?php if ($cancel_success): ?>
<div class="toast-cancel" id="cancelToast">
  <span class="t-icon">✅</span>
  <span class="t-msg">Pendaftaran untuk <strong><?= htmlspecialchars($cancel_success) ?></strong> berhasil dibatalkan.</span>
</div>
<?php endif; ?>
<nav class="navbar">
  <a href="index.php" class="logo">EVENTLOG</a>
  <div class="nav-links">
    <a href="index.php">Browse Events</a>
    <a href="registrasi_saya.php">My Registrations</a>
    <a href="auth/logout.php" class="nav-cta">Logout</a>
  </div>
</nav>

<div class="container">
  <div class="sec-label">MY ACCOUNT</div>
  <h2 class="sec-title" style="margin-bottom:32px">My Registrations</h2>

  <?php if (mysqli_num_rows($result) == 0): ?>
    <div class="empty-state">
      <div style="font-size:52px;margin-bottom:16px">📋</div>
      <p>You haven't registered for any events yet.</p>
      <a href="index.php" class="btn-ticket" style="margin-top:20px;display:inline-flex">Browse Events</a>
    </div>
  <?php else: ?>
    <div class="table-container">
      <table>
        <tr>
          <th>#</th>
          <th>Event Name</th>
          <th>Date</th>
          <th>Location</th>
          <th>Price</th>
          <th>Status</th>
          <th>Registered On</th>
          <th>Action</th>
        </tr>
        <?php $no=1; while($row=mysqli_fetch_assoc($result)): ?>
          <tr>
            <td><?=$no++?></td>
            <td style="font-weight:600;color:var(--white)"><?=htmlspecialchars($row['nama_event'])?></td>
            <td><?=date('d M Y',strtotime($row['tanggal']))?></td>
            <td style="color:var(--muted)"><?=htmlspecialchars($row['lokasi'])?></td>
            <td>
              <?php if($row['jenis_harga']==='gratis'): ?>
                <span class="badge-price badge-free" style="font-size:11px;padding:3px 10px">FREE</span>
              <?php else: ?>
                <span style="color:var(--text);font-size:13px">Rp <?=number_format($row['harga'],0,',','.')?></span>
              <?php endif; ?>
            </td>
            <td>
              <span class="status-<?=$row['status']?>">
                <?=ucfirst($row['status'])?>
              </span>
            </td>
            <td style="color:var(--muted)"><?=date('d M Y, H:i',strtotime($row['tgl_daftar']))?></td>
            <td>
              <button
                class="btn-cancel-reg"
                onclick="openCancelModal(<?=(int)$row['id']?>, <?=htmlspecialchars(json_encode($row['nama_event']))?> )"
                title="Batalkan pendaftaran"
              >✕ Cancel</button>
            </td>
          </tr>
        <?php endwhile; ?>
      </table>
    </div>
  <?php endif; ?>
</div>

<footer class="footer">
  <div class="footer-inner">
    <div class="footer-grid">
      <div class="footer-brand">
        <div class="footer-logo">EVENTLOG</div>
        <p>Your trusted platform to discover and register for exciting events near you.</p>
      </div>
      <div class="footer-col">
        <h4>Quick Links</h4>
        <a href="index.php">Home</a>
        <a href="registrasi_saya.php">My Registrations</a>
        <a href="auth/logout.php">Logout</a>
      </div>
      <div class="footer-col">
        <h4>Explore Events</h4>
        <a href="index.php">All Events</a>
        <a href="index.php?jenis=gratis">Free Events</a>
        <a href="index.php?jenis=berbayar">Paid Events</a>
      </div>
      <div class="footer-col">
        <h4>Contact</h4>
        <a href="mailto:info@eventlog.id">info@eventlog.id</a>
        <a href="https://maps.google.com/?q=Jakarta,+Indonesia" target="_blank" rel="noopener">Jakarta, Indonesia</a>
      </div>
    </div>
    <div class="footer-bottom">
      <span>&copy; <?=date('Y')?> EVENTLOG — All rights reserved</span>
      <span>Made with ❤️ in Indonesia</span>
    </div>
  </div>
</footer>
<style>
.btn-cancel-reg{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#fca5a5;border-radius:50px;padding:5px 14px;font-size:12px;font-weight:700;cursor:pointer;font-family:var(--font-body);transition:all .2s;white-space:nowrap}
.btn-cancel-reg:hover{background:rgba(239,68,68,.22);border-color:rgba(239,68,68,.55);color:#fff}
</style>
<script>
function openCancelModal(regId, eventName) {
  document.getElementById('cancelEventName').textContent = eventName;
  document.getElementById('cancelConfirmBtn').href = 'registrasi/cancel_event.php?reg_id=' + regId;
  document.getElementById('cancelModal').classList.add('active');
}
function closeCancelModal() {
  document.getElementById('cancelModal').classList.remove('active');
}
// Close modal when clicking overlay background
document.getElementById('cancelModal').addEventListener('click', function(e){
  if (e.target === this) closeCancelModal();
});
// Show cancel toast if present
(function(){
  var t = document.getElementById('cancelToast');
  if (!t) return;
  setTimeout(function(){ t.classList.add('show'); }, 100);
  setTimeout(function(){ t.classList.remove('show'); }, 4000);
})();
</script>
</body>
</html>
