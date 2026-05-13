<?php
require 'config.php';
if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$id = $_GET['id'] ?? 0;

$success = '';
$error = '';

// Ambil data transaksi yang akan diedit
$stmt = $pdo->prepare("SELECT * FROM transactions WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $user_id]);
$transaksi = $stmt->fetch();

if (!$transaksi) {
    header("Location: riwayat.php");
    exit;
}

// Ambil kategori
$stmt = $pdo->prepare("SELECT * FROM categories ORDER BY tipe, nama");
$stmt->execute();
$categories = $stmt->fetchAll();

// Proses Update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kategori_id = $_POST['kategori_id'];
    $jumlah      = str_replace(',', '', $_POST['jumlah']);
    $keterangan  = trim($_POST['keterangan']);
    $tanggal     = $_POST['tanggal'];
    $tipe        = $_POST['tipe'];

    if ($jumlah > 0) {
        $stmt = $pdo->prepare("UPDATE transactions SET 
            kategori_id = ?, 
            jumlah = ?, 
            keterangan = ?, 
            tanggal = ?, 
            tipe = ? 
            WHERE id = ? AND user_id = ?");
        
        if ($stmt->execute([$kategori_id, $jumlah, $keterangan, $tanggal, $tipe, $id, $user_id])) {
            $success = "Transaksi berhasil diupdate!";
            // Refresh data
            $stmt = $pdo->prepare("SELECT * FROM transactions WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $user_id]);
            $transaksi = $stmt->fetch();
        } else {
            $error = "Gagal mengupdate transaksi.";
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
    <title>Edit Transaksi - FinTrack</title>
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
    </style>
</head>
<body>
<div class="container py-4">

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8 col-xl-6">

            <div class="card">
                <div class="card-header bg-white py-3">
                    <h4 class="mb-0"><i class="fas fa-edit"></i> Edit Transaksi</h4>
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

                    <form method="POST">
                        <!-- Tipe Transaksi -->
                        <div class="mb-4">
                            <label class="form-label fw-medium">Tipe Transaksi</label>
                            <input type="hidden" name="tipe" id="tipeInput" value="<?= $transaksi['tipe'] ?>">
                            
                            <div class="btn-group w-100" role="group">
                                <button type="button" id="btn-income" 
                                        class="btn btn-success btn-tipe <?= $transaksi['tipe']=='income' ? 'active' : '' ?>" 
                                        onclick="setTipe('income')">
                                    <i class="fas fa-arrow-up"></i> Pemasukan
                                </button>
                                <button type="button" id="btn-expense" 
                                        class="btn btn-danger btn-tipe <?= $transaksi['tipe']=='expense' ? 'active' : '' ?>" 
                                        onclick="setTipe('expense')">
                                    <i class="fas fa-arrow-down"></i> Pengeluaran
                                </button>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12 col-sm-6 mb-3 mb-sm-0">
                                <label class="form-label">Tanggal</label>
                                <input type="date" name="tanggal" class="form-control" 
                                       value="<?= $transaksi['tanggal'] ?>" required>
                            </div>
                            <div class="col-12 col-sm-6">
                                <label class="form-label">Kategori</label>
                                <select name="kategori_id" class="form-select" required>
                                    <?php foreach($categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>" 
                                            <?= $cat['id'] == $transaksi['kategori_id'] ? 'selected' : '' ?>>
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
                                       value="<?= $transaksi['jumlah'] ?>" required min="1" step="1">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Keterangan</label>
                            <textarea name="keterangan" class="form-control" rows="3" 
                                      placeholder="Contoh: Gaji bulan Mei"><?= htmlspecialchars($transaksi['keterangan'] ?? '') ?></textarea>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-warning btn-lg py-3">
                                <i class="fas fa-save"></i> Update Transaksi
                            </button>
                            <a href="riwayat.php" class="btn btn-secondary btn-lg">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function setTipe(tipe) {
    document.getElementById('tipeInput').value = tipe;
    
    document.getElementById('btn-income').classList.toggle('active', tipe === 'income');
    document.getElementById('btn-expense').classList.toggle('active', tipe === 'expense');
}
</script>
</body>
</html>