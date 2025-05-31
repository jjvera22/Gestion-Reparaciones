<?php 
    $conexion = new mysqli("localhost","root", "", "taller_reparacion");

    if ($conexion->connect_error) {
        die("Connection failed: " . $conexion->connect_error);
    }