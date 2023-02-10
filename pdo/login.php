<?php


require_once 'class/UserPDO.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$login = 'toto';
$password = 'toto';

$user_pdo = new UserPDO($login, $password);


var_dump($user_pdo);

try {
    if ($user = $user_pdo->connect()) {
        var_dump($user);
        echo 'Connexion réussie. Bienvenue ' . $user->_firstname . "\n";
    }
} catch (Exception $e) {
    echo 'Erreur : ' . $e->getMessage();
}