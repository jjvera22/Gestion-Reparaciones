<?php
include_once '../includes/db.php';

switch ($_POST['action']) {
    case 'view_repair_request_finished':
        $repairRequestId = $_POST['repair_request_id'];
        $stmt = $conexion->prepare("
                SELECT 
                    rr.id as repair_request_id,
                    d.description as device_description, 
                    d.model, 
                    d.brand, 
                    s.name as status_name, 
                    u_device.name as user_name, 
                    rr.problem_description, 
                    rr.request_date, 
                    di.diagnosis, 
                    di.solution_description, 
                    di.diagnosis_date,
                    u_diagnosed.name as user_diagnosed_name
                FROM repair_requests rr 
                    JOIN devices d ON d.id = rr.device_id
                    JOIN statuses s ON s.id = rr.status_id
                    JOIN users u_device ON u_device.id = d.user_id
                    LEFT JOIN diagnostics di ON rr.id = di.repair_request_id
                    LEFT JOIN users u_diagnosed ON u_diagnosed.id = di.diagnosed_by
                    WHERE rr.id = ?
            ");
        $stmt->execute([$repairRequestId]);
        $repairRequest = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'status' => 'success',
            'data' => $repairRequest
        ]);

        break;

    case 'finish_repair_request':
        session_start();
        $repairRequestId = $_POST['repair_request_id'];
        $diagnosis = $_POST['diagnosis_form'];
        $solutionDescription = $_POST['solution_description_form'];
        $diagnosedBy = $_SESSION['user']['id'];
        $diagnosisDate = $_POST['diagnosis_date_form'];

        $stmt = $conexion->prepare("SELECT id FROM statuses WHERE name = 'Finalizado'");
        $stmt->execute();
        $statusId = $stmt->fetchColumn();

        $stmt = $conexion->prepare("UPDATE repair_requests SET status_id = ? WHERE id = ?");
        $stmt->execute([$statusId, $repairRequestId]);

        $stmt = $conexion->prepare("
            INSERT INTO diagnostics (repair_request_id, diagnosis, solution_description, diagnosed_by, diagnosis_date)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$repairRequestId, $diagnosis, $solutionDescription, $diagnosedBy, $diagnosisDate]);

        echo json_encode([
            'status' => 'success',
            'message' => 'Solicitud de reparación finalizada correctamente.'
        ]);

        break;

    case 'advance_repair_request_status':
        $repairRequestId = $_POST['repair_request_id'];
        $stmt = $conexion->prepare("SELECT status_id FROM repair_requests WHERE id = ?");
        $stmt->execute([$repairRequestId]);
        $currentStatusId = $stmt->fetchColumn();

        $stmt = $conexion->prepare("SELECT * FROM statuses WHERE id = ?");
        $stmt->execute([$currentStatusId]);
        $currentStatus = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($currentStatus['name'] == 'Enviado'){
            $stmt = $conexion->prepare("SELECT id FROM statuses WHERE name = 'Revisión'");
            $stmt->execute();
            $newStatusId = $stmt->fetchColumn();

            $stmt = $conexion->prepare("UPDATE repair_requests SET status_id = ? WHERE id = ?");
            $stmt->execute([$newStatusId, $repairRequestId]);

            echo json_encode([
                'status' => 'success',
                'message' => 'Solicitud de reparación actualizada a "Revisión".'
            ]);
            
        } elseif ($currentStatus['name'] == 'Revisión') {
            $stmt = $conexion->prepare("SELECT id FROM statuses WHERE name = 'Reparación'");
            $stmt->execute();
            $newStatusId = $stmt->fetchColumn();

            $stmt = $conexion->prepare("UPDATE repair_requests SET status_id = ? WHERE id = ?");
            $stmt->execute([$newStatusId, $repairRequestId]);

            echo json_encode([
                'status' => 'success',
                'message' => 'Solicitud de reparación actualizada a "Reparación".'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'La solicitud de reparación no se puede avanzar desde el estado actual.'
            ]);
        }

        break;

    case 'get_user_devices':
        $userId = $_POST['user_id'];
        $stmt = $conexion->prepare("SELECT * FROM devices WHERE user_id = ?");
        $stmt->execute([$userId]);
        $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'status' => 'success',
            'data' => $devices
        ]);
        
        break;

    case 'create_repair_request':
        $deviceId = $_POST['device_id'];
        $problemDescription = $_POST['problem_description'];
        $requestDate = date('Y-m-d');

        $stmt = $conexion->prepare("SELECT id FROM statuses WHERE name = 'Enviado'");
        $stmt->execute();
        $statusId = $stmt->fetchColumn();

        $stmt = $conexion->prepare("INSERT INTO repair_requests (request_date, device_id, problem_description, status_id) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$requestDate, $deviceId, $problemDescription, $statusId])) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Solicitud de reparación creada correctamente.'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Error al crear la solicitud de reparación.'
            ]);
        }

        break;

    case 'get_repair_request':
        $repairRequestId = $_POST['repair_request_id'];

        $stmt = $conexion->prepare("
            SELECT rr.id as repair_request_id, rr.request_date, rr.problem_description, d.description as device_description, d.model, d.brand, s.name as status_name, d.id as device_id
            FROM repair_requests rr
            JOIN devices d ON d.id = rr.device_id
            JOIN statuses s ON s.id = rr.status_id
            WHERE rr.id = ?
        ");
        $stmt->execute([$repairRequestId]);
        $repairRequest = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($repairRequest) {
            echo json_encode([
                'status' => 'success',
                'data' => $repairRequest
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Solicitud de reparación no encontrada.'
            ]);
        }

        break;

    case 'edit_repair_request':
        $repairRequestId = $_POST['repair_request_id'];
        $problemDescription = $_POST['problem_description'];
        $deviceId = $_POST['device_id'];

        $stmt = $conexion->prepare("UPDATE repair_requests SET problem_description = ?, device_id = ? WHERE id = ?");
        if ($stmt->execute([$problemDescription, $deviceId, $repairRequestId])) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Solicitud de reparación actualizada correctamente.'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Error al actualizar la solicitud de reparación.'
            ]);
        }

        break;

    case 'delete_repair_request':
        $repairRequestId = $_POST['repair_request_id'];

        $stmt = $conexion->prepare("DELETE FROM repair_requests WHERE id = ?");
        if ($stmt->execute([$repairRequestId])) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Solicitud de reparación eliminada correctamente.'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Error al eliminar la solicitud de reparación.'
            ]);
        }

        break;
    default:
        # code...
        break;
}
