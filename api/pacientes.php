<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

/* Preflight CORS */
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once '../config/connectdb.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    /* ───── GET: listar / buscar ───── */
    case 'GET':
        $buscar = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
        if ($buscar !== '') {
            $stmt = $pdo->prepare(
                "SELECT cedula, nombre, apellido, correo
                 FROM pacientes
                 WHERE cedula   LIKE :q
                    OR nombre   LIKE :q
                    OR apellido LIKE :q
                    OR correo   LIKE :q
                 ORDER BY nombre ASC"
            );
            $stmt->execute([':q' => "%$buscar%"]);
        } else {
            $stmt = $pdo->query(
                "SELECT cedula, nombre, apellido, correo
                 FROM pacientes
                 ORDER BY nombre ASC"
            );
        }
        echo json_encode($stmt->fetchAll());
        break;

    /* ───── POST: crear ───── */
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data)
            $data = $_POST;

        // Validaciones
        $cedula = trim($data['cedula'] ?? '');
        $nombre = trim($data['nombre'] ?? '');
        $apellido = trim($data['apellido'] ?? '');
        $correo = trim($data['correo'] ?? '');

        if (!$cedula || !$nombre || !$apellido || !$correo) {
            http_response_code(422);
            echo json_encode(['success' => false, 'error' => 'Todos los campos son obligatorios.']);
            break;
        }
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'error' => 'El correo electrónico no es válido.']);
            break;
        }

        // Verificar cédula duplicada
        $check = $pdo->prepare("SELECT COUNT(*) FROM pacientes WHERE cedula = :cedula");
        $check->execute([':cedula' => $cedula]);
        if ($check->fetchColumn() > 0) {
            http_response_code(409);
            echo json_encode(['success' => false, 'error' => 'Ya existe un paciente con esa cédula.']);
            break;
        }

        $stmt = $pdo->prepare(
            "INSERT INTO pacientes (cedula, nombre, apellido, correo)
             VALUES (:cedula, :nombre, :apellido, :correo)"
        );
        $stmt->execute([
            ':cedula' => $cedula,
            ':nombre' => $nombre,
            ':apellido' => $apellido,
            ':correo' => $correo,
        ]);
        http_response_code(201);
        echo json_encode(['success' => true, 'message' => 'Paciente agregado correctamente.']);
        break;

    /* ───── PUT: actualizar ───── */
    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);

        $cedula = trim($data['cedula'] ?? '');
        $nombre = trim($data['nombre'] ?? '');
        $apellido = trim($data['apellido'] ?? '');
        $correo = trim($data['correo'] ?? '');

        if (!$cedula || !$nombre || !$apellido || !$correo) {
            http_response_code(422);
            echo json_encode(['success' => false, 'error' => 'Todos los campos son obligatorios.']);
            break;
        }
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'error' => 'El correo electrónico no es válido.']);
            break;
        }

        $stmt = $pdo->prepare(
            "UPDATE pacientes
             SET nombre=:nombre, apellido=:apellido, correo=:correo
             WHERE cedula=:cedula"
        );
        $stmt->execute([
            ':cedula' => $cedula,
            ':nombre' => $nombre,
            ':apellido' => $apellido,
            ':correo' => $correo,
        ]);

        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Paciente no encontrado.']);
            break;
        }
        echo json_encode(['success' => true, 'message' => 'Paciente actualizado correctamente.']);
        break;

    /* ───── DELETE: eliminar ───── */
    case 'DELETE':
        $data = json_decode(file_get_contents('php://input'), true);
        $cedula = trim($data['cedula'] ?? '');

        if (!$cedula) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Cédula requerida.']);
            break;
        }

        $stmt = $pdo->prepare("DELETE FROM pacientes WHERE cedula = :cedula");
        $stmt->execute([':cedula' => $cedula]);

        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Paciente no encontrado.']);
            break;
        }
        echo json_encode(['success' => true, 'message' => 'Paciente eliminado correctamente.']);
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido.']);
        break;
}
?>