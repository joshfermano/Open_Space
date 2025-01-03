<?php
require_once '../../config/config.php';

session_start();
session_destroy();
header('Location: /openspace/src/index.php');
exit;
