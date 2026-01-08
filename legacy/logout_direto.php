<?php
/**
 * LOGOUT DIRETO
 */

session_start();
session_destroy();

header('Location: login_direto.php');
exit;
