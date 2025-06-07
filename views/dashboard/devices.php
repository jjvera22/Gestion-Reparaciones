<?php 
include_once ('../../includes/db.php');
include('../partials/head.php'); 
$_SESSION['menu_active'] = 'devices';
include('../partials/sidebar.php');

if($_POST && isset($_POST["action"])){
    $action = $_POST["action"];
    
    // ACCION AGREGAR DISPOSITIVO
    if($action == 'add_device'){
        $brand = $_POST['brand'];
        $model = $_POST['model'];
        $description = $_POST['description'];
        $user_id = $_SESSION['user']['id'];

        $stmt = $conexion->prepare("INSERT INTO devices (brand, model, description, user_id) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$brand, $model, $description, $user_id])) {
            echo '<script>alert("Dispositivo Guardado Correctamente");</script>';
        } else {
            echo '<script>alert("Error al guardar el dispositivo");</script>';
        }
    }
    
    // ACCION EDITAR DISPOSITIVO
    else if($action == 'edit_device'){
        $id = $_POST['id'];
        $brand = $_POST['brand'];
        $model = $_POST['model'];
        $description = $_POST['description'];

        $stmt = $conexion->prepare("UPDATE devices SET brand = ?, model = ?, description = ? WHERE id = ?");
        if ($stmt->execute([$brand, $model, $description, $id])) {
            echo '<script>alert("Dispositivo Actualizado Correctamente");</script>';
        } else {
            echo '<script>alert("Error al actualizar el dispositivo");</script>';
        }
    }
    
    // ACCION ELIMINAR DISPOSITIVO
    else if($action == 'delete_device'){
        $id = $_POST['id'];
        
        // Verificar que el dispositivo pertenece al usuario
        $stmt = $conexion->prepare("SELECT id FROM devices WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $_SESSION['user']['id']]);
        
        if ($stmt->rowCount() > 0) {
            $stmt = $conexion->prepare("DELETE FROM devices WHERE id = ?");
            if ($stmt->execute([$id])) {
                echo '<script>alert("Dispositivo Eliminado Correctamente");</script>';
            } else {
                echo '<script>alert("Error al eliminar el dispositivo");</script>';
            }
        } else {
            echo '<script>alert("No tienes permiso para eliminar este dispositivo");</script>';
        }
    }
}
?>

<div class='content-wrapper'>
    <div class='content-header'>
        <div class='container-fluid'>
            <div class='row mb-2'>
                <div class='col-sm-6'>
                    <h1 class='m-0'>Mis Dispositivos</h1>
                </div>
            </div>
        </div>
    </div>

    <section class='content'>
        <div class='container-fluid'>
            <div class='row'>
                <div class='col-md-4'>
                    <div class='card'>
                        <div class='card-header'>
                            <h3 class='card-title'>Agregar Dispositivo</h3>
                        </div>
                        <div class='card-body'>
                            <form action="devices.php" method="post">
                                <div class="mb-3">
                                    <label for="brand" class="form-label">Marca</label>
                                    <input type="text" class="form-control" name="brand" required>
                                </div>
                                <div class="mb-3">
                                    <label for="model" class="form-label">Modelo</label>
                                    <input type="text" class="form-control" name="model" required>
                                </div>
                                <div class="mb-3">
                                    <label for="description" class="form-label">Descripción</label>
                                    <textarea class="form-control" name="description" rows="3"></textarea>
                                </div>
                                <input type="hidden" name="action" value="add_device">
                                <button type="submit" class="btn btn-primary">Guardar</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class='col-md-8'>
                    <div class='card'>
                        <div class='card-body'>
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Marca</th>
                                        <th>Modelo</th>
                                        <th>Descripción</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                        $stmt = $conexion->prepare("SELECT * FROM devices WHERE user_id = ?");
                                        $stmt->execute([$_SESSION['user']['id']]);
                                        $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                        foreach ($devices as $device) {
                                            echo "
                                            <tr>
                                                <td>{$device['id']}</td>
                                                <td>{$device['brand']}</td>
                                                <td>{$device['model']}</td>
                                                <td>{$device['description']}</td>
                                                <td>
                                                    <form action='devices.php' method='post' style='display:inline;'>
                                                        <input type='hidden' name='action' value='delete_device'>
                                                        <input type='hidden' name='id' value='{$device['id']}'>
                                                        <button type='submit' class='btn btn-danger btn-sm'>Eliminar</button>
                                                    </form>
                                                    <button class='btn btn-warning btn-sm' data-bs-toggle='modal' data-bs-target='#editModal' 
                                                            onclick='editDevice(\"{$device['id']}\", \"{$device['brand']}\", \"{$device['model']}\", \"{$device['description']}\")'>
                                                        Editar
                                                    </button>
                                                </td>
                                            </tr>
                                            ";
                                        }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal para editar dispositivo -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Editar Dispositivo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="devices.php" method="post">
                    <div class="mb-3">
                        <label for="edit_brand" class="form-label">Marca</label>
                        <input type="text" class="form-control" id="edit_brand" name="brand" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_model" class="form-label">Modelo</label>
                        <input type="text" class="form-control" id="edit_model" name="model" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Descripción</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>
                    <input type="hidden" name="action" value="edit_device">
                    <input type="hidden" id="edit_id" name="id">
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function editDevice(id, brand, model, description) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_brand').value = brand;
    document.getElementById('edit_model').value = model;
    document.getElementById('edit_description').value = description;
}
</script>

<?php include '../partials/footer.php'; ?>