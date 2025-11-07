<?php
require_once __DIR__ . '/../config/conexion.php';

if ($argc < 3) {
    echo "Uso: php update_password.php <username> <new_password>\n";
    exit(1);
}

$username = $argv[1];
$newPassword = $argv[2];

try {
    $db = Conexion::connect();
    if (!$db) {
        echo "ERROR: No se pudo conectar a la base de datos\n";
        exit(1);
    }

    $hash = password_hash($newPassword, PASSWORD_DEFAULT);

    $stmt = $db->prepare("UPDATE usuarios SET password = ? WHERE username = ?");
    if (!$stmt) {
        echo "ERROR prepare: " . $db->error . "\n";
        exit(1);
    }
    $stmt->bind_param('ss', $hash, $username);
    if ($stmt->execute()) {
        echo "Contraseña actualizada para usuario: $username\n";
    } else {
        echo "ERROR execute: " . $stmt->error . "\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
