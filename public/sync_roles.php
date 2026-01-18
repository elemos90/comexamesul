<!DOCTYPE html>
<html>

<head>
    <title>User Management - Quick Role Sync</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
        }

        .success {
            color: green;
            font-weight: bold;
        }

        .error {
            color: red;
            font-weight: bold;
        }

        pre {
            background: #f4f4f4;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
        }
    </style>
</head>

<body>
    <h1>User Management - Role Sync Utility</h1>
    <p>This script synchronizes existing user roles from <code>users.role</code> to <code>user_roles</code> table.</p>

    <?php
    require_once __DIR__ . '/../bootstrap.php';

    try {
        $db = database();

        // Sync all existing roles
        $stmt = $db->prepare("
            INSERT INTO user_roles (user_id, role, assigned_at)
            SELECT id, role, created_at
            FROM users
            WHERE role IN ('vigilante', 'supervisor', 'membro', 'coordenador')
              AND id NOT IN (
                  SELECT DISTINCT user_id 
                  FROM user_roles 
                  WHERE role = users.role
              )
            ON DUPLICATE KEY UPDATE user_id = user_id
        ");

        $stmt->execute();
        $synced = $stmt->rowCount();

        echo "<div class='success'>";
        echo "<h2>✅ Success!</h2>";
        echo "<p>Synchronized {$synced} user role(s) to user_roles table.</p>";
        echo "</div>";

        // Show all users and their roles
        $users = $db->query("
            SELECT u.id, u.name, u.email, u.role as primary_role,
                   GROUP_CONCAT(ur.role ORDER BY ur.role) as all_roles
            FROM users u
            LEFT JOIN user_roles ur ON ur.user_id = u.id
            WHERE u.role IN ('coordenador', 'membro', 'supervisor', 'vigilante')
            GROUP BY u.id
            ORDER BY u.name
        ")->fetchAll(PDO::FETCH_ASSOC);

        echo "<h3>Current User Roles:</h3>";
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Primary Role</th><th>All Roles</th></tr>";

        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>{$user['id']}</td>";
            echo "<td>{$user['name']}</td>";
            echo "<td>{$user['email']}</td>";
            echo "<td><strong>{$user['primary_role']}</strong></td>";
            echo "<td>" . ($user['all_roles'] ?? '<em>none</em>') . "</td>";
            echo "</tr>";
        }

        echo "</table>";

        echo "<h3>✅ Next Steps:</h3>";
        echo "<ol>";
        echo "<li><strong>Logout</strong> of the system</li>";
        echo "<li><strong>Login</strong> again</li>";
        echo "<li>Access <strong>Gestão de Utilizadores</strong> from the sidebar</li>";
        echo "</ol>";

    } catch (Exception $e) {
        echo "<div class='error'>";
        echo "<h2>❌ Error</h2>";
        echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
        echo "</div>";
    }
    ?>

    <hr>
    <p><a href="<?= url('/dashboard') ?>">← Back to Dashboard</a></p>
</body>

</html>