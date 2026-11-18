<?php
include '../config.php';

// --- UBAH STATUS ---
if (isset($_POST['ubah_status'])) {
    $id_pesanan = (int)$_POST['pesanan_id'];
    $status_baru = mysqli_real_escape_string($conn, $_POST['status']);
    mysqli_query($conn, "UPDATE pesanan SET status='$status_baru' WHERE id=$id_pesanan");
    header("Location: pesanan.php?status_filter=" . urlencode($_GET['status_filter'] ?? ''));
    exit;
}

// --- FILTER ---
$filter = $_GET['status_filter'] ?? '';
$where = "";
if ($filter != '' && $filter != 'Semua') {
    $where = "WHERE p.status = '" . mysqli_real_escape_string($conn, $filter) . "'";
}

// --- STATISTIK ATAS ---
$stat_total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as jml, COALESCE(SUM(total),0) as omset FROM pesanan")) ;
$stat_menunggu = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as jml FROM pesanan WHERE status='Menunggu Pembayaran'"));
$stat_diproses = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as jml FROM pesanan WHERE status='Diproses'"));
$stat_selesai = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as jml FROM pesanan WHERE status='Selesai'"));

// --- AMBIL PESANAN ---
$q = mysqli_query($conn, "
    SELECT p.*, 
    (SELECT COUNT(*) FROM pesanan_detail pd WHERE pd.pesanan_id = p.id) as jml_item,
    (SELECT GROUP_CONCAT(CONCAT(pr.nama_produk, ' (', pd.jumlah, 'x)') SEPARATOR ', ') 
     FROM pesanan_detail pd JOIN produk pr ON pd.produk_id = pr.id 
     WHERE pd.pesanan_id = p.id) as isi_pesanan
    FROM pesanan p
    $where
    ORDER BY p.id DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Riwayat Pesanan - PasarKita Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
  body { background: #FFF8E7; font-family: 'Segoe UI', sans-serif; }
  .header { background: #6B0F0F; color: white; border-bottom: 3px solid #C9A227; }
  .hero { background: linear-gradient(90deg, #6B0F0F, #9A1B1B, #C9A227); color: white; border-radius: 16px; }
  .card-stat { border: none; border-radius: 16px; }
  .table-head { background: #6B0F0F; color: white; }
  .badge-menunggu { background: #FFC107; color: #000; }
  .badge-diproses { background: #0D6EFD; }
  .badge-dikirim { background: #6F42C1; }
  .badge-selesai { background: #198754; }
  .badge-batal { background: #DC3545; }
</style>
</head>
<body>

<div class="header py-3 sticky-top">
  <div class="container d-flex justify-content-between align-items-center">
    <h4 class="m-0 fw-bold">🌿 Pasar<span style="color:#C9A227;">Kita</span> <small class="fw-normal">Admin</small></h4>
    <a href="../index.php" class="btn btn-light btn-sm fw-bold">🏪 Lihat Toko</a>
  </div>
</div>

<div class="container my-4">
  <div class="d-flex gap-2 mb-3 flex-wrap">
    <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">← Kembali ke Dashboard</a>
    <a href="../index.php" class="btn btn-outline-secondary btn-sm">🏪 Kembali ke Toko</a>
    <a href="produk.php" class="btn btn-outline-secondary btn-sm">📦 Kelola Produk</a>
    <a href="pesanan.php" class="btn btn-sm text-white" style="background:#6B0F0F;">🧾 Riwayat Pesanan</a>
  </div>

  <div class="hero p-4 mb-4">
    <h2 class="fw-bold m-0">🧾 Riwayat Pesanan</h2>
    <p class="mb-0">Total: <?= $stat_total['jml'] ?> Pesanan • Omset: Rp <?= number_format($stat_total['omset'], 0, ',', '.') ?> • Pempek • Tekwan • Kemplang • Songket • Kain Jumputan</p>
  </div>

  <!-- STATISTIK -->
  <div class="row mb-4">
    <div class="col-6 col-md-3 mb-2"><div class="card card-stat shadow-sm p-3"><small class="text-muted">Total Pesanan</small><h3 class="fw-bold m-0"><?= $stat_total['jml'] ?></h3></div></div>
    <div class="col-6 col-md-3 mb-2"><div class="card card-stat shadow-sm p-3"><small class="text-muted">Menunggu Pembayaran</small><h3 class="fw-bold m-0 text-warning"><?= $stat_menunggu['jml'] ?></h3></div></div>
    <div class="col-6 col-md-3 mb-2"><div class="card card-stat shadow-sm p-3"><small class="text-muted">Diproses</small><h3 class="fw-bold m-0 text-primary"><?= $stat_diproses['jml'] ?></h3></div></div>
    <div class="col-6 col-md-3 mb-2"><div class="card card-stat shadow-sm p-3"><small class="text-muted">Selesai</small><h3 class="fw-bold m-0 text-success"><?= $stat_selesai['jml'] ?></h3></div></div>
  </div>

  <!-- FILTER -->
  <div class="d-flex gap-2 mb-3 flex-wrap">
    <?php foreach (['Semua','Menunggu Pembayaran','Diproses','Dikirim','Selesai','Dibatalkan'] as $s): ?>
      <a href="pesanan.php?status_filter=<?= urlencode($s) ?>" class="btn btn-sm <?= ($filter==$s || ($filter=='' && $s=='Semua')) ? 'text-white' : 'btn-outline-secondary' ?>" style="<?= ($filter==$s || ($filter=='' && $s=='Semua')) ? 'background:#6B0F0F;' : '' ?>"><?= $s ?></a>
    <?php endforeach; ?>
  </div>

  <!-- TABEL -->
  <div class="card shadow-sm" style="border-radius:16px; overflow:hidden;">
    <div class="table-responsive">
    <table class="table table-hover m-0 align-middle">
      <thead class="table-head">
        <tr>
          <th>ID</th><th>Tanggal</th><th>Isi Pesanan</th><th>Total</th><th>Status</th><th>Aksi</th>
        </tr>
      </thead>
      <tbody>
      <?php if (mysqli_num_rows($q) == 0): ?>
        <tr><td colspan="6" class="text-center py-5">
          <div style="font-size:60px;">🧾</div>
          <b>Belum ada pesanan</b><br><small class="text-muted">Pesanan dari checkout.php akan muncul di sini</small>
        </td></tr>
      <?php endif; ?>
      <?php while ($p = mysqli_fetch_assoc($q)):
        $badge = match($p['status']) {
          'Menunggu Pembayaran' => 'badge-menunggu',
          'Diproses' => 'badge-diproses',
          'Dikirim' => 'badge-dikirim',
          'Selesai' => 'badge-selesai',
          'Dibatalkan' => 'badge-batal',
          default => 'bg-secondary'
        };
      ?>
        <tr>
          <td class="fw-bold">#PK-<?= str_pad($p['id'], 4, '0', STR_PAD_LEFT) ?></td>
          <td><?= date('d/m/Y H:i', strtotime($p['tanggal'])) ?><br><small class="text-muted"><?= $p['jml_item'] ?> item</small></td>
          <td style="max-width:280px;"><?= htmlspecialchars($p['isi_pesanan'] ?? '-') ?></td>
          <td class="fw-bold" style="color:#6B0F0F;">Rp <?= number_format($p['total'], 0, ',', '.') ?></td>
          <td><span class="badge <?= $badge ?>"><?= htmlspecialchars($p['status']) ?></span></td>
          <td>
            <button class="btn btn-sm text-white" style="background:#C9A227;" data-bs-toggle="modal" data-bs-target="#detail<?= $p['id'] ?>">Detail</button>
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#status<?= $p['id'] ?>">Ubah Status</button>
          </td>
        </tr>

        <!-- MODAL DETAIL -->
        <div class="modal fade" id="detail<?= $p['id'] ?>" tabindex="-1">
          <div class="modal-dialog"><div class="modal-content">
            <div class="modal-header" style="background:#6B0F0F; color:white;"><h5 class="modal-title">Detail #PK-<?= str_pad($p['id'], 4, '0', STR_PAD_LEFT) ?></h5><button class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
              <p><b>Tanggal:</b> <?= $p['tanggal'] ?><br><b>Total:</b> Rp <?= number_format($p['total'], 0, ',', '.') ?><br><b>Status:</b> <?= $p['status'] ?></p>
              <hr><b>Isi Pesanan:</b><br>
              <?php
                $qd = mysqli_query($conn, "SELECT pd.*, pr.nama_produk, pr.foto FROM pesanan_detail pd JOIN produk pr ON pd.produk_id=pr.id WHERE pd.pesanan_id={$p['id']}");
                while($d = mysqli_fetch_assoc($qd)):
                  $src = '../uploads/' . rawurlencode($d['foto']);
              ?>
                <div class="d-flex gap-2 align-items-center mb-2">
                  <?php if(file_exists('../uploads/'.$d['foto'])): ?><img src="<?= $src ?>" style="width:50px;height:50px;object-fit:cover;border-radius:8px;"><?php endif; ?>
                  <div class="flex-grow-1"><?= htmlspecialchars($d['nama_produk']) ?><br><small><?= $d['jumlah'] ?> x Rp <?= number_format($d['harga'], 0, ',', '.') ?></small></div>
                  <b>Rp <?= number_format($d['jumlah']*$d['harga'], 0, ',', '.') ?></b>
                </div>
              <?php endwhile; ?>
            </div>
          </div></div>
        </div>

        <!-- MODAL UBAH STATUS -->
        <div class="modal fade" id="status<?= $p['id'] ?>" tabindex="-1">
          <div class="modal-dialog"><div class="modal-content">
            <form method="POST">
              <div class="modal-header"><h5 class="modal-title">Ubah Status #PK-<?= str_pad($p['id'], 4, '0', STR_PAD_LEFT) ?></h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
              <div class="modal-body">
                <input type="hidden" name="pesanan_id" value="<?= $p['id'] ?>">
                <select name="status" class="form-select">
                  <?php foreach (['Menunggu Pembayaran','Diproses','Dikirim','Selesai','Dibatalkan'] as $opt): ?>
                    <option value="<?= $opt ?>" <?= $p['status']==$opt ? 'selected' : '' ?>><?= $opt ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="modal-footer"><button type="submit" name="ubah_status" class="btn text-white" style="background:#6B0F0F;">Simpan</button></div>
            </form>
          </div></div>
        </div>

      <?php endwhile; ?>
      </tbody>
    </table>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>