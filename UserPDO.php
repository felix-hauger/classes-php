<?php

require_once 'DbConnection.php';

class UserPDO extends DbConnection
{

}

$user_pdo = new UserPDO();

var_dump($user_pdo);