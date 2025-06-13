<?php
session_start();
require '../db_config.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: ../auth/login.php");
  exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $jenis = $_POST['jenis'];
  $kategori = $_POST['kategori'];
  $jumlah = $_POST['jumlah'];
  $keterangan = $_POST['keterangan'];
  $tanggal = $_POST['tanggal'];

  if (!in_array($jenis, ['Pemasukan', 'Pengeluaran'])) {
    die("Jenis transaksi tidak valid.");
  }

  $stmt = $pdo->prepare("INSERT INTO transaksi (user_id, jenis, kategori, jumlah, keterangan, tanggal) VALUES (?, ?, ?, ?, ?, ?)");
  $stmt->execute([$user_id, $jenis, $kategori, $jumlah, $keterangan, $tanggal]);

  header("Location: tambah_transaksi.php");
  exit;
}

$stmt = $pdo->prepare("SELECT * FROM transaksi WHERE user_id = ? ORDER BY tanggal DESC");
$stmt->execute([$user_id]);
$transaksi = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Tambah Transaksi</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
  <style>
    .fade-in { animation: fadeIn 0.8s ease-out both; }
    .slide-up { animation: slideUp 0.9s ease-out both; }
    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }
    @keyframes slideUp {
      from { transform: translateY(40px); opacity: 0; }
      to { transform: translateY(0); opacity: 1; }
    }
  </style>
</head>
<body class="bg-[#FDF6F0] min-h-screen flex flex-col items-center justify-center px-4 py-10 relative">

  <div class="w-full max-w-2xl bg-white p-6 rounded-xl shadow-xl border border-[#F8D5C2] fade-in">

    <h1 class="text-3xl font-bold mb-6 text-center flex items-center justify-center gap-3 text-[#F89C8C] slide-up">
      <i class="fas fa-wallet"></i> Tambah Transaksi
    </h1>

    <form method="POST" class="bg-[#FFF5EF] p-5 rounded-xl shadow border border-[#FADDD0] mb-6 space-y-4 slide-up">
      <select name="jenis" class="w-full p-3 border rounded focus:ring-2 focus:ring-[#F89C8C]" required>
        <option value="">Pilih Jenis</option>
        <option value="Pemasukan">Pemasukan</option>
        <option value="Pengeluaran">Pengeluaran</option>
      </select>
      <input name="kategori" placeholder="Kategori" class="w-full p-3 border rounded focus:ring-2 focus:ring-[#F89C8C]" required>
      <input name="jumlah" type="number" placeholder="Jumlah (Rp)" class="w-full p-3 border rounded focus:ring-2 focus:ring-[#F89C8C]" required>
      <input name="keterangan" placeholder="Keterangan" class="w-full p-3 border rounded focus:ring-2 focus:ring-[#F89C8C]">
      <input name="tanggal" type="date" class="w-full p-3 border rounded focus:ring-2 focus:ring-[#F89C8C]" required>
      <button type="submit" class="bg-[#F89C8C] text-white px-4 py-2 rounded w-full hover:bg-[#E97462] transition-all">
        <i class="fas fa-plus-circle mr-2"></i> Simpan
      </button>
    </form>

    <h2 class="text-xl font-semibold mb-4 text-[#4A5240] flex items-center gap-2 slide-up">
      <i class="fas fa-clock text-[#A0A58C]"></i> Riwayat Transaksi
    </h2>

    <?php if (count($transaksi) === 0): ?>
      <p class="text-center text-gray-500 italic slide-up">Belum ada transaksi.</p>
    <?php else: ?>
      <div class="overflow-x-auto slide-up">
        <table class="w-full bg-white border rounded-xl shadow text-sm text-[#4A5240]">
          <thead class="bg-[#FADDD0] text-[#4A5240]">
            <tr>
              <th class="p-3 border">Tanggal</th>
              <th class="p-3 border">Jenis</th>
              <th class="p-3 border">Kategori</th>
              <th class="p-3 border">Jumlah</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($transaksi as $t): ?>
              <tr class="hover:bg-[#FDF0E6] transition-all">
                <td class="p-3 border"><?= date('d-m-Y', strtotime($t['tanggal'])) ?></td>
                <td class="p-3 border"><?= htmlspecialchars($t['jenis']) ?></td>
                <td class="p-3 border"><?= htmlspecialchars($t['kategori']) ?></td>
                <td class="p-3 border <?= $t['jenis'] == 'Pemasukan' ? 'text-green-600' : 'text-red-600' ?>">
                  Rp <?= number_format($t['jumlah'], 0, ',', '.') ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

    <div class="fixed bottom-4 left-1/2 -translate-x-1/2 bg-white shadow-lg rounded-full px-6 py-2 flex gap-4 border border-[#C5CBAF]">
    <a href="../dashboard/dashboard.php" class="bg-[#A0A58C] text-white px-4 py-2 rounded-full hover:bg-[#789262] text-sm font-medium">🏠 Dashboard</a>
    <a href="../anggaran/anggaran.php" class="bg-[#F8CDBE] text-white px-4 py-2 rounded-full hover:bg-[#F78E79] text-sm font-medium">📋 Anggaran</a>
    <a href="../laporan/laporan.php" class="bg-[#F8CDBE] text-white px-4 py-2 rounded-full hover:bg-[#F78E79] text-sm font-medium">📊 Laporan</a>
    <a href="../sasaran/sasaran.php" class="bg-[#F28482] text-white px-4 py-2 rounded-full hover:bg-[#F78E79] text-sm font-medium">🎯 Sasaran</a>
  </div>

</body>
</html>
