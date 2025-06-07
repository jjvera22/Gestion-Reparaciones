<?php
include_once '../includes/db.php';
include_once '../includes/AuthMiddleware.php';

header('Content-Type: application/json');

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        // Obtener dispositivos
        if (isset($_GET['user_id'])) {
            $userId = $_GET['user_id'];
            $stmt = $conexion->prepare("SELECT * FROM devices WHERE user_id = ?");
            $stmt->execute([$userId]);
            $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'status' => 'success',
                'data' => $devices
            ]);
        } elseif (isset($_GET['id'])) {
            $deviceId = $_GET['id'];
            $stmt = $conexion->prepare("SELECT * FROM devices WHERE id = ?");
            $stmt->execute([$deviceId]);
            $device = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($device) {
                echo json_encode([
                    'status' => 'success',
                    'data' => $device
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Dispositivo no encontrado'
                ]);
            }
        } else {
            // Solo administradores pueden ver todos los dispositivos
            if ($_SESSION['user']['active_profile']['name'] === 'admin') {
                $stmt = $conexion->prepare("SELECT * FROM devices");
                $stmt->execute();
                $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode([
                    'status' => 'success',
                    'data' => $devices
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'No autorizado'
                ]);
            }
        }
        break;

    case 'POST':
        // Crear o actualizar dispositivo
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (isset($data['id'])) {
            // Actualizar dispositivo
            $stmt = $conexion->prepare("UPDATE devices SET brand = ?, model = ?, description = ? WHERE id = ? AND user_id = ?");
            $result = $stmt->execute([
                $data['brand'],
                $data['model'],
                $data['description'],
                $data['id'],
                $_SESSION['user']['id']
            ]);
            
            if ($result) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Dispositivo actualizado correctamente'
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Error al actualizar el dispositivo'
                ]);
            }
        } else {
            // Crear nuevo dispositivo
            $stmt = $conexion->prepare("INSERT INTO devices (brand, model, description, user_id) VALUES (?, ?, ?, ?)");
            $result = $stmt->execute([
                $data['brand'],
                $data['model'],
                $data['description'],
                $_SESSION['user']['id']
            ]);
            
            if ($result) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Dispositivo creado correctamente',
                    'id' => $conexion->lastInsertId()
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Error al crear el dispositivo'
                ]);
            }
        }
        break;

    case 'DELETE':
        // Eliminar dispositivo
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (isset($data['id'])) {
            // Verificar que el dispositivo pertenece al usuario o es admin
            $stmt = $conexion->prepare("SELECT user_id FROM devices WHERE id = ?");
            $stmt->execute([$data['id']]);
            $device = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($device && ($device['user_id'] == $_SESSION['user']['id'] || $_SESSION['user']['active_profile']['name'] === 'admin')) {
                $stmt = $conexion->prepare("DELETE FROM devices WHERE id = ?");
                $result = $stmt->execute([$data['id']]);
                
                if ($result) {
                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Dispositivo eliminado correctamente'
                    ]);
                } else {
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Error al eliminar el dispositivo'
                    ]);
                }
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'No autorizado o dispositivo no encontrado'
                ]);
            }
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'ID de dispositivo no proporcionado'
            ]);
        }
        break;

    default:
        echo json_encode([
            'status' => 'error',
            'message' => 'Método no permitido'
        ]);
        break;
}