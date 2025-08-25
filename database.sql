-- Buat database (opsional, bisa dijalankan manual jika perlu)
-- CREATE DATABASE IF NOT EXISTS pemilihan_osis CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE pemilihan_osis;

-- Tabel siswa
CREATE TABLE IF NOT EXISTS siswa (
	nisn VARCHAR(20) PRIMARY KEY,
	nama VARCHAR(100) NOT NULL,
	kelas VARCHAR(20) NOT NULL,
	password_hash VARCHAR(255) NOT NULL,
	has_voted TINYINT(1) NOT NULL DEFAULT 0,
	voted_at DATETIME NULL,
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel users (admin dan wali kelas)
CREATE TABLE IF NOT EXISTS users (
	id INT AUTO_INCREMENT PRIMARY KEY,
	username VARCHAR(50) NOT NULL UNIQUE,
	nama VARCHAR(100) NOT NULL,
	role ENUM('admin','walikelas') NOT NULL,
	kelas VARCHAR(20) NULL,
	password_hash VARCHAR(255) NOT NULL,
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel calon OSIS
CREATE TABLE IF NOT EXISTS calon (
	id INT AUTO_INCREMENT PRIMARY KEY,
	nama VARCHAR(100) NOT NULL,
	foto VARCHAR(255) NULL,
	visi TEXT NOT NULL,
	misi TEXT NOT NULL,
	nomor_urut INT NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel votes
CREATE TABLE IF NOT EXISTS votes (
	id INT AUTO_INCREMENT PRIMARY KEY,
	nisn VARCHAR(20) NOT NULL,
	calon_id INT NOT NULL,
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	UNIQUE KEY uniq_vote_nisn (nisn),
	INDEX idx_votes_calon (calon_id),
	CONSTRAINT fk_votes_siswa FOREIGN KEY (nisn) REFERENCES siswa(nisn) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT fk_votes_calon FOREIGN KEY (calon_id) REFERENCES calon(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel settings (untuk status voting, dll.)
CREATE TABLE IF NOT EXISTS settings (
	id INT AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(50) NOT NULL UNIQUE,
	value VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Set default: voting belum dibuka
INSERT INTO settings (name, value)
VALUES ('voting_open', '0')
ON DUPLICATE KEY UPDATE value = VALUES(value);

-- Contoh admin default (password MD5: admin123) agar mudah login awal
INSERT INTO users (username, nama, role, kelas, password_hash)
VALUES ('admin', 'Administrator', 'admin', NULL, MD5('admin123'))
ON DUPLICATE KEY UPDATE nama=VALUES(nama), role=VALUES(role);

