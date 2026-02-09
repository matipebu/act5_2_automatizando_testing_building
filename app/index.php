<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda de Contactos</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f5f5f5; }
        .container { max-width: 900px; margin: 40px auto; padding: 0 20px; }
        .card { background: #fff; border-radius: 8px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); padding: 20px; }
        h1 { margin-bottom: 10px; }
        .status { margin: 10px 0; padding: 12px; border-radius: 6px; }
        .success { background: #e6ffed; color: #0f5132; border: 1px solid #b6f4c0; }
        .error { background: #ffe6e6; color: #842029; border: 1px solid #f5c2c7; }
        form { display: flex; gap: 10px; margin: 20px 0; flex-wrap: wrap; }
        input { padding: 10px; border: 1px solid #ddd; border-radius: 6px; flex: 1; min-width: 180px; }
        button { background: #007bff; color: #fff; border: none; padding: 10px 16px; border-radius: 6px; cursor: pointer; }
        button:hover { background: #0056b3; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 10px; border-bottom: 1px solid #eee; text-align: left; }
    </style>
</head>
<body>
<div class="container">
    <div class="card">
        <h1>📇 Agenda de Contactos</h1>
        <p>API simple en PHP + Docker.</p>
        <?php
        require_once __DIR__ . '/config/database.php';
        $db = (new Database())->pdo();
        $msg = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'], $_POST['email'], $_POST['phone'])) {
            $stmt = $db->prepare('INSERT INTO contacts (name, email, phone) VALUES (?, ?, ?)');
            $stmt->execute([
                trim($_POST['name']),
                trim($_POST['email']),
                trim($_POST['phone'])
            ]);
            $msg = 'Contacto añadido';
        }

        if (isset($_GET['del'])) {
            $stmt = $db->prepare('DELETE FROM contacts WHERE id = ?');
            $stmt->execute([(int)$_GET['del']]);
            $msg = 'Contacto eliminado';
        }

        $contacts = $db->query('SELECT * FROM contacts ORDER BY created_at DESC')->fetchAll();
        ?>

        <?php if ($msg): ?>
            <div class="status success">✅ <?php echo htmlspecialchars($msg); ?></div>
        <?php endif; ?>

        <form method="POST">
            <input name="name" placeholder="Nombre" required>
            <input name="email" placeholder="Email" required type="email">
            <input name="phone" placeholder="Teléfono" required>
            <button type="submit">Añadir</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Teléfono</th>
                    <th>Creado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($contacts as $c): ?>
                <tr>
                    <td><?php echo htmlspecialchars($c['name']); ?></td>
                    <td><?php echo htmlspecialchars($c['email']); ?></td>
                    <td><?php echo htmlspecialchars($c['phone']); ?></td>
                    <td><?php echo htmlspecialchars($c['created_at']); ?></td>
                    <td><a href="?del=<?php echo (int)$c['id']; ?>" onclick="return confirm('Eliminar contacto?');">Eliminar</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
