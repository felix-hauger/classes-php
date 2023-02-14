<?php

require_once 'class/UserPDO.php';
require_once 'class/Form.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user'])) {

}

if (isset($_POST['submit'])) {
    if (Form::areAllPostsFilled()) {
        $login = $_POST['login'];
        $password = $_POST['password'];

        $user_pdo = new UserPDO($login, $password);

        try {
            if ($user = $user_pdo->connect()) {
                // var_dump($user);
                echo 'Connexion réussie. Bienvenue ' . $user->_firstname . "\n";
            }
        } catch (Exception $e) {
            echo 'Erreur : ' . $e->getMessage();
        }
    }
}

var_dump('session', $_SESSION);

?>

<form action="" method="post">
    <input type="text" name="login" id="login" placeholder="Login">
    <input type="password" name="password" id="password" placeholder="Mot de passe">
    <input type="submit" name="submit" value="Connexion">
</form>