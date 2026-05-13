<?php
// ====================== CONFIG.PHP ======================

// Mulai Session
session_start();

// Konfigurasi Database
$host = 'localhost';
$db   = 'fintrack';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8mb4", 
        $user, 
        $pass
    );
    
    // Pengaturan PDO
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("❌ Koneksi Database Gagal: " . $e->getMessage());
}

// Fungsi Cek Login
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Fungsi Logout
function logout() {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}

// Set Timezone Indonesia (WITA - Bali)
date_default_timezone_set('Asia/Makassar');

// ====================== END CONFIG ======================