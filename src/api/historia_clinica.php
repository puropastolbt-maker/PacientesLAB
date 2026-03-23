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

require_once __DIR__ . '/../config/connectdb.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    /* ───── GET: listar por cédula del paciente ───── */
    case 'GET':
        $cedula = isset($_GET['cedula']) ? trim($_GET['cedula']) : '';
        
        if (!$cedula) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Cédula requerida.']);
            break;
        }

        $stmt = $pdo->prepare(
            "SELECT id_historia, cedula, fecha, diagnostico, tratamiento, observaciones
             FROM historia_clinica
             WHERE cedula = :cedula
             ORDER BY fecha DESC"
        );
        $stmt->execute([':cedula' => $cedula]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    /* ───── POST: crear registro ───── */
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data)
            $data = $_POST;

        // Validaciones
        $cedula = trim($data['cedula'] ?? '');
        $fecha = trim($data['fecha'] ?? '');
        $diagnostico = trim($data['diagnostico'] ?? '');
        $tratamiento = trim($data['tratamiento'] ?? '');
        $observaciones = trim($data['observaciones'] ?? '');

        if (!$cedula || !$fecha) {
            http_response_code(422);
            echo json_encode(['success' => false, 'error' => 'Cédula y fecha son obligatorios.']);
            break;
        }

        // Verificar que el paciente existe
        $check = $pdo->prepare("SELECT COUNT(*) FROM pacientes WHERE cedula = :cedula");
        $check->execute([':cedula' => $cedula]);
        if ($check->fetchColumn() == 0) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'El paciente no existe.']);
            break;
        }

        $stmt = $pdo->prepare(
            "INSERT INTO historia_clinica (cedula, fecha, diagnostico, tratamiento, observaciones)
             VALUES (:cedula, :fecha, :diagnostico, :tratamiento, :observaciones)"
        );
        $stmt->execute([
            ':cedula' => $cedula,
            ':fecha' => $fecha,
            ':diagnostico' => $diagnostico,
            ':tratamiento' => $tratamiento,
            ':observaciones' => $observaciones,
        ]);
        http_response_code(201);
        echo json_encode(['success' => true, 'message' => 'Registro de historia clínica agregado correctamente.']);
        break;

    /* ───── PUT: actualizar ───── */
    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);

        $id_historia = trim($data['id_historia'] ?? '');
        $fecha = trim($data['fecha'] ?? '');
        $diagnostico = trim($data['diagnostico'] ?? '');
        $tratamiento = trim($data['tratamiento'] ?? '');
        $observaciones = trim($data['observaciones'] ?? '');

        if (!$id_historia || !$fecha) {
            http_response_code(422);
            echo json_encode(['success' => false, 'error' => 'ID del registro y fecha son obligatorios.']);
            break;
        }

        $stmt = $pdo->prepare(
            "UPDATE historia_clinica
             SET fecha=:fecha, diagnostico=:diagnostico, tratamiento=:tratamiento, observaciones=:observaciones
             WHERE id_historia=:id_historia"
        );
        $stmt->execute([
            ':id_historia' => $id_historia,
            ':fecha' => $fecha,
            ':diagnostico' => $diagnostico,
            ':tratamiento' => $tratamiento,
            ':observaciones' => $observaciones,
        ]);

        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Registro de historia clínica no encontrado.']);
            break;
        }
        echo json_encode(['success' => true, 'message' => 'Registro actualizado correctamente.']);
        break;

    /* ───── DELETE: eliminar ───── */
    case 'DELETE':
        $data = json_decode(file_get_contents('php://input'), true);
        $id_historia = trim($data['id_historia'] ?? '');

        if (!$id_historia) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'ID del registro requerido.']);
            break;
        }

        $stmt = $pdo->prepare("DELETE FROM historia_clinica WHERE id_historia = :id_historia");
        $stmt->execute([':id_historia' => $id_historia]);

        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Registro de historia clínica no encontrado.']);
            break;
        }
        echo json_encode(['success' => true, 'message' => 'Registro eliminado correctamente.']);
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido.']);
        break;
}
?>
