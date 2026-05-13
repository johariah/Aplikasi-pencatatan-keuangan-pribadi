<?php
require 'config.php';
if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Filter bulan (default bulan ini)
$bulan = $_GET['bulan'] ?? date('Y-m');

// Ambil data laporan
$stmt = $pdo->prepare("SELECT 
    SUM(CASE WHEN tipe='income' THEN jumlah ELSE 0 END) as total_income,
    SUM(CASE WHEN tipe='expense' THEN jumlah ELSE 0 END) as total_expense,
    COUNT(*) as total_transaksi
    FROM transactions 
    WHERE user_id = ? AND DATE_FORMAT(tanggal, '%Y-%m') = ?");
$stmt->execute([$user_id, $bulan]);
$laporan = $stmt->fetch();

$total_income = $laporan['total_income'] ?? 0;
$total_expense = $laporan['total_expense'] ?? 0;
$balance = $total_income - $total_expense;

// Ambil transaksi bulan ini
$stmt = $pdo->prepare("SELECT t.*, c.nama as kategori_nama 
                      FROM transactions t 
                      JOIN categories c ON t.kategori_id = c.id 
                      WHERE t.user_id = ? AND DATE_FORMAT(t.tanggal, '%Y-%m') = ?
                      ORDER BY t.tanggal DESC");
$stmt->execute([$user_id, $bulan]);
$transactions = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Bulanan - FinTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-size: 16px;
        }
        .card {
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .stat-card {
            transition: transform 0.2s;
        }
        .table th, .table td {
            vertical-align: middle;
        }
        @media (max-width: 768px) {
            .table-responsive {
                font-size: 0.95rem;
            }
        }
    </style>
</head>
<body>
<div class="container py-4">

    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h4 class="mb-0"><i class="fas fa-chart-bar"></i> Laporan Bulanan</h4>
            <a href="dashboard.php" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left"></i> Dashboard
            </a>
        </div>
        
        <div class="card-body p-4">

            <!-- Filter Bulan -->
            <form method="GET" class="mb-4">
                <div class="row">
                    <div class="col-12">
                        <select name="bulan" class="form-select form-select-lg" onchange="this.form.submit()">
                            <?php 
                            $currentYear = date('Y');
                            for($m=1; $m<=12; $m++): 
                                $value = $currentYear . '-' . str_pad($m, 2, '0', STR_PAD_LEFT);
                            ?>
                                <option value="<?= $value ?>" <?= $value == $bulan ? 'selected' : '' ?>>
                                    <?= date('F Y', strtotime($value)) ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
            </form>

            <!-- Statistik -->
            <div class="row g-3 mb-4">
                <div class="col-12 col-md-4">
                    <div class="card text-white bg-success stat-card h-100">
                        <div class="card-body text-center py-4">
                            <h6>Pemasukan</h6>
                            <h3 class="mb-0">Rp <?= number_format($total_income) ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="card text-white bg-danger stat-card h-100">
                        <div class="card-body text-center py-4">
                            <h6>Pengeluaran</h6>
                            <h3 class="mb-0">Rp <?= number_format($total_expense) ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="card text-white <?= $balance >= 0 ? 'bg-primary' : 'bg-warning' ?> stat-card h-100">
                        <div class="card-body text-center py-4">
                            <h6>Saldo Bulan Ini</h6>
                            <h3 class="mb-0">Rp <?= number_format($balance) ?></h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detail Transaksi -->
            <?php if (!empty($transactions)): ?>
                <h5 class="mb-3">Detail Transaksi (<?= count($transactions) ?>)</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Tanggal</th>
                                <th>Kategori</th>
                                <th>Keterangan</th>
                                <th class="text-end">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($transactions as $t): ?>
                            <tr>
                                <td><?= date('d M', strtotime($t['tanggal'])) ?></td>
                                <td>
                                    <span class="badge <?= $t['tipe']=='income' ? 'bg-success' : 'bg-danger' ?>">
                                        <?= htmlspecialchars($t['kategori_nama']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($t['keterangan'] ?: '-') ?></td>
                                <td class="text-end fw-bold <?= $t['tipe']=='income' ? 'text-success' : 'text-danger' ?>">
                                    <?= $t['tipe']=='income' ? '+' : '-' ?> Rp <?= number_format($t['jumlah']) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-receipt fa-4x text-muted mb-3"></i>
                    <h5>Tidak ada transaksi di bulan ini</h5>
                    <a href="transaksi.php" class="btn btn-success mt-3">Tambah Transaksi</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>