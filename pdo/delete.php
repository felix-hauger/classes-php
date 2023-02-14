<?php

require_once 'class/UserPDO.php';

session_start();

$delete_user = new UserPDO();

$delete_user->delete();
header('location: index.php');
die();