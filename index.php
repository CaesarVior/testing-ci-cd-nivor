<?php
// Konfigurasi Koneksi Database
$host = 'localhost';
$dbname = 'db_users';
$username = 'root';
$password = ''; // sesuaikan jika ada password database Anda

try {
    // Membuat koneksi PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // Query mengambil data dari tabel users
    $stmt = $pdo->prepare("SELECT id, nama, tanggal_lahir, usia, created_at FROM users ORDER BY id ASC");
    $stmt->execute();
    $users = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pengguna (Users)</title>
    <style>
        body { font-family: sans-serif; background-color: #f4f7f6; padding: 40px; }
        .container { max-width: 800px; margin: 0 auto; background: #fff; padding: 25px; border-radius: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; border-bottom: 1px solid #ddd; text-align: left; }
        th { background-color: #3498db; color: white; }
    </style>
</head>
<body>

<div class="container">
    <h2>Data Users</h2>
    <?php if (!empty($users)): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama</th>
                    <th>Tanggal Lahir</th>
                    <th>Usia</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['id']); ?></td>
                        <td><?= htmlspecialchars($user['nama']); ?></td>
                        <td><?= date('d-m-Y', strtotime($user['tanggal_lahir'])); ?></td>
                        <td><?= htmlspecialchars($user['usia']); ?> tahun</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Data tidak ditemukan.</p>
    <?php endif; ?>
</div>

</body>
</html>