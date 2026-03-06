-- Datenbank für dynamische QR Codes
-- Bei all-inkl.com über phpMyAdmin ausführen

-- Admin-User Tabelle (sicheres Passwort-Hashing)
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` varchar(20) DEFAULT 'admin',
  `erstellt_am` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `qr_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shortcode` varchar(50) NOT NULL,
  `ziel_url` text NOT NULL,
  `titel` varchar(255) DEFAULT NULL,
  `beschreibung` text DEFAULT NULL,
  `farbe` varchar(7) DEFAULT '#4F46E5',
  `aktiv` tinyint(1) DEFAULT 1,
  `scans` int(11) DEFAULT 0,
  `erstellt_am` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `aktualisiert_am` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `shortcode` (`shortcode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Migration: Farbe-Spalte zu bestehenden Installationen hinzufügen
-- ALTER TABLE `qr_codes` ADD COLUMN IF NOT EXISTS `farbe` varchar(7) DEFAULT '#4F46E5';

CREATE TABLE IF NOT EXISTS `qr_scans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `qr_code_id` int(11) NOT NULL,
  `scan_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `qr_code_id` (`qr_code_id`),
  CONSTRAINT `qr_scans_ibfk_1` FOREIGN KEY (`qr_code_id`) REFERENCES `qr_codes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `admin_settings` (
  `id` int(11) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Beispiel-Daten zum Testen
INSERT INTO `qr_codes` (`shortcode`, `ziel_url`, `titel`, `beschreibung`) VALUES
('demo', 'https://www.google.com', 'Demo QR Code', 'Zeigt auf Google - änderbar!'),
('test123', 'https://www.wikipedia.org', 'Test Code', 'Wikipedia Eintrag');

-- WICHTIG: Kein Standard-Passwort! 
-- Passwort wird beim ersten Setup über setup.php gesetzt.
