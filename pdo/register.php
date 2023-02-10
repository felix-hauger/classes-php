<?php

require_once 'class/UserPDO.php';
require_once 'class/Form.php';

$errors = [];

if (isset($_POST['submit'])) {
    if (Form::areAllPostsFilled()) {
        $login = $_POST['login'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $password_confirm = $_POST['password-confirm'];
        $firstname = $_POST['firstname'];
        $lastname = $_POST['lastname'];

        if (Form::passConfirm($password, $password_confirm)) {
            $user_pdo = new UserPDO($login, $password, $email, $firstname, $lastname);

            try {
                if ($user_pdo->register($login, $password, $email, $firstname, $lastname)) {
                    echo 'Inscription réussie';
                }
            } catch (Exception $e) {
                echo 'Erreur : ' . $e->getMessage();
            }
        } else {
            $errors['confirm'] = 'Champs des Mots de Passe différents.';
        }
    } else {
        $errors['unfilled'] = 'Remplissez tous les champs';
    }
}
?>

<form action="" method="post">
    <input type="text" name="login" placeholder="Login">
    <input type="email" name="email" id="email" placeholder="Email">
    <input type="password" name="password" id="password" placeholder="Mot de Passe">
    <input type="password" name="password-confirm" id="password-confirm" placeholder="Confirmation Mot de Passe">
    <?php if (isset($errors['confirm'])) : ?>
        <span style="color: red;"><?= $errors['confirm'] ?></span>
    <?php endif ?>
    <input type="text" name="firstname" placeholder="Prénom">
    <input type="text" name="lastname" placeholder="Nom">
    <input type="submit" name="submit" value="Inscription">
    <?php if (isset($errors['unfilled'])) : ?>
        <span style="color: red;"><?= $errors['unfilled'] ?></span>
    <?php endif ?>
</form>