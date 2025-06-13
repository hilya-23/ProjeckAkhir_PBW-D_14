<?php
session_start();
require '../db_config.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: ../auth/login.php");
  exit;
}

$user_id = $_SESSION['user_id'];

// Tambah sasaran
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah'])) {
  $tujuan = $_POST['tujuan'];
  $target = $_POST['target'];
  $stmt = $pdo->prepare("INSERT INTO sasaran (tujuan, target, user_id) VALUES (?, ?, ?)");
  $stmt->execute([$tujuan, $target, $user_id]);
  header("Location: sasaran.php");
  exit;
}

// Tambah saldo ke sasaran
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_saldo'])) {
  $id = $_POST['id'];
  $jumlah = $_POST['jumlah'];
  $stmtCheck = $pdo->prepare("SELECT id FROM sasaran WHERE id = ? AND user_id = ?");
  $stmtCheck->execute([$id, $user_id]);
  if ($stmtCheck->rowCount() > 0) {
    $stmt = $pdo->prepare("UPDATE sasaran SET tercapai = tercapai + ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$jumlah, $id, $user_id]);
  }
  header("Location: sasaran.php");
  exit;
}

// Edit sasaran
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
  $id = $_POST['id'];
  $tujuan = $_POST['tujuan'];
  $target = $_POST['target'];
  $stmt = $pdo->prepare("UPDATE sasaran SET tujuan = ?, target = ? WHERE id = ? AND user_id = ?");
  $stmt->execute([$tujuan, $target, $id, $user_id]);
  header("Location: sasaran.php");
  exit;
}

// Hapus sasaran
if (isset($_GET['hapus'])) {
  $id = $_GET['hapus'];
  $stmtCheck = $pdo->prepare("SELECT id FROM sasaran WHERE id = ? AND user_id = ?");
  $stmtCheck->execute([$id, $user_id]);
  if ($stmtCheck->rowCount() > 0) {
    $stmt = $pdo->prepare("DELETE FROM sasaran WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $user_id]);
  }
  header("Location: sasaran.php");
  exit;
}

// Ambil semua sasaran
$stmt = $pdo->prepare("SELECT * FROM sasaran WHERE user_id = ? ORDER BY id DESC");
$stmt->execute([$user_id]);
$sasaran = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Sasaran Keuangan</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
<body class="min-h-screen bg-gradient-to-br from-[#F3EBDD] to-[#FCEFE7] px-4 pb-28 pt-10 flex flex-col items-center text-[#4A5240] font-sans">

  <div class="w-full max-w-xl bg-white p-6 rounded-xl shadow-xl border border-[#e9d9c9] fade-in">

    <h1 class="text-3xl font-bold mb-6 flex items-center justify-center gap-3 slide-up">
      <i class="fas fa-bullseye text-[#F89C8C]"></i> Sasaran Keuangan
    </h1>

    <?php if (isset($_GET['edit'])):
      $edit_id = $_GET['edit'];
      $data = $pdo->prepare("SELECT * FROM sasaran WHERE id = ? AND user_id = ?");
      $data->execute([$edit_id, $user_id]);
      $row = $data->fetch();
    ?>
      <?php if ($row): ?>
        <form method="POST" class="bg-[#F8D5C2] p-4 rounded-lg shadow mb-6 slide-up">
          <input type="hidden" name="id" value="<?= $row['id'] ?>">
          <input type="text" name="tujuan" value="<?= htmlspecialchars($row['tujuan']) ?>" class="w-full mb-3 p-3 border rounded focus:ring-2 focus:ring-[#F89C8C]" required>
          <input type="number" name="target" value="<?= $row['target'] ?>" class="w-full mb-3 p-3 border rounded focus:ring-2 focus:ring-[#F89C8C]" required>
          <button name="update" class="bg-[#F89C8C] text-white px-4 py-2 rounded hover:bg-[#e08070] transition">Update</button>
          <a href="sasaran.php" class="ml-4 text-sm text-gray-600 hover:underline">Batal</a>
        </form>
      <?php else: ?>
        <p class="text-red-600 mb-4">Sasaran tidak ditemukan.</p>
      <?php endif; ?>

    <?php else: ?>
      <form method="POST" class="bg-[#F3EBDD] p-4 rounded-lg shadow mb-6 slide-up">
        <input type="text" name="tujuan" placeholder="Tujuan" class="w-full mb-3 p-3 border rounded focus:ring-2 focus:ring-[#F89C8C]" required>
        <input type="number" name="target" placeholder="Target Nominal" class="w-full mb-3 p-3 border rounded focus:ring-2 focus:ring-[#F89C8C]" required>
        <button name="tambah" class="bg-[#F89C8C] text-white w-full py-2 rounded hover:bg-[#e08070] transition">Simpan Sasaran</button>
      </form>
    <?php endif; ?>

    <?php foreach ($sasaran as $s):
      $progres = $s['target'] > 0 ? min(100, ($s['tercapai'] / $s['target']) * 100) : 0;
    ?>
      <div class="bg-white p-5 mb-6 rounded-xl shadow border border-[#e0d4c4] hover:shadow-lg transition slide-up">
        <div class="flex justify-between items-center mb-2">
          <h3 class="font-semibold text-lg"><?= htmlspecialchars($s['tujuan']) ?></h3>
          <div class="space-x-3 text-sm">
            <a href="?edit=<?= $s['id'] ?>" class="text-[#F89C8C] hover:underline"><i class="fas fa-edit"></i> Edit</a>
            <a href="?hapus=<?= $s['id'] ?>" class="text-[#D76B61] hover:underline" onclick="return confirm('Yakin hapus sasaran ini?')"><i class="fas fa-trash-alt"></i> Hapus</a>
          </div>
        </div>

        <div class="w-full bg-gray-200 h-4 rounded-full overflow-hidden">
          <div class="h-4 bg-gradient-to-r from-[#F89C8C] to-[#F8D5C2] rounded-full transition-all duration-700 ease-in-out" style="width: <?= $progres ?>%"></div>
        </div>
        <p class="text-sm mt-2 mb-3 text-gray-600">
          Rp <?= number_format($s['tercapai'], 0, ',', '.') ?> dari Rp <?= number_format($s['target'], 0, ',', '.') ?> (<?= round($progres) ?>%)
        </p>

        <form method="POST" class="flex gap-2 mt-2">
          <input type="hidden" name="id" value="<?= $s['id'] ?>">
          <input type="number" name="jumlah" placeholder="Tambah saldo" class="flex-grow p-2 border rounded focus:ring-2 focus:ring-[#9AA089]" required>
          <button name="tambah_saldo" class="bg-[#9AA089] text-white px-4 py-2 rounded hover:bg-[#7c8c6c] transition">Tambah</button>
        </form>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="fixed bottom-4 left-1/2 -translate-x-1/2 bg-white shadow-lg rounded-full px-6 py-2 flex gap-4 border border-[#C5CBAF]">
    <a href="../dashboard/dashboard.php" class="bg-[#A0A58C] text-white px-4 py-2 rounded-full hover:bg-[#789262] text-sm font-medium">🏠 Dashboard</a>
    <a href="../anggaran/anggaran.php" class="bg-[#F8CDBE] text-white px-4 py-2 rounded-full hover:bg-[#F78E79] text-sm font-medium">📋 Anggaran</a>
    <a href="../laporan/laporan.php" class="bg-[#F8CDBE] text-white px-4 py-2 rounded-full hover:bg-[#F78E79] text-sm font-medium">📊 Laporan</a>
    <a href="../transaksi/tambah_transaksi.php" class="bg-[#F28482] text-white px-4 py-2 rounded-full hover:bg-[#F78E79] text-sm font-medium">➕ Transaksi</a>
  </div>

</body>
</html>
