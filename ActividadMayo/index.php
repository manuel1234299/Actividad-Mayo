<?php

require_once __DIR__ . '/classes/Controller.php';
require_once __DIR__ . '/classes/View.php';

$controller = new Controller(__DIR__ . '/storage/users.json');
$view = new View();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $controller->register($_POST, $_FILES);
    if ($result['success']) {
        echo '<p>Registro completado. Usuario creado con éxito.</p>';
        exit;
    }

    $view->renderRegisterForm($_POST, $result['errors']);
    exit;
}

$view->renderRegisterForm();
