<?php
function sanitizeInput($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function validatePassword($password)
{
    return strlen($password) >= 8;
}

function hashPassword($password)
{
    return password_hash($password, PASSWORD_DEFAULT);
}
