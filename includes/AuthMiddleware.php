<?php

session_start();

if (!isset($_SESSION['user'])) {
    header("Location: /GestionTaller/index.php");
    exit;
}