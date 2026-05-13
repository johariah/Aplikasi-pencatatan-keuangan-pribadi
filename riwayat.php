<?php
require 'config.php';
if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Proses Hapus
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $stmt = $pdo->prepare("DELETE FROM transactions WHERE id = ? AND user_id = ?");
    if ($stmt->execute([$id, $user_id])) {
        $success = "Transaksi berhasil dihapus!";
    }
}

// Ambil semua transaksi
$stmt = $pdo->prepare("SELECT t.*, c.nama as kategori_nama 
                      FROM transactions t 
                      JOIN categories c ON t.kategori_id = c.id 
                      WHERE t.user_id = ? 
                      ORDER BY t.tanggal DESC, t.created_at DESC");
$stmt->execute([$user_id]);
$transactions = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Transaksi - FinTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-size: 16px;
        }
        .card {
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        .table th, .table td {
            vertical-align: middle;
        }
        .amount {
            font-weight: 600;
        }
        .btn-action {
            padding: 6px 10px;
            font-size: 1.1rem;
        }
        @media (max-width: 768px) {
            .table-responsive {
                font-size: 0.95rem;
            }
            .btn-action {
                padding: 8px 12px;
            }
            h4 {
                font-size: 1.4rem;
            }
        }
    </style>
</head>
<body>
<div class="container py-4">

    <!-- Card Utama -->
    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="fas fa-history"></i> Riwayat Transaksi</h4>
            <a href="dashboard.php" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-arrow-left"></i> Dashboard
            </a>
        </div>
        
        <div class="card-body">

            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $success ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (empty($transactions)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-receipt fa-4x text-muted mb-3"></i>
                    <h5>Belum ada transaksi</h5>
                    <p class="text-muted">Mulai catat keuanganmu sekarang</p>
                    <a href="transaksi.php" class="btn btn-success btn-lg mt-3">
                        <i class="fas fa-plus-circle"></i> Tambah Transaksi Pertama
                    </a>
                </div>
            <?php else: ?>
                
                <!-- Tombol Tambah Floating (khusus mobile) -->
                <div class="d-lg-none mb-3">
                    <a href="transaksi.php" class="btn btn-success btn-lg w-100">
                        <i class="fas fa-plus-circle"></i> Tambah Transaksi
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Tanggal</th>
                                <th>Kategori</th>
                                <th>Keterangan</th>
                                <th class="text-end">Jumlah</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($transactions as $t): ?>
                            <tr>
                                <td><?= date('d M Y', strtotime($t['tanggal'])) ?></td>
                                <td>
                                    <span class="badge <?= $t['tipe']=='income' ? 'bg-success' : 'bg-danger' ?>">
                                        <?= htmlspecialchars($t['kategori_nama']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($t['keterangan'] ?: '-') ?></td>
                                <td class="text-end amount <?= $t['tipe']=='income' ? 'text-success' : 'text-danger' ?>">
                                    <?= $t['tipe']=='income' ? '+' : '-' ?> Rp <?= number_format($t['jumlah']) ?>
                                </td>
                                <td class="text-center">
                                    <a href="edit_transaksi.php?id=<?= $t['id'] ?>" 
                                       class="btn btn-sm btn-warning btn-action">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="riwayat.php?hapus=<?= $t['id'] ?>" 
                                       class="btn btn-sm btn-danger btn-action"
                                       onclick="return confirm('Yakin ingin menghapus transaksi ini?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>