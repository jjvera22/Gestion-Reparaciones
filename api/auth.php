<?php
include_once '../includes/db.php';

switch ($_POST['action']) {
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

            // Consulto perfiles del usuario
            $stmt = $conexion->prepare("
                SELECT p.id, p.name, p.description
                FROM user_profiles up
                JOIN profiles p ON p.id = up.profile_id
                WHERE up.user_id = ?
            ");
            $stmt->execute([$user['id']]);
            $profiles = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $_SESSION['user'] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'profiles' => $profiles,
            ];

            if (count($profiles) == 1) {
                $_SESSION['user']['active_profile'] = $profiles[0];

                echo json_encode([
                    'status' => 'success',
                    'redirect' => '/GestionTaller/views/dashboard/home.php'
                ]);
            } else {
                $_SESSION['available_profiles'] = $profiles;

                echo json_encode([
                    'status' => 'success',
                    'redirect' => '/GestionTaller/views/auth/select-profile.php'
                ]);
            }
            
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Las credenciales son incorrectas']);
        }
        
        break;

    case 'register':
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $confirm_password = $_POST['confirmPassword'];
        $phone = $_POST['phone'];
        $address = $_POST['address'];

        $conexion->beginTransaction();

        $stmt = $conexion->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['status' => 'error', 'message' => 'El correo electrónico ya está registrado']);
            exit;
        }

        // Insertar el nuevo usuario
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conexion->prepare("INSERT INTO users (name, email, password, phone, address) VALUES (?, ?, ?, ?, ?)");
        
        if ($stmt->execute([$name, $email, $hashed_password, $phone, $address])) {
            $userId = $conexion->lastInsertId();

            $stmt = $conexion->prepare("SELECT p.id FROM profiles p WHERE p.name = 'client'");
            $stmt->execute();
            $profileId = $stmt->fetch(PDO::FETCH_ASSOC);

            $stmt = $conexion->prepare("INSERT INTO user_profiles (user_id, profile_id) VALUES (?, ?)");
            $stmt->execute([$userId, $profileId['id']]);

            $conexion->commit();
            echo json_encode(['status' => 'success', 'message' => 'Usuario registrado exitosamente', 'redirect' => '../../index.php']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al registrar el usuario']);
        }
        
        break;
    case 'selectProfile':
        $profileId = $_POST['profileId'];
        session_start();
        $stmt = $conexion->prepare("SELECT * FROM profiles WHERE id = ?");
        $stmt->execute([$profileId]);
        $profile = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($profile) {
            $_SESSION['user']['active_profile'] = $profile;
            echo json_encode(['status' => 'success', 'redirect' => '/GestionTaller/views/dashboard/home.php']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Perfil no encontrado']);
        }
        break;

    case 'logout':
        session_start();
        session_destroy();
        echo json_encode(['status' => 'success', 'redirect' => '/GestionTaller/index.php']);
        break;
        
    default:
        # code...
        break;
}