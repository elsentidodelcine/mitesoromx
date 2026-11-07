<?php
// Bórralo después de la prueba
header('Content-Type: text/plain; charset=utf-8');

echo "PHP: " . PHP_VERSION . "\n";
echo "PDO: " . (extension_loaded('pdo') ? 'OK' : 'NO') . "\n";
echo "pdo_mysql: " . (extension_loaded('pdo_mysql') ? 'OK' : 'NO') . "\n";
echo "JSON: " . (extension_loaded('json') ? 'OK' : 'NO') . "\n";
echo "mbstring: " . (extension_loaded('mbstring') ? 'OK' : 'NO') . "\n";
echo "fileinfo: " . (extension_loaded('fileinfo') ? 'OK' : 'NO') . "\n";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "post_max_size: " . ini_get('post_max_size') . "\n";
echo "memory_limit: " . ini_get('memory_limit') . "\n";

// Prueba de conexión (sin mostrar la contraseña)
try {
    // Reemplaza SOLO la contraseña. No subas este archivo con la clave real a GitHub.
    $pdo = new PDO(
        'mysql:host=db5021810304.hosting-data.io;dbname=dbs16302678;charset=utf8mb4',
        'dbu5672783',
        'iyrg7ubUs3WQJ$e26',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    echo "MariaDB conexión: OK\n";
    echo "Versión servidor: " . $pdo->query('SELECT VERSION()')->fetchColumn() . "\n";
} catch (Throwable $e) {
    echo "MariaDB conexión: ERROR\n";
    echo "Mensaje: " . $e->getMessage() . "\n";
}