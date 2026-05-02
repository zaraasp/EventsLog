-- =========================
-- CREATE DATABASE
-- =========================
CREATE DATABASE IF NOT EXISTS eventsphere;
USE eventsphere;

-- =========================
-- USERS TABLE
-- =========================
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','user') NOT NULL DEFAULT 'user',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- EVENTS TABLE
-- =========================
CREATE TABLE events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama_event VARCHAR(150) NOT NULL,
  deskripsi TEXT,
  tanggal DATE NOT NULL,
  lokasi VARCHAR(200) NOT NULL,
  kuota INT NOT NULL DEFAULT 50,
  harga INT NOT NULL DEFAULT 0,
  jenis_harga ENUM('gratis','berbayar') NOT NULL DEFAULT 'gratis',
  user_id INT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- =========================
-- REGISTRATIONS TABLE
-- =========================
CREATE TABLE registrations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  event_id INT NOT NULL,
  status ENUM('pending','approved','rejected') DEFAULT 'pending',
  tgl_daftar DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
);

-- =========================
-- SAMPLE USERS
-- =========================
INSERT INTO users (nama, email, password, role) VALUES
('Administrator', 'admin@eventsphere.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('User Biasa', 'user@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');

-- =========================
-- SAMPLE EVENTS
-- =========================
INSERT INTO events (nama_event, deskripsi, tanggal, lokasi, kuota, harga, jenis_harga, user_id) VALUES
('Workshop Laravel Dasar', 'Belajar Laravel dari nol', '2025-05-15', 'Aula A', 50, 0, 'gratis', 1),
('Seminar UI/UX', 'Belajar design modern', '2025-05-22', 'Ballroom', 100, 150000, 'berbayar', 1),
('Hackathon 24 Jam', 'Kompetisi coding', '2025-06-01', 'Lab Komputer', 80, 50000, 'berbayar', 1),
('New Year Tech Kickoff 2026', 'Penyelenggara: TechIndo Community. Waktu: 10 Januari 2026, 10:00 WIB', '2026-01-10', 'Jakarta Convention Center', 500, 200000, 'berbayar', NULL),
('Bekasi Creative Market & Workshop', 'Penyelenggara: Bekasi Creative Hub. Waktu: 24 Januari 2026, 09:00 WIB', '2026-01-24', 'Grand Metropolitan Mall', 2000, 0, 'gratis', NULL),
('Valentine Music Festival', 'Penyelenggara: LiveNation ID. Waktu: 14 Februari 2026, 18:00 WIB', '2026-02-14', 'Gelora Bung Karno', 5000, 500000, 'berbayar', NULL),
('Workshop Public Speaking & MC', 'Penyelenggara: SpeakUp Academy. Waktu: 21 Februari 2026, 09:00 WIB', '2026-02-21', 'Hotel Santika Mega City', 100, 150000, 'berbayar', NULL),
('Jakarta International Java Jazz Festival', 'Penyelenggara: Java Festival Production. Waktu: 6 Maret 2026, 15:00 WIB', '2026-03-06', 'Jakarta International Expo', 15000, 850000, 'berbayar', NULL),
('Seminar Karir & Job Fair Bekasi', 'Penyelenggara: Disnaker Kota Bekasi. Waktu: 20 Maret 2026, 08:00 WIB', '2026-03-20', 'Bekasi Cyber Park', 3000, 0, 'gratis', NULL),
('Indonesia International Motor Show', 'Penyelenggara: Dyandra Promosindo. Waktu: 4 April 2026, 10:00 WIB', '2026-04-04', 'Jakarta International Expo', 20000, 80000, 'berbayar', NULL),
('Ramadhan Culinary Expo & Tausiyah', 'Penyelenggara: Pemkot Bekasi. Waktu: 18 April 2026, 15:00 WIB', '2026-04-18', 'Summarecon Mall Bekasi', 5000, 0, 'gratis', NULL),
('Jakarta Sneaker Day 2026', 'Penyelenggara: JSD Team. Waktu: 9 Mei 2026, 10:00 WIB', '2026-05-09', 'Senayan City', 8000, 120000, 'berbayar', NULL),
('Bootcamp Data Science for Beginners', 'Penyelenggara: DataCamp ID. Waktu: 23 Mei 2026, 09:00 WIB', '2026-05-23', 'BSI Convention Center', 50, 1200000, 'berbayar', NULL),
('Pekan Raya Jakarta', 'Penyelenggara: JIExpo & Pemprov DKI. Waktu: 15 Juni 2026, 15:00 WIB', '2026-06-15', 'Jakarta International Expo', 50000, 50000, 'berbayar', NULL),
('Konser Pesta Rakyat Bekasi', 'Penyelenggara: Bekasi Music Promotor. Waktu: 27 Juni 2026, 19:00 WIB', '2026-06-27', 'Patriot Candrabhaga Stadium', 10000, 100000, 'berbayar', NULL),
('Konser: Mitski (Live in Jakarta)', 'Penyelenggara: TBA (Promotor Internasional). Waktu: 19.00 WIB – Selesai', '2026-07-18', 'Jakarta (Venue spesifik menyusul)', 5000, 1200000, 'berbayar', NULL),
('Festival: Bandung Arts Festival', 'Penyelenggara: Dinas Kebudayaan & Pariwisata Jawa Barat. Waktu: 09.00 – 22.00 WIB. Beberapa workshop berbayar Rp50.000', '2026-07-13', 'Berbagai lokasi di Bandung (pusat di GOR Sapura/Taman Budaya)', 0, 0, 'gratis', NULL),
('Konser: The Sounds Project', 'Penyelenggara: Sounds Project Team. Waktu: 14.00 – 23.59 WIB (3-day pass)', '2026-08-07', 'Eco Park Ancol, Jakarta', 20000, 450000, 'berbayar', NULL),
('Festival & Lifestyle: Bandung Art Month', 'Penyelenggara: Komunitas Seni Bandung & Disbudpar. Waktu: 10.00 – 18.00 WIB. Harga variatif', '2026-08-15', 'Galeri-galeri seni di Bandung', 0, 0, 'gratis', NULL),
('Festival Musik: Pestapora', 'Penyelenggara: Boss Creator. Waktu: 13.00 – 00.00 WIB (3-day pass early bird)', '2026-09-25', 'JIExpo Kemayoran, Jakarta', 30000, 600000, 'berbayar', NULL),
('Seminar: Indonesia Economic Forum 2026', 'Penyelenggara: IEF Group. Waktu: 09.00 – 17.00 WIB', '2026-09-28', 'Hotel Raffles/Ritz Carlton, Jakarta', 1000, 2500000, 'berbayar', NULL),
('Festival Musik: Synchronize Festival', 'Penyelenggara: demajors & Dyandra Promosindo. Waktu: 14.00 – 01.00 WIB', '2026-10-16', 'Gambir Expo Kemayoran, Jakarta', 25000, 550000, 'berbayar', NULL),
('Konser: Papandayan Jazz Festival', 'Penyelenggara: The Papandayan Hotel. Waktu: 15.00 – 23.00 WIB', '2026-10-19', 'Hotel Papandayan, Bandung', 2000, 250000, 'berbayar', NULL),
('Konser: My Chemical Romance (Live in Jakarta)', 'Penyelenggara: TBA (Promotor Internasional). Waktu: 19.00 WIB – Selesai', '2026-11-22', 'Nusantara International Convention Exhibition (NICE) PIK 2', 15000, 1500000, 'berbayar', NULL),
('Workshop/Event Budaya: Angklung Pride', 'Penyelenggara: Yayasan Saung Angklung Udjo. Waktu: 09.00 – 15.00 WIB', '2026-11-15', 'Saung Angklung Udjo, Bandung', 1000, 100000, 'berbayar', NULL),
('Konser: BTS World Tour 2026-2027', 'Penyelenggara: HYBE / BigHit Music (Lokal partner TBA). Waktu: 19.00 WIB', '2026-12-26', 'Jakarta International Stadium atau Gelora Bung Karno', 50000, 1500000, 'berbayar', NULL),
('Workshop: Year-End Creative Tech Workshop', 'Penyelenggara: Komunitas Teknologi Lokal/Academy. Waktu: 10.00 – 16.00 WIB', '2026-12-14', 'Co-working space ternama di Jakarta (misal: GoWork/WeWork)', 100, 350000, 'berbayar', NULL);

-- =========================
-- SAMPLE REGISTRATIONS
-- =========================
INSERT INTO registrations (user_id, event_id, status) VALUES
(2, 1, 'pending'),
(2, 2, 'pending');