<?php
// Permitir accesos externos y definir que la respuesta de esta API siempre será JSON
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

// Importar la configuración de conexión que acabas de hacer
require_once 'config.php';

// Validar que la petición sea estrictamente POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Método no permitido."]);
    exit;
}

// Capturar el cuerpo de la petición JSON (los datos que envíe el usuario)
$data = json_decode(file_get_contents("php://input"));

// Comprobar que los campos requeridos tengan datos
if (!empty($data->usuario) && !empty($data->password)) {
    try {
        // Verificar si el nombre de usuario ya está tomado en la base de datos
        $queryCheck = "SELECT id FROM usuarios WHERE usuario = :usuario";
        $stmtCheck = $pdo->prepare($queryCheck);
        $stmtCheck->bindParam(":usuario", $data->usuario);
        $stmtCheck->execute();

        if ($stmtCheck->rowCount() > 0) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "El nombre de usuario ya está registrado."]);
            exit;
        }

        // Estructurar la inserción del nuevo usuario
        $query = "INSERT INTO usuarios (usuario, password) VALUES (:usuario, :password)";
        $stmt = $pdo->prepare($query);

        // Encriptación segura de la contraseña usando el algoritmo BCRYPT
        $password_hashed = password_hash($data->password, PASSWORD_BCRYPT);
        
        // Sanitización de entradas de texto contra inyecciones de código
        $usuario_clean = htmlspecialchars(strip_tags($data->usuario));

        // Enlazar los datos procesados con los parámetros de la consulta SQL
        $stmt->bindParam(":usuario", $usuario_clean);
        $stmt->bindParam(":password", $password_hashed);

        if ($stmt->execute()) {
            http_response_code(201); // Estado 201 significa creado con éxito
            echo json_encode(["status" => "success", "message" => "Usuario registrado satisfactoriamente."]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Error en el servidor: " . $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Datos incompletos. Ingrese usuario y contraseña."]);
}
?>