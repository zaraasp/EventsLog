<?php
// ================================================
//  koneksi.php
//  File ini dipakai di semua halaman PHP lain
//  dengan cara: include 'koneksi.php';
// ================================================
 
$host     = "localhost";   // server database (biasanya localhost)
$user     = "root";        // username MySQL (default XAMPP = root)
$password = "";            // password MySQL (default XAMPP = kosong)
$database = "eventsphere"; // nama database yang sudah dibuat
 
// Buat koneksi ke MySQL
$conn = mysqli_connect($host, $user, $password, $database);
 
// Cek apakah koneksi berhasil
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
 