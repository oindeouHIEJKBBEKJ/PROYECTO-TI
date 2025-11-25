<?php
session_start();

function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

function redirectIfNotLogged()
{
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

function redirectIfLogged()
{
    if (isLoggedIn()) {
        header("Location: dashboard.php");
        exit();
    }
}

function getUserRole()
{
    return $_SESSION['user_role'] ?? null;
}

function isAdmin()
{
    return getUserRole() === 'admin';
}

function isDocente()
{
    return getUserRole() === 'docente';
}

function isEgresado()
{
    return getUserRole() === 'egresado';
}

function isTI()
{
    return getUserRole() === 'ti';
}
?>