<?php include '../partials/head.php'; ?>
<?php $_SESSION['menu_active'] = 'request'; ?>
<?php include '../partials/sidebar.php'; ?>

<h1>Solicitudes de Reparación</h1>
<?php
if($_SESSION['user']['active_profile']['name'] == 'admin') {
    $smtp = $conexion->prepare("
        SELECT rr.*, d.*, s.*, d.description as device_description, u.name as user_name, rr.id as repair_request_id FROM repair_requests rr 
        JOIN devices d ON d.id = rr.device_id
        JOIN statuses s ON s.id = rr.status_id
        JOIN users u ON u.id = d.user_id
    ");
} else {
    $smtp = $conexion->prepare("
        SELECT rr.*, d.*, s.*, d.description as device_description, u.name as user_name, rr.id as repair_request_id FROM repair_requests rr 
        JOIN devices d ON d.id = rr.device_id
        JOIN statuses s ON s.id = rr.status_id
        JOIN users u ON u.id = d.user_id
        WHERE u.id = ?
    ");
    $smtp->bindParam(1, $_SESSION['user']['id'], PDO::PARAM_INT);
}

$smtp->execute();
$requests = $smtp->fetchAll(PDO::FETCH_ASSOC);
$count = 0;
?>

<button id="btn-add-repair-request" class="btn btn-success">Nueva Solicitud</button>

<table class="table table-hover align-middle">
    <thead>
        <tr>
            <th>N°</th>
            <th>Dispositivo</th>
            <th>Problema</th>
            <th>Cliente</th>
            <th>Fecha de ingreso</th>
            <th>Estado</th>
            <th class="text-center">Acciones</th>
        </tr>
    <tbody>
        <?php foreach ($requests as $request): ?>
            <?php $count++; ?>
            <tr>
                <td><?php echo $count; ?></td>
                <td><?php echo $request['device_description'] . ' ' . $request['model'] . ' ' . $request['brand']; ?></td>
                <td><?php echo $request['problem_description']; ?></td>
                <td><?php echo $request['user_name']; ?></td>
                <td><?php echo date('d/m/Y', strtotime($request['request_date'])); ?></td>
                <td>
                    <?php
                    $status = $request['name'];
                    $badgeClass = '';

                    switch ($status) {
                        case 'Enviado':
                            $badgeClass = 'bg-secondary';
                            break;
                        case 'Revisión':
                            $badgeClass = 'bg-warning text-dark';
                            break;
                        case 'Reparación':
                            $badgeClass = 'bg-primary';
                            break;
                        case 'Finalizado':
                            $badgeClass = 'bg-success';
                            break;
                        default:
                            $badgeClass = 'bg-light text-dark';
                    }
                    ?>
                    <span class="badge <?php echo $badgeClass; ?>">
                        <?php echo $status; ?>
                    </span>
                </td>

                <td class="text-center">
                    <?php if ($_SESSION['user']['active_profile']['name'] == 'admin'): ?>
                        <?php if ($request['name'] == 'Enviado' || $request['name'] == 'Revisión'): ?>
                            <button
                                class="btn btn-warning btn-sm btn-next-status"
                                title="Avanzar al siguiente estado"
                                data-request-id="<?php echo $request['repair_request_id']; ?>">
                                <i class="fas fa-arrow-right"></i>
                            </button>
                        <?php elseif ($request['name'] == 'Reparación'): ?>
                            <button
                                class="btn btn-success btn-sm btn-finish-request"
                                title="Finalizar solicitud"
                                data-request-id="<?php echo $request['repair_request_id']; ?>">
                                <i class="fas fa-check-circle"></i>
                            </button>
                        <?php elseif ($request['name'] == 'Finalizado'): ?>
                            <button
                                class="btn btn-primary btn-sm btn-view-diagnosis"
                                title="Ver diagnóstico"
                                data-request-id="<?php echo $request['repair_request_id']; ?>">
                                <i class="fas fa-eye"></i>
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if ($_SESSION['user']['active_profile']['name'] == 'client'): ?>
                        <button
                            class="btn btn-info btn-sm btn-view-diagnosis"
                            title="Ver solicitud"
                            data-request-id="<?php echo $request['repair_request_id']; ?>">
                            <i class="fas fa-eye"></i>
                        </button>

                        <?php if ($request['name'] == 'Enviado'): ?>
                            <button
                                class="btn btn-warning btn-sm btn-edit-request"
                                title="Editar solicitud"
                                data-request-id="<?php echo $request['repair_request_id']; ?>">
                                <i class="fas fa-edit"></i>
                            </button>

                            <button
                                class="btn btn-danger btn-sm btn-delete-request"
                                title="Eliminar solicitud"
                                data-request-id="<?php echo $request['repair_request_id']; ?>">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
    </thead>
</table>

<!-- Modal para ver solicitud -->
<div class="modal fade" id="viewRequestModal" tabindex="-1" aria-labelledby="viewRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="viewRequestModalLabel">Detalles de la Solicitud Finalizada</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <!-- Datos del Dispositivo -->
                <h6 class="mb-2">Información del Dispositivo</h6>
                <div class="mb-2"><strong>Marca:</strong> <span id="deviceBrand"></span></div>
                <div class="mb-2"><strong>Modelo:</strong> <span id="deviceModel"></span></div>
                <div class="mb-2"><strong>Descripción:</strong> <span id="deviceDescription"></span></div>

                <hr>

                <!-- Datos de la Solicitud -->
                <h6 class="mb-2">Información de la Solicitud</h6>
                <div class="mb-2"><strong>Fecha de Solicitud:</strong> <span id="requestDate"></span></div>
                <div class="mb-2"><strong>Descripción del Problema:</strong> <span id="problemDescription"></span></div>

                <hr>

                <!-- Datos del Diagnóstico -->
                <h6 class="mb-2">Diagnóstico</h6>
                <div class="mb-2"><strong>Diagnóstico:</strong> <span id="diagnosis"></span></div>
                <div class="mb-2"><strong>Diagnosticado por:</strong> <span id="diagnosedBy"></span></div>
                <div class="mb-2"><strong>Fecha del Diagnóstico:</strong> <span id="diagnosisDate"></span></div>
                <div class="mb-2"><strong>Solución Propuesta:</strong> <span id="solutionDescription"></span></div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>

        </div>
    </div>
</div>

<!-- Modal Crear Diagnóstico -->
<div class="modal fade" id="createDiagnosticModal" tabindex="-1" aria-labelledby="createDiagnosticModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="createDiagnosticModalLabel">Finalizar Solicitud - Crear Diagnóstico</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <form id="diagnosticForm">

                    <input type="hidden" name="repair_request_id" id="diagnostic_repair_request_id">
                    <input type="hidden" name="action" id="diagnostic_form_action" value="finish_repair_request">

                    <div class="mb-3">
                        <label for="diagnosis" class="form-label">Diagnóstico</label>
                        <textarea class="form-control" id="diagnosis_form" name="diagnosis" rows="3" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="diagnosis_date" class="form-label">Fecha de diagnóstico</label>
                        <input type="date" class="form-control" id="diagnosis_date_form" name="diagnosis_date" required>
                    </div>

                    <div class="mb-3">
                        <label for="solution_description" class="form-label">Descripción de la solución</label>
                        <textarea class="form-control" id="solution_description_form" name="solution_description" rows="3" required></textarea>
                    </div>

                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" form="diagnosticForm" class="btn btn-primary">Guardar diagnóstico</button>
            </div>

        </div>
    </div>
</div>

<!-- Modal Confirmar Avance de Estado -->
<div class="modal fade" id="confirmAdvanceModal" tabindex="-1" aria-labelledby="confirmAdvanceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="confirmAdvanceModalLabel">Confirmar acción</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <form id="advanceStateForm">

                    <input type="hidden" name="repair_request_id" id="advance_repair_request_id">

                    <p>¿Está seguro de que desea avanzar esta solicitud al siguiente estado?</p>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" form="advanceStateForm" class="btn btn-success">Sí, avanzar</button>
            </div>

        </div>
    </div>
</div>

<!-- Modal Agregar Solicitud de Reparación -->
<div class="modal fade" id="createRepairRequestModal" tabindex="-1" aria-labelledby="addRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="addRequestModalLabel">Nueva Solicitud de Reparación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <form id="createRequestForm">
                    <!-- Si es admin selecciono un usuario, sino se guarda automáticamente el authenticado -->
                    <?php if ($_SESSION['user']['active_profile']['name'] == 'admin'): ?>
                        <div class="mb-3">
                            <label for="user_id" class="form-label">Cliente</label>
                            <select class="form-select" name="user_id" id="user_id_select" required>
                                <option value="">Seleccione un cliente</option>
                                <?php
                                $stmt = $conexion->prepare("SELECT id, name FROM users WHERE id IN (SELECT user_id FROM user_profiles WHERE profile_id = (SELECT id FROM profiles WHERE name = 'client'))");
                                $stmt->execute();
                                $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($clients as $client) {
                                    echo "<option value='{$client['id']}'>{$client['name']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    <?php else: ?>
                        <input type="hidden" name="user_id" value="<?php echo $_SESSION['user']['id']; ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="device_id" class="form-label">Equipo</label>
                        <select class="form-select" name="device_id" id="device_id_select" required>
                            <!-- Aquí se cargan las opciones mediante JS ajax cuando es admin -->
                            <option value="">Seleccione un dispositivo</option>
                            <?php if ($_SESSION['user']['active_profile']['name'] == 'client'): ?>
                                <?php
                                $stmt = $conexion->prepare("SELECT * FROM devices WHERE user_id = ?");
                                $stmt->execute([$_SESSION['user']['id']]);
                                $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($devices as $device) {
                                    echo "<option value='{$device['id']}'>{$device['description']} - {$device['brand']} {$device['model']}</option>";
                                }
                                ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="problem_description" class="form-label">Descripción del problema</label>
                        <textarea class="form-control" name="problem_description" id="problem_description" rows="3" required></textarea>
                    </div>

                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" form="createRequestForm" class="btn btn-primary">Crear Solicitud</button>
            </div>

        </div>
    </div>
</div>

<!-- Modal Editar Solicitud de Reparación -->
<div class="modal fade" id="editRepairRequestModal" tabindex="-1" aria-labelledby="editRepairRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editRepairRequestForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="editRepairRequestModalLabel">Editar Solicitud de Reparación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="repair_request_id" id="edit_repair_request_id">

                    <div class="mb-3">
                        <label for="edit_device_id" class="form-label">Equipo</label>
                        <select class="form-select" name="device_id" id="edit_device_id_select" required>
                            <option value="">Seleccione un equipo</option>
                            <?php
                            $stmt = $conexion->prepare("SELECT * FROM devices WHERE user_id = ?");
                            $stmt->execute([$_SESSION['user']['id']]);
                            $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($devices as $device) {
                                echo "<option value='{$device['id']}'>{$device['description']} - {$device['brand']} {$device['model']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="edit_problem_description" class="form-label">Descripción del problema</label>
                        <textarea class="form-control" name="problem_description" id="edit_problem_description" rows="4" required></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Confirmar Eliminación -->
<div class="modal fade" id="deleteRequestModal" tabindex="-1" aria-labelledby="deleteRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="deleteRequestForm">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteRequestModalLabel">Confirmar Eliminación</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p>¿Está seguro de que desea eliminar esta solicitud de reparación? Esta acción no se puede deshacer.</p>
                    <input type="hidden" name="repair_request_id" id="delete_repair_request_id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="../../public/js/repair_request.js"></script>

<?php include '../partials/footer.php'; ?>