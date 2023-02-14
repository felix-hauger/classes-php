<?php

require_once 'class/DbConnection.php';
require_once 'class/UserPDO.php';

session_start();

$logout_user = new UserPDO();

$logout_user->disconnect();
session_unset();
header('location: login.php');
die();