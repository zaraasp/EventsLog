<?php
require_once 'koneksi.php';
session_start();

$search         = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_jenis   = isset($_GET['jenis'])  ? $_GET['jenis'] : '';
$bulan_selected = isset($_GET['bulan'])  ? (int)$_GET['bulan'] : 0;
$tahun_selected = isset($_GET['tahun'])  ? (int)$_GET['tahun'] : 0;

// Flash messages from redirects
$flash_msgs = [
    'already_registered' => '⚠️ You are already registered for this event.',
    'event_not_found'    => '❌ Event not found.',
    'failed'             => '❌ Registration failed. Please try again.',
];
$flash = isset($_GET['msg']) && isset($flash_msgs[$_GET['msg']]) ? $flash_msgs[$_GET['msg']] : '';

$months = [1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',
           7=>'July',8=>'August',9=>'September',10=>'October',11=>'November',12=>'December'];

// ── Build query ──
$where  = ["nama_event LIKE ?"];
$types  = 's';
$params = ["%{$search}%"];

if ($bulan_selected > 0) { $where[] = "MONTH(tanggal)=?"; $types .= 'i'; $params[] = $bulan_selected; }
if ($tahun_selected > 0) { $where[] = "YEAR(tanggal)=?";  $types .= 'i'; $params[] = $tahun_selected; }
if ($filter_jenis === 'gratis' || $filter_jenis === 'berbayar') {
    $where[] = "jenis_harga=?"; $types .= 's'; $params[] = $filter_jenis;
}

$query = "SELECT * FROM events WHERE " . implode(' AND ', $where) . " ORDER BY tanggal ASC";
$stmt  = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// ── Page title ──
$title_parts = [];
if ($filter_jenis === 'gratis')   $title_parts[] = "Free";
if ($filter_jenis === 'berbayar') $title_parts[] = "Paid";
if ($bulan_selected > 0)          $title_parts[] = $months[$bulan_selected];
if ($tahun_selected > 0)          $title_parts[] = $tahun_selected;
$page_title = count($title_parts) ? implode(" ", $title_parts) . " Events" : "All Events";

function url_without($skip) {
    $p = $_GET;
    foreach ((array)$skip as $k) unset($p[$k]);
    return 'index.php' . (count($p) ? '?' . http_build_query($p) : '');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>EVENTLOG — Discover Amazing Events</title>
<link rel="stylesheet" href="style/style.css">
<style>
/* ── FLASH ── */
.flash-bar{padding:13px 20px;border-radius:10px;font-size:14px;margin-bottom:20px;text-align:center}
.flash-warn{background:rgba(251,191,36,.1);color:#fcd34d;border:1px solid rgba(251,191,36,.25)}
.flash-err{background:rgba(248,113,113,.1);color:#fca5a5;border:1px solid rgba(248,113,113,.22)}

/* ── FILTER TAGS ── */
.filter-active-bar{display:flex;align-items:center;gap:8px;margin-bottom:20px;flex-wrap:wrap}
.filter-tag{display:inline-flex;align-items:center;gap:6px;padding:5px 13px;border-radius:50px;font-size:12px;font-weight:700;letter-spacing:.4px}
.filter-tag-purple{background:rgba(124,58,237,.15);color:var(--purple-light);border:1px solid rgba(124,58,237,.3)}
.filter-tag-green{background:rgba(34,197,94,.12);color:#4ade80;border:1px solid rgba(34,197,94,.25)}
.filter-tag-blue{background:rgba(56,189,248,.1);color:#7dd3fc;border:1px solid rgba(56,189,248,.2)}
.filter-tag a{color:inherit;opacity:.6;text-decoration:none;font-size:13px;margin-left:2px}
.filter-tag a:hover{opacity:1}
.filter-clear-all{font-size:12px;color:var(--muted);text-decoration:none;padding:5px 12px;border:1px solid var(--border);border-radius:50px;transition:all .2s}
.filter-clear-all:hover{color:var(--white);border-color:rgba(255,255,255,.25)}

/* ── PAYMENT MODAL ── */
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.78);backdrop-filter:blur(7px);z-index:9999;align-items:center;justify-content:center;padding:20px}
.modal-overlay.active{display:flex}
.modal-box{background:linear-gradient(135deg,#141425,#1a1a2e);border:1px solid rgba(124,58,237,.35);border-radius:22px;padding:42px 38px;max-width:490px;width:100%;position:relative;box-shadow:0 28px 80px rgba(0,0,0,.65),0 0 60px rgba(124,58,237,.15);animation:modalIn .32s cubic-bezier(.175,.885,.32,1.275)}
@keyframes modalIn{from{opacity:0;transform:scale(.91) translateY(22px)}to{opacity:1;transform:scale(1) translateY(0)}}
.modal-icon{width:64px;height:64px;background:linear-gradient(135deg,rgba(124,58,237,.2),rgba(224,64,170,.15));border:1px solid rgba(124,58,237,.3);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:28px;margin:0 auto 18px}
.modal-box h3{font-family:var(--font-display);font-size:26px;letter-spacing:2px;color:var(--white);text-align:center;margin-bottom:5px}
.modal-subtitle{text-align:center;color:var(--muted);font-size:13.5px;margin-bottom:26px}
.modal-event-name{font-weight:700;color:var(--white);font-size:15px;background:rgba(255,255,255,.04);border:1px solid var(--border);border-radius:10px;padding:12px 16px;margin-bottom:20px;text-align:center}
.modal-price-block{background:linear-gradient(135deg,rgba(124,58,237,.12),rgba(224,64,170,.08));border:1px solid rgba(124,58,237,.25);border-radius:12px;padding:20px 24px;margin-bottom:26px}
.modal-price-row{display:flex;justify-content:space-between;align-items:center;font-size:13px;color:var(--muted);margin-bottom:8px}
.modal-price-row:last-child{margin-bottom:0;border-top:1px solid rgba(255,255,255,.07);padding-top:12px;margin-top:4px;font-size:15px;font-weight:700;color:var(--white)}
.modal-price-row .val{color:var(--white);font-weight:600}
.modal-price-row:last-child .val{background:var(--grad);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;font-size:17px}
.modal-notice{background:rgba(251,191,36,.07);border:1px solid rgba(251,191,36,.2);border-radius:10px;padding:12px 16px;font-size:13px;color:#fcd34d;margin-bottom:22px;line-height:1.6}
.modal-actions{display:flex;gap:10px}
.modal-actions .btn-confirm{flex:1;padding:13px;background:var(--grad);color:var(--white);border:none;border-radius:50px;font-size:14px;font-weight:700;cursor:pointer;font-family:var(--font-body);transition:opacity .2s,transform .2s;text-decoration:none;display:flex;align-items:center;justify-content:center;gap:6px}
.modal-actions .btn-confirm:hover{opacity:.9;transform:scale(1.03)}
.modal-actions .btn-cancel{padding:13px 20px;background:rgba(255,255,255,.05);color:var(--muted);border:1px solid var(--border2);border-radius:50px;font-size:14px;font-weight:600;cursor:pointer;font-family:var(--font-body);transition:all .2s}
.modal-actions .btn-cancel:hover{color:var(--white);border-color:rgba(255,255,255,.3)}
.modal-close{position:absolute;top:16px;right:16px;background:rgba(255,255,255,.06);border:1px solid var(--border);color:var(--muted);border-radius:50%;width:32px;height:32px;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:16px;transition:all .2s}
.modal-close:hover{color:var(--white);background:rgba(255,255,255,.12)}
</style>
</head>
<body>

<!-- PAYMENT MODAL (paid events) -->
<div class="modal-overlay" id="paymentModal">
  <div class="modal-box">
    <button class="modal-close" onclick="closeModal()">✕</button>
    <div class="modal-icon">💳</div>
    <h3>CHECKOUT</h3>
    <p class="modal-subtitle">Please review your order details before confirming</p>
    <div class="modal-event-name" id="modalEventName">—</div>
    <div class="modal-price-block">
      <div class="modal-price-row"><span>Ticket Price</span><span class="val" id="modalHarga">—</span></div>
      <div class="modal-price-row"><span>Service Fee</span><span class="val">Rp 0</span></div>
      <div class="modal-price-row"><span>Total</span><span class="val" id="modalTotal">—</span></div>
    </div>
    <div class="modal-notice">⚠️ Payment is made via <strong>manual bank transfer</strong>. Proof of payment must be submitted to the organizer. Your registration status will change from <strong>Pending → Approved</strong> after verification.</div>
    <div class="modal-actions">
      <button class="btn-cancel" onclick="closeModal()">Cancel</button>
      <a href="#" id="modalConfirmBtn" class="btn-confirm">✓ Confirm &amp; Register</a>
    </div>
  </div>
</div>

<!-- NAVBAR -->
<nav class="navbar">
  <a href="index.php" class="logo">EVENTLOG</a>
  <div class="nav-links">
    <?php if(isset($_SESSION['user_id'])): ?>
      <a href="#">Hello, <?=htmlspecialchars($_SESSION['nama']??'User')?> 👋</a>
      <a href="registrasi_saya.php">My Registrations</a>
      <?php if(($_SESSION['role']??'')==='admin'): ?>
        <a href="events/dashboard.php" style="color: #fbbf24; font-weight: bold;" >Admin Panel</a>
      <?php endif; ?>
      <a href="auth/logout.php" class="nav-cta">Logout</a>
    <?php else: ?>
      <a href="auth/login.php">Sign In</a>
      <a href="auth/register.php" class="nav-cta">Sign Up →</a>
    <?php endif; ?>
  </div>
</nav>

<!-- HERO -->
<div class="hero">
  <div class="hero-bg"></div>
  <div class="hero-grid"></div>
  <div class="hero-content">
    <div class="hero-pill">🎉 Indonesia's Best Event Platform</div>
    <h1>Discover <em>Amazing</em><br>Events Near You</h1>
    <p>Register for your favorite events and never miss a great moment!</p>
    <div class="hero-actions">
      <a href="#events" class="hero-btn hero-btn-primary">Browse Events →</a>
      <?php if(!isset($_SESSION['user_id'])): ?>
        <a href="auth/register.php" class="hero-btn hero-btn-ghost">Join for Free</a>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- MAIN -->
<div class="container" id="events">
  <div class="sec-label">EVENT SCHEDULE</div>
  <div style="display:flex;justify-content:space-between;align-items:flex-end;margin-bottom:24px;flex-wrap:wrap;gap:16px;">
    <h2 class="sec-title" style="margin-bottom:0;"><?=$page_title?></h2>
  </div>

  <?php if($flash): ?>
    <div class="flash-bar flash-warn"><?=$flash?></div>
  <?php endif; ?>

  <!-- ACTIVE FILTER BADGES -->
  <?php
  $has_filter = ($filter_jenis !== '' || $bulan_selected > 0 || $tahun_selected > 0 || $search !== '');
  if ($has_filter): ?>
  <div class="filter-active-bar">
    <?php if($filter_jenis === 'gratis'): ?>
      <span class="filter-tag filter-tag-green">✓ Free <a href="<?=url_without('jenis')?>">✕</a></span>
    <?php elseif($filter_jenis === 'berbayar'): ?>
      <span class="filter-tag filter-tag-purple">💳 Paid <a href="<?=url_without('jenis')?>">✕</a></span>
    <?php endif; ?>
    <?php if($bulan_selected > 0): ?>
      <span class="filter-tag filter-tag-blue">📅 <?=$months[$bulan_selected]?> <a href="<?=url_without('bulan')?>">✕</a></span>
    <?php endif; ?>
    <?php if($tahun_selected > 0): ?>
      <span class="filter-tag filter-tag-blue">📅 <?=$tahun_selected?> <a href="<?=url_without('tahun')?>">✕</a></span>
    <?php endif; ?>
    <?php if($search !== ''): ?>
      <span class="filter-tag filter-tag-blue">🔍 "<?=htmlspecialchars($search)?>" <a href="<?=url_without('search')?>">✕</a></span>
    <?php endif; ?>
    <a href="index.php" class="filter-clear-all">Clear all filters</a>
  </div>
  <?php endif; ?>

  <!-- FILTER FORM -->
  <div class="filter-box">
    <form method="GET" action="index.php" class="filter-form">
      <?php if($filter_jenis !== ''): ?>
        <input type="hidden" name="jenis" value="<?=htmlspecialchars($filter_jenis)?>">
      <?php endif; ?>
      <input type="text" name="search" placeholder="🔍  Search events..." value="<?=htmlspecialchars($search)?>">
      <select name="bulan">
        <option value="0">All Months</option>
        <?php for($b=1;$b<=12;$b++): ?>
          <option value="<?=$b?>" <?=$b==$bulan_selected?'selected':''?>><?=$months[$b]?></option>
        <?php endfor; ?>
      </select>
      <select name="tahun">
        <option value="0">All Years</option>
        <?php for($y=2025;$y<=2027;$y++): ?>
          <option value="<?=$y?>" <?=$y==$tahun_selected?'selected':''?>><?=$y?></option>
        <?php endfor; ?>
      </select>
      <button type="submit">Search</button>
    </form>
  </div>

  <!-- EVENT LIST -->
  <?php $total = mysqli_num_rows($result); ?>
  <?php if($total == 0): ?>
    <div class="empty-state">
      <div style="font-size:52px;margin-bottom:16px;">🔍</div>
      <p>No events found. Try adjusting your filters!</p>
      <a href="index.php" style="display:inline-block;margin-top:20px;color:var(--purple-light);text-decoration:none;font-size:14px">← Reset all filters</a>
    </div>
  <?php else: ?>
    <p style="color:var(--muted);font-size:13px;margin-bottom:20px;">Showing <strong style="color:var(--white)"><?=$total?></strong> event<?=$total>1?'s':''?></p>
    <div class="event-list">
      <?php $d=0; while($ev=mysqli_fetch_assoc($result)): ?>
        <div class="event-row" style="animation-delay:<?=$d?>ms">
          <div class="event-row-info">
            <h3><?=htmlspecialchars($ev['nama_event'])?></h3>
            <p><?=htmlspecialchars($ev['deskripsi'])?></p>
          </div>
          <div class="event-meta">
            <div class="event-meta-item"><span class="meta-dot"></span><?=date('d M Y',strtotime($ev['tanggal']))?></div>
            <div class="event-meta-item"><span class="meta-dot pink"></span><?=htmlspecialchars($ev['lokasi'])?></div>
          </div>
          <div class="event-badges">
            <span class="badge-kuota"><?=$ev['kuota']?> seats</span>
            <?php if($ev['jenis_harga']==='berbayar'): ?>
              <span class="badge-price badge-paid">Rp <?=number_format($ev['harga'],0,',','.')?></span>
            <?php else: ?>
              <span class="badge-price badge-free">✓ FREE</span>
            <?php endif; ?>
          </div>
          <?php if(isset($_SESSION['user_id'])): ?>
            <?php if($ev['jenis_harga']==='berbayar'): ?>
              <button class="btn-ticket" onclick="openPaymentModal('<?=addslashes(htmlspecialchars($ev['nama_event']))?>',<?=$ev['harga']?>,<?=$ev['id']?>)">Get Ticket →</button>
            <?php else: ?>
              <a href="registrasi/daftar_event.php?event_id=<?=$ev['id']?>" class="btn-ticket">Register Free →</a>
            <?php endif; ?>
          <?php else: ?>
            <a href="auth/login.php" class="btn-ticket-ghost">Sign In to Register</a>
          <?php endif; ?>
        </div>
      <?php $d+=60; endwhile; ?>
    </div>
  <?php endif; ?>
  <?php mysqli_stmt_close($stmt); ?>
</div>

<!-- NEWSLETTER -->
<div class="newsletter">
  <h2>Get the Latest Events<br>Delivered to Your Inbox</h2>
  <form class="nl-form" onsubmit="return false">
    <input type="email" placeholder="Enter your email address...">
    <button type="submit">→</button>
  </form>
</div>

<!-- FOOTER -->
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
        <a href="auth/login.php">Sign In</a>
        <a href="auth/register.php">Create Account</a>
        <?php if(isset($_SESSION['user_id'])): ?>
          <a href="registrasi_saya.php">My Registrations</a>
        <?php endif; ?>
        <?php if(($_SESSION['role']??'')==='admin'): ?>
          <a href="events/dashboard.php">Admin Panel</a>
        <?php endif; ?>
      </div>
      <div class="footer-col">
        <h4>Explore Events</h4>
        <a href="index.php">All Events</a>
        <a href="index.php?jenis=gratis">Free Events</a>
        <a href="index.php?jenis=berbayar">Paid Events</a>
        <a href="index.php?bulan=<?=date('n')?>&tahun=<?=date('Y')?>">This Month</a>
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

<script>
function openPaymentModal(name, price, eventId) {
  const fmt = 'Rp ' + parseInt(price).toLocaleString('id-ID');
  document.getElementById('modalEventName').textContent = name;
  document.getElementById('modalHarga').textContent = fmt;
  document.getElementById('modalTotal').textContent = fmt;
  document.getElementById('modalConfirmBtn').href = 'registrasi/daftar_event.php?event_id=' + eventId;
  document.getElementById('paymentModal').classList.add('active');
}
function closeModal() {
  document.getElementById('paymentModal').classList.remove('active');
}
document.getElementById('paymentModal').addEventListener('click', function(e) {
  if (e.target === this) closeModal();
});
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') closeModal();
});
</script>
</body>
</html>
