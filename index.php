<?php
// Fungsi sederhana untuk membaca/load file .env secara native
function loadEnv($filePath = __DIR__ . '/.env') {
    if (!file_exists($filePath)) {
        return false;
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        // Lewati komentar
        if (strpos($line, '#') === 0) {
            continue;
        }

        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value, " \t\n\r\0\x0B\"'"); // hapus spasi & tanda kutip
            
            if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                putenv(sprintf('%s=%s', $name, $value));
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
    return true;
}

// Muat konfigurasi .env
loadEnv();

// Ambil variabel lingkungan dengan nama kunci yang baru
$driver   = getenv('DB_CONNECTION') ?: 'pgsql';
$host     = getenv('DB_HOST') ?: 'localhost';
$port     = getenv('DB_PORT') ?: '5432';
$dbname   = getenv('DB_DATABASE') ?: 'db_users';
$username = getenv('DB_USERNAME') ?: 'postgres';
$password = getenv('DB_PASSWORD') ?: '';

try {
    // Koneksi PDO dinamis menggunakan variabel dari .env
    $pdo = new PDO("{$driver}:host={$host};port={$port};dbname={$dbname}", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // Query untuk mengambil data pengguna
    $stmt = $pdo->prepare("SELECT id, nama, tanggal_lahir, usia, created_at FROM users ORDER BY id ASC");
    $stmt->execute();
    $users = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Koneksi gagal atau terjadi kesalahan query: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pengguna - PostgreSQL dengan .env</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6; padding: 40px; margin: 0; }
        .container { max-width: 900px; margin: 0 auto; background: #ffffff; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        h2 { margin-top: 0; color: #2c3e50; border-bottom: 2px solid #27ae60; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px 15px; text-align: left; }
        thead tr { background-color: #27ae60; color: #ffffff; }
        tbody tr { border-bottom: 1px solid #dddddd; }
        tbody tr:nth-of-type(even) { background-color: #f9f9f9; }
        tbody tr:hover { background-color: #f1f1f1; }
        .badge { background-color: #e8f8f5; color: #1e8449; padding: 4px 8px; border-radius: 4px; font-weight: bold; }
    </style>
</head>
<body>

<div class="container">
    <h2>Data Users (PostgreSQL + .env)</h2>
    
    <?php if (!empty($users)): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama</th>
                    <th>Tanggal Lahir</th>
                    <th>Usia</th>
                    <th>Dibuat Pada</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['id']); ?></td>
                        <td><strong><?= htmlspecialchars($user['nama']); ?></strong></td>
                        <td><?= date('d-m-Y', strtotime($user['tanggal_lahir'])); ?></td>
                        <td><span class="badge"><?= htmlspecialchars($user['usia']); ?> tahun</span></td>
                        <td><?= htmlspecialchars($user['created_at']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Tidak ada data pengguna ditemukan.</p>
    <?php endif; ?>
</div>

</body>
</html>