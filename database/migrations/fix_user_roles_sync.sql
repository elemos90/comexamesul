-- Quick fix: Ensure all existing users have their roles in user_roles table
-- Execute this in phpMyAdmin

-- 1. Insert missing roles from users.role into user_roles
INSERT INTO user_roles (user_id, role, assigned_at)
SELECT id, role, created_at
FROM users
WHERE role IN ('vigilante', 'supervisor', 'membro', 'coordenador')
  AND id NOT IN (SELECT DISTINCT user_id FROM user_roles WHERE role = users.role)
ON DUPLICATE KEY UPDATE user_id = user_id;

-- 2. Verify your coordinator role
SELECT u.id, u.name, u.email, u.role as primary_role, 
       GROUP_CONCAT(ur.role ORDER BY ur.role) as all_roles
FROM users u
LEFT JOIN user_roles ur ON ur.user_id = u.id
WHERE u.role = 'coordenador'
GROUP BY u.id;
