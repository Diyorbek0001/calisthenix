<?php

declare(strict_types=1);

require_once __DIR__ . '/../controllers/authController.php';

logoutUser();

header('Location: login.php');
exit;
