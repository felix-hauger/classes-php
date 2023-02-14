<?php


require_once 'class/UserPDO.php';

session_start();

$logged_user = new UserPDO();

if ($logged_user->getAllInfos()) {
    $firstname = $logged_user->getFirstname();
}


?>

<h1>Bonjour <?= isset($firstname) ? $firstname : 'invité' ?></h1>