<?php

require_once 'UserPDO.php';

$login = 'tyty';
$password = 'tyty';
$email = 'tyty@tyty.fr';
$firstname = 'tyty';
$lastname = 'tyty';

$user_pdo = new UserPDO($login, $password, $email, $firstname, $lastname);

try {
    if ($user_pdo->register('tyty', 'tyty', 'tyty@tyty.fr', 'tyty', 'tyty')) {
        echo 'Inscription réussie';
    }
} catch (Exception $e) {
    echo 'Erreur : ' . $e->getMessage();
}
