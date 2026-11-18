<?php
include '../config.php';
// JANGAN buat function rupiah() lagi di sini, karena sudah ada di config.php

if (isset($_GET['hapus'])) {
  $id = (int)$_GET['hapus'];
  // Hapus foto dulu biar tidak numpuk di folder uploads/
  $cek = mysqli_fetch_assoc(mysqli_query($conn, "SELECT foto FROM produk WHERE id='$id'"));
  if ($cek && !empty($cek['foto']) && file_exists('../uploads/' . $cek['foto'])) {
      unlink('../uploads/' . $cek['foto']);
  }
  mysqli_query($conn, "DELETE FROM produk WHERE id='$id'");
  header("Location: produk.php");
  exit;
}

$total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as j FROM produk"))['j'];
?>
<!DOCTYPE html>
<html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kelola Produk - PasarKita Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
  body{ background:#FFF8E7; font-family:'Segoe UI',sans-serif; }
  .navbar-modern{ background:linear-gradient(90deg,#6B0F0F,#8B1E1E,#A52A2A) !important; border-bottom:4px solid #C9A227; padding:16px 0; }
  .navbar-modern .navbar-brand{ color:#fff !important; font-weight:800; font-size:22px; }
  .navbar-modern .navbar-brand span{ color:#F2D57E; }
  .hero-admin{ background:linear-gradient(135deg,#6B0F0F,#8B1E1E,#B8860B); color:white; border-radius:20px; padding:24px 28px; border:2px solid #C9A227; }
  .hero-admin h2{ font-weight:900; margin:0; }
  .btn-bulat{ border-radius:50px; font-weight:700; padding:9px 20px; text-decoration:none; display:inline-block; border:none; }
  .btn-merah{ background:#8B1E1E; color:white; } .btn-merah:hover{ background:#6B0F0F; color:white; }
  .btn-emas{ background:#C9A227; color:white; } .btn-emas:hover{ background:#B8860B; color:white; }
  .btn-kembali{ background:white; color:#6B0F0F; border:1.5px solid #C9A227; } .btn-kembali:hover{ background:#FFF3CD; color:#6B0F0F; }
  .btn-tambah{ background:#0F9D58; color:white; } .btn-tambah:hover{ background:#0B7A44; color:white; }
  .card-modern{ border:none; border-radius:20px; background:white; box-shadow:0 10px 30px rgba(0,0,0,.08); overflow:hidden; border-top:6px solid #C9A227; }
  .judul{ font-weight:800; color:#6B0F0F; border-left:6px solid #C9A227; padding-left:12px; }
  table thead th{ background:#6B0F0F !important; color:#F2D57E !important; border:none; }
  .badge-kat{ background:#FFF3CD; color:#8B1E1E; padding:5px 10px; border-radius:20px; font-weight:700; font-size:12px; border:1px solid #C9A227; }
  .harga{ color:#8B1E1E; font-weight:800; }
  .foto-thumb{ width:55px; height:55px; object-fit:cover; border-radius:10px; }
</style>
</head>
<body>
<nav class="navbar navbar-dark navbar-modern"><div class="container">
<a class="navbar-brand" href="../index.php">🥬 Pasar<span>Kita</span> <small style="font-size:12px;color:#F2D57E;">Admin</small></a>
<div class="d-flex gap-2">
  <a href="pesanan.php" class="btn btn-warning btn-bulat btn-sm fw-bold">🧾 Riwayat Pesanan</a>
  <a href="../index.php" class="btn btn-light btn-bulat">🏠 Lihat Toko</a>
</div>
</div></nav>

<div class="container mt-4">
  <div class="d-flex gap-2 flex-wrap">
    <a href="dashboard.php" class="btn btn-kembali btn-bulat">← Kembali ke Dashboard</a>
    <a href="../index.php" class="btn btn-kembali btn-bulat">🏠 Kembali ke Toko</a>
    <a href="pesanan.php" class="btn btn-bulat" style="background:#6B0F0F; color:white;">🧾 Riwayat Pesanan</a>
  </div>

  <div class="hero-admin mt-4"><h2>⚙️ Kelola Produk</h2><p>Total: <?= $total ?> Produk • Pempek • Tekwan • Kemplang • Songket • Kain Jumputan</p></div>

  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-4 mb-3">
    <h4 class="judul mb-0">📋 Daftar Produk</h4>
    <div class="d-flex gap-2"><a href="tambah_produk.php" class="btn btn-tambah btn-bulat">+ Tambah Produk</a><a href="../index.php" class="btn btn-kembali btn-bulat">← Kembali</a></div>
  </div>

  <div class="card card-modern"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
    <thead><tr><th>ID</th><th>Foto</th><th>Nama</th><th>Kategori</th><th>Harga</th><th>Stok</th><th>Aksi</th></tr></thead>
    <tbody>
    <?php
    $q = mysqli_query($conn, "SELECT p.*, k.nama_kategori FROM produk p LEFT JOIN kategori k ON p.kategori_id = k.id ORDER BY p.id DESC");
    if (mysqli_num_rows($q) == 0) {
        echo '<tr><td colspan="7" class="text-center py-4">Belum ada produk. Klik <b>+ Tambah Produk</b></td></tr>';
    }
    while ($p = mysqli_fetch_assoc($q)) {
      $file_foto = '../uploads/' . $p['foto'];
      $src_foto  = '../uploads/' . rawurlencode($p['foto']);
    ?>
    <tr>
      <td><b>#<?= $p['id'] ?></b></td>
      <td>
        <?php if (!empty($p['foto']) && file_exists($file_foto)): ?>
          <img src="<?= $src_foto ?>" class="foto-thumb">
        <?php else: ?>
          <div class="foto-thumb bg-light d-flex align-items-center justify-content-center">🍲</div>
        <?php endif; ?>
      </td>
      <td><b><?= htmlspecialchars($p['nama_produk']) ?></b></td>
      <td><span class="badge-kat"><?= htmlspecialchars($p['nama_kategori'] ?? '-') ?></span></td>
      <td class="harga"><?= rupiah($p['harga']) ?></td>
      <td><?= $p['stok'] ?> pcs</td>
      <td>
        <a href="edit_produk.php?id=<?= $p['id'] ?>" class="btn btn-emas btn-bulat btn-sm">Edit</a>
        <a href="produk.php?hapus=<?= $p['id'] ?>" onclick="return confirm('Yakin hapus <?= htmlspecialchars($p['nama_produk']) ?>?')" class="btn btn-danger btn-bulat btn-sm">Hapus</a>
      </td>
    </tr>
    <?php } ?>
    </tbody></table></div></div>

  <a href="../index.php" class="btn btn-merah btn-bulat w-100 mt-4 py-2">← Kembali ke Dashboard</a>
</div>
<footer class="text-center py-3 mt-4" style="background:#6B0F0F; color:#F2D57E;"><small>© 2026 PasarKita • Palembang</small></footer>
</body></html>