<?php

require_once 'UserPDO.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$login = 'tyty';
$password = 'tyty';

$user_pdo = new UserPDO($login, $password);


// var_dump($user_pdo);

try {
    if ($user = $user_pdo->update('test', 'toto')) {
        var_dump($user);
        echo 'Mise à jour réussie. Bienvenue ' . $user->_firstname . "\n";
    }
} catch (Exception $e) {
    echo 'Erreur : ' . $e->getMessage();
}