<?php include '../partials/head.php'; ?>
<?php $_SESSION['menu_active'] = 'diagnostics';?>
<?php include '../partials/sidebar.php'; ?>

    <h1>Diagnósticos</h1>

    <?php
    $smtp = $conexion->prepare("
            SELECT  rr.*, diag.*,
                    rr.id as repair_request_id,
                    u.name as user_name
            FROM diagnostics diag
            JOIN repair_requests rr ON rr.id = diag.repair_request_id
            JOIN users u ON u.id = diag.diagnosed_by
    ");
    $smtp->execute();
    $diagnosticos = $smtp->fetchAll(PDO::FETCH_ASSOC);
    
    $cont=0;
    ?>

    <table class="table table-hover align-middle">
        <thead>            
            <tr>
                <th>N°</th>
                <th>Diagnostico</th>
                <th>Diagnosticado por</th>
                <th>Fecha de diagnostico</th>
                <th>Solucion</th>
                <th>Solicitud de reparacion</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($diagnosticos as $fila):?>
                <?php $cont++;?>
                <tr>
                    <td><?php echo $cont;?></td>
                    <td><?php echo $fila['diagnosis'];?></td>
                    <td><?php echo $fila['user_name']?></td>
                    <td><?php echo $fila['diagnosis_date']?></td>
                    <td><?php echo $fila['solution_description']?></td>
                    <td><?php echo $fila['repair_request_id']?></td>
                    <td></td>
                </tr>
            <?php endforeach;?>
        </tbody>

    </table>

    <script src="../../public/js/diagnostics.js" ></script>

<?php include '../partials/footer.php'; ?>