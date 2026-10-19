<?php

declare(strict_types=1);

header('Content-Type: text/html; charset=utf-8');

function envValue(string $key): string
{
    static $env = null;

    if ($env === null) {
        $path = __DIR__ . '/.env';

        if (!is_file($path) || !is_readable($path)) {
            throw new RuntimeException('File .env tidak ditemukan atau tidak bisa dibaca.');
        }

        $env = parse_ini_file($path, false, INI_SCANNER_RAW);

        if ($env === false) {
            throw new RuntimeException('File .env gagal dibaca.');
        }
    }

    $value = $env[$key] ?? null;

    if (!is_string($value) || $value === '') {
        throw new RuntimeException("Konfigurasi {$key} belum diisi.");
    }

    return $value;
}

$status = [];
$pdo = null;

try {
    if (envValue('DB_CONNECTION') !== 'pgsql') {
        throw new RuntimeException('DB_CONNECTION harus bernilai pgsql.');
    }

    $host = envValue('DB_HOST');
    $port = envValue('DB_PORT');
    $database = envValue('DB_DATABASE');
    $username = envValue('DB_USERNAME');
    $password = envValue('DB_PASSWORD');

    $status[] = ['Membaca .env', true, 'Konfigurasi berhasil dibaca.'];

    $dsn = sprintf(
        'pgsql:host=%s;port=%s;dbname=%s;connect_timeout=5',
        $host,
        $port,
        $database
    );

    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    $version = $pdo->query('SELECT version()')->fetchColumn();

    $status[] = [
        'Koneksi PostgreSQL',
        true,
        'Berhasil terhubung ke ' . $database . ' menggunakan user ' . $username . '.'
    ];

    $schemaFile = __DIR__ . '/schema.sql';

    if (!is_file($schemaFile) || !is_readable($schemaFile)) {
        throw new RuntimeException('File schema.sql tidak ditemukan atau tidak bisa dibaca.');
    }

    $schema = file_get_contents($schemaFile);

    if ($schema === false || trim($schema) === '') {
        throw new RuntimeException('File schema.sql kosong atau gagal dibaca.');
    }

    // Untuk pengujian, jalankan schema di dalam transaksi.
    // Cocok untuk schema sederhana yang menggunakan perintah transactional.
    $pdo->beginTransaction();

    try {
        $pdo->exec($schema);
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $e;
    }

    $status[] = [
        'Menjalankan schema.sql',
        true,
        'Schema berhasil dijalankan.'
    ];

    $rows = $pdo
        ->query('SELECT id, message, created_at FROM connection_test ORDER BY id DESC LIMIT 5')
        ->fetchAll();

    $status[] = [
        'Membaca tabel pengujian',
        true,
        'Berhasil membaca ' . count($rows) . ' baris.'
    ];
} catch (Throwable $e) {
    $status[] = ['Pengujian', false, $e->getMessage()];
}

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nivor PostgreSQL Connection Test</title>
    <style>
        body {
            font-family: system-ui, sans-serif;
            max-width: 850px;
            margin: 40px auto;
            padding: 0 20px;
            color: #222;
        }
        .status {
            padding: 14px;
            margin: 12px 0;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        .success { border-left: 5px solid #16803d; }
        .failed { border-left: 5px solid #c62828; }
        pre {
            white-space: pre-wrap;
            overflow-wrap: anywhere;
        }
        .warning { color: #a15c00; }
    </style>
</head>
<body>
    <h1>Nivor PostgreSQL Test</h1>
    <p>Hasil pengujian koneksi database aplikasi.</p>

    <?php foreach ($status as [$title, $success, $message]): ?>
        <section class="status <?= $success ? 'success' : 'failed' ?>">
            <strong><?= $success ? 'BERHASIL' : 'GAGAL' ?> — <?= h($title) ?></strong>
            <p><?= h($message) ?></p>
        </section>
    <?php endforeach; ?>

    <?php if ($pdo instanceof PDO && isset($rows)): ?>
        <h2>Data dari connection_test</h2>
        <?php if ($rows === []): ?>
            <p>Belum ada data yang ditemukan.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($rows as $row): ?>
                    <li>
                        #<?= (int) $row['id'] ?> —
                        <?= h((string) $row['message']) ?> —
                        <?= h((string) $row['created_at']) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    <?php endif; ?>

    <p class="warning">
        Halaman ini hanya untuk pengujian. Hapus atau lindungi halaman
        setelah pengujian selesai. Jangan tampilkan detail error database
        kepada pengunjung di production.
    </p>
</body>
</html>