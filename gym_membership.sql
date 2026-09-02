-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 01 Sep 2026 pada 04.17
-- Versi server: 10.4.28-MariaDB
-- Versi PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gym_membership`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `attendances`
--

CREATE TABLE `attendances` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `member_id` bigint(20) UNSIGNED NOT NULL,
  `rfid_card_id` bigint(20) UNSIGNED DEFAULT NULL,
  `method` enum('rfid','manual') NOT NULL DEFAULT 'rfid',
  `check_in_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `attendances`
--

INSERT INTO `attendances` (`id`, `member_id`, `rfid_card_id`, `method`, `check_in_at`, `created_at`, `updated_at`) VALUES
(1, 3, 2, 'rfid', '2026-08-31 08:03:11', '2026-08-30 22:05:24', '2026-08-31 08:03:11'),
(2, 4, 3, 'rfid', '2026-08-31 08:14:03', '2026-08-30 22:05:36', '2026-08-31 08:14:03'),
(3, 5, 4, 'rfid', '2026-08-31 06:52:13', '2026-08-31 06:52:13', '2026-08-31 06:52:13'),
(4, 3, 2, 'rfid', '2026-08-31 08:14:09', '2026-08-31 08:14:09', '2026-08-31 08:14:09'),
(5, 4, 3, 'rfid', '2026-09-01 02:11:52', '2026-09-01 02:11:52', '2026-09-01 02:11:52'),
(6, 5, 4, 'rfid', '2026-09-01 02:14:20', '2026-09-01 02:14:20', '2026-09-01 02:14:20');

-- --------------------------------------------------------

--
-- Struktur dari tabel `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `members`
--

CREATE TABLE `members` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `membership_package_id` bigint(20) UNSIGNED DEFAULT NULL,
  `member_code` varchar(20) NOT NULL,
  `rfid_uid` varchar(50) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `gender` enum('L','P') DEFAULT NULL,
  `join_date` date NOT NULL,
  `expire_date` date DEFAULT NULL,
  `status` enum('active','inactive','expired') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `members`
--

INSERT INTO `members` (`id`, `user_id`, `membership_package_id`, `member_code`, `rfid_uid`, `photo`, `address`, `birth_date`, `gender`, `join_date`, `expire_date`, `status`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 'GYM-2608-0001', NULL, NULL, 'Lumin Pedalaman', '1995-10-13', 'L', '2026-08-29', '2026-10-29', 'active', '2026-08-28 21:53:53', '2026-08-30 22:24:58'),
(3, 5, 1, 'GYM-2608-0002', NULL, NULL, 'Dusun', '2001-01-01', 'L', '2026-08-31', '2026-10-01', 'active', '2026-08-30 20:45:50', '2026-08-30 20:45:50'),
(4, 6, 1, 'GYM-2608-0003', NULL, NULL, 'alahan', '2012-12-12', 'L', '2026-08-31', '2026-10-01', 'active', '2026-08-30 21:00:01', '2026-08-30 21:00:01'),
(5, 7, 1, 'GYM-2608-0004', NULL, NULL, 'siteba', '2002-05-12', 'L', '2026-08-31', '2026-10-01', 'active', '2026-08-31 06:52:04', '2026-08-31 06:52:04');

-- --------------------------------------------------------

--
-- Struktur dari tabel `membership_packages`
--

CREATE TABLE `membership_packages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `duration_months` int(10) UNSIGNED NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `membership_packages`
--

INSERT INTO `membership_packages` (`id`, `name`, `duration_months`, `price`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Paket Bulanan', 1, 3000000.00, 'Bisa jadi CEO', 1, '2026-08-28 21:52:53', '2026-08-28 21:52:53');

-- --------------------------------------------------------

--
-- Struktur dari tabel `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2024_01_01_000001_add_role_and_phone_to_users_table', 1),
(5, '2024_01_01_000002_create_membership_packages_table', 1),
(6, '2024_01_01_000003_create_members_table', 1),
(7, '2024_01_01_000004_create_rfid_cards_table', 1),
(8, '2024_01_01_000005_create_attendances_table', 1),
(9, '2024_01_01_000006_create_whatsapp_logs_table', 1),
(10, '2024_01_01_000007_create_payments_table', 1),
(11, '2026_08_29_034207_create_scan__u_i_d_s_table', 1),
(12, '2026_08_29_081107_create_scan_uids_table', 2),
(13, '2026_08_31_040957_add_rfid_uid_to_members_table', 3),
(14, '2026_08_31_145506_add_checkout_and_scan_uid_to_attendances_table', 3),
(15, '2026_08_31_151134_add_last_scan_at_to_attendances_table', 4);

-- --------------------------------------------------------

--
-- Struktur dari tabel `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `payments`
--

CREATE TABLE `payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `member_id` bigint(20) UNSIGNED NOT NULL,
  `membership_package_id` bigint(20) UNSIGNED DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `payment_date` date NOT NULL,
  `status` enum('paid','pending') NOT NULL DEFAULT 'paid',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `payments`
--

INSERT INTO `payments` (`id`, `member_id`, `membership_package_id`, `amount`, `payment_date`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 3000000.00, '2026-08-29', 'paid', '2026-08-28 21:53:53', '2026-08-28 21:53:53'),
(3, 3, 1, 3000000.00, '2026-08-31', 'paid', '2026-08-30 20:45:50', '2026-08-30 20:45:50'),
(4, 4, 1, 3000000.00, '2026-08-31', 'paid', '2026-08-30 21:00:01', '2026-08-30 21:00:01'),
(5, 1, 1, 3000000.00, '2026-08-31', 'paid', '2026-08-30 22:24:58', '2026-08-30 22:24:58'),
(6, 5, 1, 3000000.00, '2026-08-31', 'paid', '2026-08-31 06:52:04', '2026-08-31 06:52:04');

-- --------------------------------------------------------

--
-- Struktur dari tabel `rfid_cards`
--

CREATE TABLE `rfid_cards` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uid` varchar(50) NOT NULL,
  `member_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` enum('unassigned','assigned','blocked') NOT NULL DEFAULT 'unassigned',
  `assigned_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `rfid_cards`
--

INSERT INTO `rfid_cards` (`id`, `uid`, `member_id`, `status`, `assigned_at`, `created_at`, `updated_at`) VALUES
(1, 'A14321', 1, 'assigned', '2026-08-29 06:43:52', '2026-08-28 21:53:53', '2026-08-29 06:43:52'),
(2, 'FB:01:47:13', 3, 'assigned', '2026-08-30 20:45:50', '2026-08-30 20:45:50', '2026-08-30 20:45:50'),
(3, 'C1:B8:C8:A3', 4, 'assigned', '2026-08-30 21:00:01', '2026-08-30 21:00:01', '2026-08-30 21:00:01'),
(4, '3B:44:1A:13', 5, 'assigned', '2026-08-31 06:52:04', '2026-08-31 06:52:04', '2026-08-31 06:52:04');

-- --------------------------------------------------------

--
-- Struktur dari tabel `scan_uids`
--

CREATE TABLE `scan_uids` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uid` varchar(64) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `scan_uids`
--

INSERT INTO `scan_uids` (`id`, `uid`, `created_at`, `updated_at`) VALUES
(1, '3B:44:1A:13', '2026-08-30 19:33:49', '2026-09-01 02:14:20');

-- --------------------------------------------------------

--
-- Struktur dari tabel `scan__u_i_d_s`
--

CREATE TABLE `scan__u_i_d_s` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uid` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `scan__u_i_d_s`
--

INSERT INTO `scan__u_i_d_s` (`id`, `uid`, `created_at`, `updated_at`) VALUES
(1, 'abcdefg', '2026-08-28 21:07:05', '2026-08-28 21:07:05');

-- --------------------------------------------------------

--
-- Struktur dari tabel `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('wcp8TrXYhjZZ4X9lHNMlhQvtWUk8k09pNOf66xZ0', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 Edg/152.0.0.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoibVBVSXFYYTNpdmVjOFpuMFdndzN4UlRqaERrN01sNmRueklnZXFodCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NTc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hZG1pbi9kYXNoYm9hcmQvbGF0ZXN0LXJmaWQtY2hlY2tpbiI7czo1OiJyb3V0ZSI7czozNToiYWRtaW4uZGFzaGJvYXJkLmxhdGVzdC1yZmlkLWNoZWNraW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=', 1788229030);

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `role` enum('admin','member') NOT NULL DEFAULT 'member',
  `phone` varchar(20) DEFAULT NULL,
  `must_change_password` tinyint(1) NOT NULL DEFAULT 0,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `role`, `phone`, `must_change_password`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'admin@test.com', 'admin', '6289524254219', 0, NULL, '$2y$12$wwp4M4/Gdd.UvVGv38moM.F5JV/Q29UXzKBgDj5HcMXWn5TAa3zRG', NULL, NULL, '2026-08-28 21:46:49'),
(2, 'Ayu', 'ayuayuan@gmail.com', 'member', '083848481212', 0, NULL, '$2y$12$I52YNf1ppaliarc/qHZnduah2grKNqiPSLtm6GHWa/g5ezx.08mcu', NULL, '2026-08-28 21:53:53', '2026-08-29 06:46:46'),
(3, 'Ayuni', 'ayuni@gym.test', 'admin', NULL, 0, NULL, '$2y$12$Zo0oh.EYZgk1/L6x28BAce9BkdNKqGd4EETHuDMhfsA1oSaDm7A9S', NULL, '2026-08-29 20:14:02', '2026-08-29 20:14:02'),
(5, 'II', 'iiaja@gmail.com', 'member', '086634286453', 0, NULL, '$2y$12$KiQzLaPOv.pcP.4J/EGepuDt.8PQwh1gzkQb.Wq053m0jcL22pvdm', NULL, '2026-08-30 20:45:50', '2026-08-30 22:33:10'),
(6, 'jamil', 'jamil@gmail.com', 'member', '086327615812', 1, NULL, '$2y$12$hFTKeKp/DVap149tL7M4VeNxKdBjExyvq36O5g9/TOoqw5kh83vT6', NULL, '2026-08-30 21:00:01', '2026-08-30 21:00:01'),
(7, 'ridho', 'ridho@gmail.com', 'member', '087382467283', 1, NULL, '$2y$12$i6MO3XcJfQtnNnjSnf1Pa.TRQD0WiYGKl7u7WqKQ2rXFBYhbgRfRe', NULL, '2026-08-31 06:52:04', '2026-08-31 06:52:04');

-- --------------------------------------------------------

--
-- Struktur dari tabel `whatsapp_logs`
--

CREATE TABLE `whatsapp_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `member_id` bigint(20) UNSIGNED DEFAULT NULL,
  `phone` varchar(20) NOT NULL,
  `type` varchar(40) NOT NULL DEFAULT 'registration',
  `message` text NOT NULL,
  `status` enum('sent','failed') NOT NULL DEFAULT 'sent',
  `response` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `whatsapp_logs`
--

INSERT INTO `whatsapp_logs` (`id`, `member_id`, `phone`, `type`, `message`, `status`, `response`, `created_at`, `updated_at`) VALUES
(1, 1, '6283848481212', 'registration', 'Halo *Ayu* 👋\n\nPendaftaran membership GYM kamu berhasil! 🎉\n\nKode Member: *GYM-2608-0001*\nPaket: *Paket Bulanan*\nAktif sampai: *29 Sep 2026*\n\nTunjukkan kartu RFID kamu di pintu masuk untuk check-in. Sampai jumpa di gym! 💪', 'sent', '{\"reason\":\"request invalid on disconnected device\",\"requestid\":677403397,\"status\":false}', '2026-08-28 21:53:53', '2026-08-28 21:53:53'),
(2, 3, '6286634286453', 'registration', 'Halo *II* 👋\n\nPendaftaran membership GYM kamu berhasil! 🎉\n\nKode Member: *GYM-2608-0002*\nPaket: *Paket Bulanan*\nAktif sampai: *01 Okt 2026*\n\nTunjukkan kartu RFID kamu di pintu masuk untuk check-in. Sampai jumpa di gym! 💪', 'failed', 'cURL error 60: SSL certificate OpenSSL verify result: unable to get local issuer certificate (20) (see https://curl.se/libcurl/c/libcurl-errors.html) for https://api.fonnte.com/send', '2026-08-30 20:45:51', '2026-08-30 20:45:51'),
(3, 4, '6286327615812', 'registration', 'Halo *jamil* 👋\n\nPendaftaran membership GYM kamu berhasil! 🎉\n\nKode Member: *GYM-2608-0003*\nPaket: *Paket Bulanan*\nAktif sampai: *01 Okt 2026*\n\nTunjukkan kartu RFID kamu di pintu masuk untuk check-in. Sampai jumpa di gym! 💪', 'failed', 'cURL error 60: SSL certificate OpenSSL verify result: unable to get local issuer certificate (20) (see https://curl.se/libcurl/c/libcurl-errors.html) for https://api.fonnte.com/send', '2026-08-30 21:00:02', '2026-08-30 21:00:02'),
(4, 5, '6287382467283', 'registration', 'Halo *ridho* 👋\n\nPendaftaran membership GYM kamu berhasil! 🎉\n\nKode Member: *GYM-2608-0004*\nPaket: *Paket Bulanan*\nAktif sampai: *01 Okt 2026*\n\nTunjukkan kartu RFID kamu di pintu masuk untuk check-in. Sampai jumpa di gym! 💪', 'failed', 'cURL error 60: SSL certificate OpenSSL verify result: unable to get local issuer certificate (20) (see https://curl.se/libcurl/c/libcurl-errors.html) for https://api.fonnte.com/send', '2026-08-31 06:52:08', '2026-08-31 06:52:08');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `attendances`
--
ALTER TABLE `attendances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attendances_member_id_foreign` (`member_id`),
  ADD KEY `attendances_rfid_card_id_foreign` (`rfid_card_id`);

--
-- Indeks untuk tabel `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indeks untuk tabel `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indeks untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indeks untuk tabel `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indeks untuk tabel `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `members_member_code_unique` (`member_code`),
  ADD UNIQUE KEY `members_rfid_uid_unique` (`rfid_uid`),
  ADD KEY `members_user_id_foreign` (`user_id`),
  ADD KEY `members_membership_package_id_foreign` (`membership_package_id`);

--
-- Indeks untuk tabel `membership_packages`
--
ALTER TABLE `membership_packages`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indeks untuk tabel `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payments_member_id_foreign` (`member_id`),
  ADD KEY `payments_membership_package_id_foreign` (`membership_package_id`);

--
-- Indeks untuk tabel `rfid_cards`
--
ALTER TABLE `rfid_cards`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rfid_cards_uid_unique` (`uid`),
  ADD UNIQUE KEY `rfid_cards_member_id_unique` (`member_id`);

--
-- Indeks untuk tabel `scan_uids`
--
ALTER TABLE `scan_uids`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `scan_uids_uid_unique` (`uid`);

--
-- Indeks untuk tabel `scan__u_i_d_s`
--
ALTER TABLE `scan__u_i_d_s`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `scan__u_i_d_s_uid_unique` (`uid`);

--
-- Indeks untuk tabel `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indeks untuk tabel `whatsapp_logs`
--
ALTER TABLE `whatsapp_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `whatsapp_logs_member_id_foreign` (`member_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `attendances`
--
ALTER TABLE `attendances`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `members`
--
ALTER TABLE `members`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `membership_packages`
--
ALTER TABLE `membership_packages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT untuk tabel `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `rfid_cards`
--
ALTER TABLE `rfid_cards`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `scan_uids`
--
ALTER TABLE `scan_uids`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `scan__u_i_d_s`
--
ALTER TABLE `scan__u_i_d_s`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `whatsapp_logs`
--
ALTER TABLE `whatsapp_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `attendances`
--
ALTER TABLE `attendances`
  ADD CONSTRAINT `attendances_member_id_foreign` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendances_rfid_card_id_foreign` FOREIGN KEY (`rfid_card_id`) REFERENCES `rfid_cards` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `members`
--
ALTER TABLE `members`
  ADD CONSTRAINT `members_membership_package_id_foreign` FOREIGN KEY (`membership_package_id`) REFERENCES `membership_packages` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `members_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_member_id_foreign` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_membership_package_id_foreign` FOREIGN KEY (`membership_package_id`) REFERENCES `membership_packages` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `rfid_cards`
--
ALTER TABLE `rfid_cards`
  ADD CONSTRAINT `rfid_cards_member_id_foreign` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `whatsapp_logs`
--
ALTER TABLE `whatsapp_logs`
  ADD CONSTRAINT `whatsapp_logs_member_id_foreign` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
