<?php
require 'config.php';
if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Ambil daftar kategori
$stmt = $pdo->prepare("SELECT * FROM categories ORDER BY tipe, nama");
$stmt->execute();
$categories = $stmt->fetchAll();

// Proses tambah transaksi
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kategori_id = $_POST['kategori_id'];
    $jumlah      = str_replace(',', '', $_POST['jumlah']);
    $keterangan  = trim($_POST['keterangan']);
    $tanggal     = $_POST['tanggal'];
    $tipe        = $_POST['tipe'];

    if ($jumlah > 0) {
        $stmt = $pdo->prepare("INSERT INTO transactions 
            (user_id, kategori_id, jumlah, keterangan, tanggal, tipe) 
            VALUES (?, ?, ?, ?, ?, ?)");
        
        if ($stmt->execute([$user_id, $kategori_id, $jumlah, $keterangan, $tanggal, $tipe])) {
            $success = "Transaksi berhasil ditambahkan!";
        } else {
            $error = "Gagal menambahkan transaksi.";
        }
    } else {
        $error = "Jumlah harus lebih dari 0!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Transaksi - FinTrack</title>
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
        .form-control, .form-select {
            padding: 14px 16px;
            font-size: 1.05rem;
            border-radius: 12px;
        }
        .btn-tipe {
            padding: 14px 0;
            font-size: 1.1rem;
            font-weight: 600;
        }
        .input-group-text {
            font-size: 1.1rem;
        }
    </style>
</head>
<body>
<div class="container py-4">

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8 col-xl-6">

            <div class="card">
                <div class="card-header bg-white py-3">
                    <h4 class="mb-0"><i class="fas fa-plus-circle"></i> Tambah Transaksi Baru</h4>
                </div>
                
                <div class="card-body p-4">

                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <?= $success ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <?= $error ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" id="transaksiForm">
                        <!-- Tipe Transaksi -->
                        <div class="mb-4">
                            <label class="form-label fw-medium">Tipe Transaksi</label>
                            <input type="hidden" name="tipe" id="tipeInput" value="expense">
                            
                            <div class="btn-group w-100" role="group">
                                <button type="button" id="btn-income" 
                                        class="btn btn-success btn-tipe" 
                                        onclick="setTipe('income')">
                                    <i class="fas fa-arrow-up"></i> Pemasukan
                                </button>
                                <button type="button" id="btn-expense" 
                                        class="btn btn-danger btn-tipe active" 
                                        onclick="setTipe('expense')">
                                    <i class="fas fa-arrow-down"></i> Pengeluaran
                                </button>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12 col-sm-6 mb-3 mb-sm-0">
                                <label class="form-label">Tanggal</label>
                                <input type="date" name="tanggal" class="form-control" 
                                       value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-12 col-sm-6">
                                <label class="form-label">Kategori</label>
                                <select name="kategori_id" class="form-select" required>
                                    <option value="">Pilih Kategori</option>
                                    <?php foreach($categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>">
                                            <?= htmlspecialchars($cat['nama']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Jumlah (Rp)</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="jumlah" class="form-control" 
                                       placeholder="0" required min="1" step="1">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Keterangan</label>
                            <textarea name="keterangan" class="form-control" rows="3" 
                                      placeholder="Contoh: Gaji bulan Mei, Beli bensin, dll"></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100 py-3">
                            <i class="fas fa-save"></i> Simpan Transaksi
                        </button>
                    </form>

                    <div class="text-center mt-4">
                        <a href="dashboard.php" class="btn btn-secondary w-100">
                            ← Kembali ke Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function setTipe(tipe) {
    document.getElementById('tipeInput').value = tipe;
    
    // Ubah tampilan tombol
    document.getElementById('btn-income').classList.toggle('active', tipe === 'income');
    document.getElementById('btn-expense').classList.toggle('active', tipe === 'expense');
}

// Set default ke Pengeluaran
setTipe('expense');
</script>
</body>
</html>