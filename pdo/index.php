<?php


require_once 'class/UserPDO.php';

session_start();

$logged_user = new UserPDO();

$logged_user->getAllInfos();

?>

<h1>Bonjour <?= $logged_user->getFirstname() ?></h1>