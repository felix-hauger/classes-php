<?php

require_once 'class/UserPDO.php';
require_once 'class/Form.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    die();
}

$user = new UserPDO;

$user_infos = $user->getAllInfos();

var_dump($_SESSION);

// $login = 'tyty';
// $password = 'tyty';

// $logged_user = $_SESSION['user'];

// // $user_pdo = new UserPDO($login, $password);
// $user_pdo = new UserPDO($logged_user->getLogin());


// // var_dump($user_pdo);

// try {
//     if ($user = $user_pdo->update('tyty', 'tyty', 'tyty@tyty.fr', 'tyty', 'tyty')) {
//         var_dump($user);
//         echo 'Mise à jour réussie. Bienvenue ' . $user->_firstname . "\n";
//     }
// } catch (Exception $e) {
//     echo 'Erreur : ' . $e->getMessage();
// }

// _____________________

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
                if ($user_pdo->update($login, $password, $email, $firstname, $lastname)) {
                    header('Location: update.php');
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
    <input type="text" name="login" placeholder="Login"  value="<?= $user_infos['login'] ?>">
    <input type="email" name="email" id="email" placeholder="Email"  value="<?= $user_infos['email'] ?>">
    <input type="password" name="password" id="password" placeholder="Mot de Passe">
    <input type="password" name="password-confirm" id="password-confirm" placeholder="Confirmation Mot de Passe">
    <?php if (isset($errors['confirm'])) : ?>
        <span style="color: red;"><?= $errors['confirm'] ?></span>
    <?php endif ?>
    <input type="text" name="firstname" placeholder="Prénom" value="<?= $user_infos['firstname'] ?>">
    <input type="text" name="lastname" placeholder="Nom" value="<?= $user_infos['lastname'] ?>">
    <input type="submit" name="submit" value="Inscription">
    <?php if (isset($errors['unfilled'])) : ?>
        <span style="color: red;"><?= $errors['unfilled'] ?></span>
    <?php endif ?>
</form>