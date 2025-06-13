<?php
session_start();
require '../db_config.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: ../auth/login.php");
  exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah'])) {
  $kategori = $_POST['kategori'];
  $jumlah = (int)$_POST['jumlah'];
  $tanggal = $_POST['tanggal'];

  $stmt = $pdo->prepare("INSERT INTO anggaran (kategori, jumlah, tanggal, user_id) VALUES (?, ?, ?, ?)");
  $stmt->execute([$kategori, $jumlah, $tanggal, $user_id]);

  header("Location: anggaran.php");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
  $id = $_POST['id'];
  $kategori = $_POST['kategori'];
  $jumlah = (int)$_POST['jumlah'];
  $tanggal = $_POST['tanggal'];

  $stmt = $pdo->prepare("UPDATE anggaran SET kategori = ?, jumlah = ?, tanggal = ? WHERE id = ? AND user_id = ?");
  $stmt->execute([$kategori, $jumlah, $tanggal, $id, $user_id]);

  header("Location: anggaran.php");
  exit;
}

if (isset($_GET['hapus'])) {
  $hapus_id = $_GET['hapus'];
  $stmt = $pdo->prepare("DELETE FROM anggaran WHERE id = ? AND user_id = ?");
  $stmt->execute([$hapus_id, $user_id]);

  header("Location: anggaran.php");
  exit;
}

$data_anggaran = [];
$total_anggaran = 0;

try {
  $stmt = $pdo->prepare("SELECT * FROM anggaran WHERE user_id = ? ORDER BY tanggal DESC");
  $stmt->execute([$user_id]);
  $data_anggaran = $stmt->fetchAll(PDO::FETCH_ASSOC);

  foreach ($data_anggaran as $item) {
    $total_anggaran += $item['jumlah'];
  }
} catch (PDOException $e) {
  echo "Gagal mengambil data: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Manajemen Anggaran</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Poppins', sans-serif; }
    .fade-in { animation: fadeIn 0.8s ease-in-out; }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body class="bg-[#EFE7D9] min-h-screen px-4 pb-24 flex flex-col items-center">

  <div class="w-full max-w-3xl mt-10 bg-white p-6 rounded-xl shadow-lg border border-[#C5CBAF] fade-in">
    <h1 class="text-3xl font-bold text-center text-[#A0A58C] mb-6">💼 Manajemen Anggaran</h1>

    <?php if (isset($_GET['edit'])):
      $edit_id = $_GET['edit'];
      $stmt = $pdo->prepare("SELECT * FROM anggaran WHERE id = ? AND user_id = ?");
      $stmt->execute([$edit_id, $user_id]);
      $row = $stmt->fetch();
    ?>

      <form method="POST" class="bg-[#F8CDBE] p-5 rounded-xl shadow mb-6 border border-[#F78E79] space-y-4">
        <input type="hidden" name="id" value="<?= $row['id'] ?>">
        <input name="kategori" value="<?= htmlspecialchars($row['kategori']) ?>" placeholder="Kategori" class="w-full p-2 border rounded" required>
        <input name="jumlah" type="number" value="<?= $row['jumlah'] ?>" placeholder="Jumlah" class="w-full p-2 border rounded" required>
        <input name="tanggal" type="date" value="<?= $row['tanggal'] ?>" class="w-full p-2 border rounded" required>
        <button name="update" class="bg-[#F28482] text-white w-full py-2 rounded hover:bg-[#F78E79]">Update</button>
        <a href="anggaran.php" class="text-sm text-blue-600 hover:underline block text-center">Batal</a>
      </form>

    <?php else: ?>

      <form method="POST" class="bg-[#C5CBAF] p-5 rounded-xl shadow mb-6 border border-[#A0A58C] space-y-4">
        <input name="kategori" placeholder="Kategori (contoh: Makanan)" class="w-full p-2 border rounded" required>
        <input name="jumlah" type="number" placeholder="Jumlah Anggaran" class="w-full p-2 border rounded" required>
        <input name="tanggal" type="date" class="w-full p-2 border rounded" required>
        <button name="tambah" class="bg-[#A0A58C] text-white w-full py-2 rounded hover:bg-[#789262]">Simpan</button>
      </form>

    <?php endif; ?>

    <div class="text-center mb-4">
      <span class="inline-block bg-green-100 text-green-700 px-4 py-2 rounded-full shadow text-sm font-medium">
        💰 Total Anggaran: Rp <?= number_format($total_anggaran, 0, ',', '.') ?>
      </span>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full bg-white border rounded shadow text-sm">
        <thead class="bg-[#F8CDBE] text-[#444]">
          <tr>
            <th class="p-3 border">Kategori</th>
            <th class="p-3 border">Jumlah</th>
            <th class="p-3 border">Tanggal</th>
            <th class="p-3 border">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($data_anggaran)): ?>
            <?php foreach ($data_anggaran as $row): ?>
              <tr class="hover:bg-[#F5F1E8]">
                <td class="p-3 border"><?= htmlspecialchars($row['kategori']) ?></td>
                <td class="p-3 border text-green-700">Rp <?= number_format($row['jumlah'], 0, ',', '.') ?></td>
                <td class="p-3 border"><?= date('d-m-Y', strtotime($row['tanggal'])) ?></td>
                <td class="p-3 border text-center">
                  <a href="?edit=<?= $row['id'] ?>" class="text-blue-600 hover:underline">Edit</a>
                  <a href="?hapus=<?= $row['id'] ?>" class="text-red-600 hover:underline ml-2" onclick="return confirm('Hapus data anggaran ini?')">Hapus</a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="4" class="text-center text-gray-500 p-4">Belum ada data anggaran.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="fixed bottom-4 left-1/2 -translate-x-1/2 bg-white shadow-lg rounded-full px-6 py-2 flex gap-4 border border-[#C5CBAF]">
    <a href="../dashboard/dashboard.php" class="bg-[#A0A58C] text-white px-4 py-2 rounded-full hover:bg-[#789262] text-sm font-medium">🏠 Dashboard</a>
    <a href="../sasaran/sasaran.php" class="bg-[#F8CDBE] text-white px-4 py-2 rounded-full hover:bg-[#F78E79] text-sm font-medium">🎯 Sasaran</a>
    <a href="../laporan/laporan.php" class="bg-[#F8CDBE] text-white px-4 py-2 rounded-full hover:bg-[#F78E79] text-sm font-medium">📊 Laporan</a>
    <a href="../transaksi/tambah_transaksi.php" class="bg-[#F28482] text-white px-4 py-2 rounded-full hover:bg-[#F78E79] text-sm font-medium">➕ Transaksi</a>
  </div>

</body>
</html>
