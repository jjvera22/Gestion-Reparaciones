<?php
include_once '/includes/db.php';

switch ($POST['action']) {
    case 'login':
        $email = $_POST['email'];
        $password = $_POST['password'];

        // Busco al usuario por email
        $smtp = $conexion->prepare("SELECT * FROM users WHERE email = ?");
        $smtp->execute([$email]);
        $user = $smtp->fetch(PDO::FETCH_ASSOC);

        // Verifico si el usuario existe y si la contraseña es correcta
        if ($user && password_verify($password, $user['password'])) {
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];

            // Consulto perfiles del usuario
            $stmt = $conexion->prepare("
                SELECT p.id, p.name
                FROM user_profiles up
                JOIN profiles p ON p.id = up.profile_id
                WHERE up.user_id = ?
            ");
            $stmt->execute([$user['id']]);
            $profiles = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($profiles) == 1) {
                $_SESSION['profile_id'] = $profiles[0]['id'];
                $_SESSION['profile_name'] = $profiles[0]['name'];

                echo json_encode([
                    'status' => 'success',
                    'redirect' => '/views/dashboard/home.php'
                ]);
            } else {
                $_SESSION['available_profiles'] = $profiles;

                echo json_encode([
                    'status' => 'success',
                    'redirect' => '/views/auth/select-profile.php'
                ]);
            }
            
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Las credenciales son incorrectas']);
        }
        
        break;
    default:
        # code...
        break;
}