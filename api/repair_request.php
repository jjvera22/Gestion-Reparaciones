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
                    JOIN diagnostics di ON rr.id = di.repair_request_id
                    JOIN users u_diagnosed ON u_diagnosed.id = di.diagnosed_by
                    WHERE rr.id = ?
            ");
        $stmt->execute([$repairRequestId]);
        $repairRequest = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'status' => 'success',
            'data' => $repairRequest
        ]);

        break;

    default:
        # code...
        break;
}
