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
            $stmt = $pdo->prepare("SELECT * FROM transactions WHERE id = ?");
            $stmt->execute([$id]);
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .card { border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.15); }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-white">
                    <h4><i class="fas fa-edit"></i> Edit Transaksi</h4>
                </div>
                <div class="card-body p-4">

                    <?php if ($success): ?>
                        <div class="alert alert-success"><?= $success ?></div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label>Tipe Transaksi</label>
                                <select name="tipe" class="form-select" required>
                                    <option value="income" <?= $transaksi['tipe']=='income' ? 'selected' : '' ?>>Pemasukan</option>
                                    <option value="expense" <?= $transaksi['tipe']=='expense' ? 'selected' : '' ?>>Pengeluaran</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label>Tanggal</label>
                                <input type="date" name="tanggal" class="form-control" value="<?= $transaksi['tanggal'] ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label>Kategori</label>
                            <select name="kategori_id" class="form-select" required>
                                <?php foreach($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $transaksi['kategori_id'] ? 'selected' : '' ?>>
                                        <?= $cat['nama'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label>Jumlah (Rp)</label>
                            <input type="number" name="jumlah" class="form-control" value="<?= $transaksi['jumlah'] ?>" required>
                        </div>

                        <div class="mb-3">
                            <label>Keterangan</label>
                            <textarea name="keterangan" class="form-control" rows="3"><?= htmlspecialchars($transaksi['keterangan']) ?></textarea>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-warning btn-lg">Update Transaksi</button>
                            <a href="riwayat.php" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>