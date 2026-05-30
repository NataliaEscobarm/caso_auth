<?php
// Configuración de cabeceras para respuestas API en formato JSON
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

// Incluir la conexión a la base de datos
require_once 'config.php';

// Validar que la petición se realice por el método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Método no permitido."]);
    exit;
}

// Capturar los datos enviados en formato JSON
$data = json_decode(file_get_contents("php://input"));

if (!empty($data->usuario) && !empty($data->password)) {
    try {
        // Buscar si el usuario existe en la base de datos
        $query = "SELECT id, usuario, password FROM usuarios WHERE usuario = :usuario LIMIT 0,1";
        $stmt = $pdo->prepare($query);
        
        $usuario_clean = htmlspecialchars(strip_tags($data->usuario));
        $stmt->bindParam(":usuario", $usuario_clean);
        $stmt->execute();

        // Si encontramos una coincidencia con el nombre de usuario...
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verificar si la contraseña ingresada coincide con el hash encriptado
            if (password_verify($data->password, $row['password'])) {
                http_response_code(200); // Código 200 significa "Autenticación Satisfactoria"
                echo json_encode([
                    "status" => "success",
                    "message" => "Autenticación satisfactoria.",
                    "user" => [
                        "id" => $row['id'],
                        "usuario" => $row['usuario']
                    ]
                ]);
            } else {
                // La contraseña no es correcta
                http_response_code(401); // Código 401 significa "No autorizado"
                echo json_encode(["status" => "error", "message" => "Error en la autenticación. Contraseña incorrecta."]);
            }
        } else {
            // El usuario no existe en el sistema
            http_response_code(401);
            echo json_encode(["status" => "error", "message" => "Error en la autenticación. El usuario no existe."]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Error en el servidor: " . $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Datos incompletos."]);
}
?>