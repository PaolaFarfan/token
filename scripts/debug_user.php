<?php
require_once __DIR__ . '/../config/conexion.php';

try {
    $db = Conexion::connect();
    if (!$db) {
        echo "ERROR: No se pudo conectar a la base de datos\n";
        exit(1);
    }

    $username = $argv[1] ?? 'admin';
    $stmt = $db->prepare("SELECT id, username, password, nombre, rol, estado FROM usuarios WHERE username = ? LIMIT 1");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        echo json_encode($user, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    } else {
        echo "null\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
