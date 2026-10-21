CREATE DATABASE IF NOT EXISTS `db_users`;
USE `db_users`;

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nama` VARCHAR(100) NOT NULL,
    `tanggal_lahir` DATE NOT NULL,
    `usia` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data Dummy
INSERT INTO `users` (`nama`, `tanggal_lahir`, `usia`) VALUES
('Budi Santoso', '1995-05-14', 31),
('Siti Rahmawati', '1998-08-22', 28),
('Ahmad Dhani', '1990-12-01', 35),
('Dewi Lestari', '2001-03-10', 25),
('Rian Ekky', '1992-11-18', 33),
('Andi Wijaya', '1997-07-04', 29),
('Fitri Ani', '2003-01-25', 23);