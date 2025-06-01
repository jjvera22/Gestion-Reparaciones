<div class="wrapper">
    <div class="sidebar p-3">
        <h4>Menú</h4>

        <a class="<?php if($_SESSION['menu_active'] == 'home') echo 'active'; ?>" href="../dashboard/home.php">Inicio</a>

        <?php if($_SESSION['user']['active_profile']['name'] == 'client') { ?>
            <a class="<?php if($_SESSION['menu_active'] == 'devices') echo 'active'; ?>" href="../dashboard/devices.php">Mis dispositivos</a>
        <?php } ?>

        <a class="<?php if($_SESSION['menu_active'] == 'request') echo 'active'; ?>" href="../dashboard/repair_requests.php">Solicitudes de Reparación</a>
        
        <?php if($_SESSION['user']['active_profile']['name'] == 'admin') { ?>
            <a class="<?php if($_SESSION['menu_active'] == 'diagnostics') echo 'active'; ?>" href="../dashboard/diagnostics.php">Diagnósticos</a>
        <?php } ?>

        <a href="#" id="logout-btn">Cerrar sesión</a>
    </div>

    <div class="content-wrapper">
        <div class="main-content">