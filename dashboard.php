<?php
require 'config.php';
if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil ringkasan keuangan
$stmt = $pdo->prepare("SELECT 
    SUM(CASE WHEN tipe='income' THEN jumlah ELSE 0 END) as total_income,
    SUM(CASE WHEN tipe='expense' THEN jumlah ELSE 0 END) as total_expense 
    FROM transactions WHERE user_id = ?");
$stmt->execute([$user_id]);
$summary = $stmt->fetch();

$total_income = $summary['total_income'] ?? 0;
$total_expense = $summary['total_expense'] ?? 0;
$balance = $total_income - $total_expense;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - FinTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        .navbar {
            background-color: rgba(0, 0, 0, 0.8) !important;
        }
    </style>
</head>
<body>
<div class="container py-5">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark rounded-3 mb-4">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="dashboard.php">
                <i class="fas fa-wallet"></i> FinTrack
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <div class="navbar-nav ms-auto">
                    <a class="nav-link active" href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
                    <a class="nav-link" href="transaksi.php"><i class="fas fa-plus-circle"></i> Tambah</a>
                    <a class="nav-link" href="riwayat.php"><i class="fas fa-history"></i> Riwayat</a>
                    <a class="nav-link" href="laporan.php"><i class="fas fa-chart-bar"></i> Laporan</a>
                    <a class="nav-link text-danger" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Header Selamat Datang -->
    <div class="text-center mb-5 text-white">
        <h1>Selamat datang, <?= htmlspecialchars($_SESSION['nama'] ?? 'User') ?> 👋</h1>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card text-white bg-primary">
                <div class="card-body text-center">
                    <h5>Saldo Saat Ini</h5>
                    <h3>Rp <?= number_format($balance) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-success">
                <div class="card-body text-center">
                    <h5>Total Pemasukan</h5>
                    <h3>Rp <?= number_format($total_income) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-danger">
                <div class="card-body text-center">
                    <h5>Total Pengeluaran</h5>
                    <h3>Rp <?= number_format($total_expense) ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5>Grafik Keuangan</h5>
                </div>
                <div class="card-body">
                    <canvas id="financeChart" height="130"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <a href="transaksi.php" class="btn btn-success btn-lg w-100 mb-3 py-3">
                <i class="fas fa-plus-circle"></i><br>Tambah Transaksi
            </a>
            <a href="riwayat.php" class="btn btn-info btn-lg w-100 mb-3 py-3 text-white">
                <i class="fas fa-history"></i><br>Lihat Riwayat
            </a>
            <a href="laporan.php" class="btn btn-warning btn-lg w-100 py-3 text-dark">
                <i class="fas fa-chart-bar"></i><br>Laporan Bulanan
            </a>
        </div>
    </div>

</div>

<script>
new Chart(document.getElementById('financeChart'), {
    type: 'doughnut',
    data: {
        labels: ['Pemasukan', 'Pengeluaran'],
        datasets: [{
            data: [<?= $total_income ?>, <?= $total_expense ?>],
            backgroundColor: ['#28a745', '#dc3545']
        }]
    },
    options: { responsive: true }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>