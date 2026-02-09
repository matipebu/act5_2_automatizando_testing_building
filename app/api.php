<?php
// app/api.php - API REST para contactos

header('Content-Type: application/json');
require_once __DIR__ . '/config/database.php';

$db = (new Database())->pdo();
$action = $_GET['action'] ?? 'list';

function jsonError(string $msg, int $code = 400): void {
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $msg]);
    exit;
}

try {
    switch ($action) {
        case 'list':
            $stmt = $db->query('SELECT * FROM contacts ORDER BY created_at DESC');
            echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
            break;

        case 'add':
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            if ($name === '' || $email === '' || $phone === '') {
                jsonError('Campos name, email y phone son requeridos');
            }
            $stmt = $db->prepare('INSERT INTO contacts (name, email, phone) VALUES (?, ?, ?)');
            $stmt->execute([$name, $email, $phone]);
            echo json_encode(['success' => true, 'id' => $db->lastInsertId()]);
            break;

        case 'delete':
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) {
                jsonError('ID requerido');
            }
            $stmt = $db->prepare('DELETE FROM contacts WHERE id = ?');
            $stmt->execute([$id]);
            echo json_encode(['success' => true]);
            break;

        default:
            jsonError('Acción no válida');
    }
} catch (Throwable $e) {
    jsonError($e->getMessage(), 500);
}
