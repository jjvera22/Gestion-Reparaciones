<?php include '../partials/head.php'; ?>
<?php $_SESSION['menu_active'] = 'request'; ?>
<?php include '../partials/sidebar.php'; ?>

<h1>Solicitudes de Reparación</h1>
<?php
$smtp = $conexion->prepare("
            SELECT rr.*, d.*, s.*, d.description as device_description, u.name as user_name, rr.id as repair_request_id FROM repair_requests rr 
            JOIN devices d ON d.id = rr.device_id
            JOIN statuses s ON s.id = rr.status_id
            JOIN users u ON u.id = d.user_id
        ");
$smtp->execute();
$requests = $smtp->fetchAll(PDO::FETCH_ASSOC);
//echo '<pre>';
//var_dump($requests);
//echo '</pre>';
$count = 0;
?>

<?php if ($_SESSION['user']['active_profile']['name'] == 'client'): ?>
    <div class="my-3">
        <button id="btn-add-repair-request" class="btn btn-success">Nueva Solicitud</a>
    </div>
<?php endif; ?>

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

                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
    </thead>
</table>

<!-- Modal para ver solicitud finalizada -->
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
          <input type="hidden" name="action" id="diagnostic_action" value="finish_repair_request">
          
          <div class="mb-3">
            <label for="diagnosis" class="form-label">Diagnóstico</label>
            <textarea class="form-control" id="diagnosis_form" name="diagnosis" rows="3" required></textarea>
          </div>
          
          <div class="mb-3">
            <label for="diagnosed_by" class="form-label">Diagnosticado por</label>
            <select class="form-select" id="diagnosed_by_select" name="diagnosed_by" required>
                <option value="" disabled selected>Seleccione un técnico</option>
                <?php
                $stmt = $conexion->prepare("SELECT u.id, u.name FROM users u JOIN user_profiles up ON up.user_id = u.id JOIN profiles p ON p.id = up.profile_id WHERE p.name = 'admin'");
                $stmt->execute();
                $technicians = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                foreach ($technicians as $technician) {
                    echo '<option value="' . $technician['id'] . '">' . $technician['name'] . '</option>';
                }
                ?>
            </select>
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


<script src="../../public/js/repair_request.js"></script>

<?php include '../partials/footer.php'; ?>