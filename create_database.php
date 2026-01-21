<?php
try {
    // Conectar sin especificar base de datos (con contraseña vacía para phpMyAdmin)
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Crear la base de datos si no existe
    $pdo->exec("CREATE DATABASE IF NOT EXISTS atlantico18 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    
    echo "✓ Base de datos 'atlantico18' creada exitosamente o ya existe.\n";
    
    // Verificar que podemos conectarnos
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=atlantico18', 'root', '');
    echo "✓ Conexión a la base de datos 'atlantico18' verificada correctamente.\n";
    
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
