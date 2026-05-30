<?php
// ====================================================================
// CONFIGURACIÓN DE LA BASE DE DATOS - CASO DE ESTUDIO
// ====================================================================

// Definición de las credenciales de conexión local
$host = "localhost";
$db_name = "auth_db";
$username = "root"; 
$password = ""; // En XAMPP la contraseña por defecto es vacía

try {
    // Inicialización del puente de conexión usando la tecnología PDO
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $username, $password);
    
    // Configurar PDO para que active las excepciones en caso de errores de SQL
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (PDOException $exception) {
    // Si la conexión falla, se captura el error y se responde en formato JSON amigable
    header("Content-Type: application/json");
    http_response_code(500); // Código de error interno del servidor
    echo json_encode([
        "status" => "error", 
        "message" => "Error crítico de conexión: " . $exception->getMessage()
    ]);
    exit; // Detener la ejecución del script por seguridad
}
?>