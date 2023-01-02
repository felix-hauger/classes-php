<?php

class User
{
    private $db;
    private $id;
    public $login;
    public $email;
    public $firstname;
    public $lastname;

    public function __construct()
    {
        $this->db = new mysqli('localhost', 'root', '', 'classes');
        if ($this->db->connect_errno) {
            error_log('Erreur de connexion à la db : ' . $this->db->connect_errno);
        }
    }

    public function register($login, $password, $email, $firstname, $lastname)
    {
        if (!$checked_email = filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Erreur : format email invalide');
        }

        $hashed_password = password_hash($password, PASSWORD_DEFAULT, ['cost' => 10]);

        $sql = 'INSERT INTO users (login, password, email, firstname, lastname) VALUES (?, ?, ?, ?, ?)';

        $insert = $this->db->prepare($sql);

        $insert->bind_param('sssss', $login, $hashed_password, $checked_email, $firstname, $lastname);

        $insert->execute();

        return [$login, $password, $email, $firstname, $lastname];
    }

    public function connect($login, $password)
    {
        // check if login exists
        $sql = 'SELECT id, login, password, email, firstname, lastname FROM users WHERE login = ?';

        $select = $this->db->prepare($sql);

        $select->bind_param('s', $login);

        $select->execute();

        $result = $select->get_result();

        $user = $result->fetch_assoc();

        if ($user !== null) {
            // var_dump($user);

            // check if password matches
            if (password_verify($password, $user['password']) || $password === $user['password']) {
                $this->id = $user['id'];
                $this->login = $user['login'];
                $this->email = $user['email'];
                $this->firstname = $user['firstname'];
                $this->lastname = $user['lastname'];

                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }

                $_SESSION['id'] = $this->id;
                $_SESSION['login'] = $this->login;
                $_SESSION['email'] = $this->email;
                $_SESSION['firstname'] = $this->firstname;
                $_SESSION['lastname'] = $this->lastname;

                return [
                    $this->id,
                    $this->login,
                    $this->email,
                    $this->firstname,
                    $this->lastname
                ];
            }
        }

        throw new Exception('Erreur : identifiants incorrects.');
    }

    // public function checkCrentials()

    public function update($login, $password, $email, $firstname, $lastname)
    {
        if (!$checked_email = filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Erreur : format email invalide');
        }

        $hashed_password = password_hash($password, PASSWORD_DEFAULT, ['cost' => 10]);

        // $logged_id = $_SESSION['id'];

        // update user infos in db
        $sql = 'UPDATE users SET login = ?, password = ?, email = ?, firstname = ?, lastname = ? WHERE id = ?';

        $update = $this->db->prepare($sql);

        $update->bind_param('sssssi', $login, $hashed_password, $checked_email, $firstname, $lastname, $_SESSION['id']);

        $update->execute();

        // update user infos from db in instance & session
        // $sql = 'SELECT id, login, password, email, firstname, lastname FROM users WHERE id = ?';

        // $select = $this->db->prepare($sql);

        // $select->bind_param('i', $logged_id);

        // $select->execute();

        // $result = $select->get_result();

        // $user = $result->fetch_assoc();

        // $this->id = $user['id'];
        // $this->login = $user['login'];
        // $this->email = $user['email'];
        // $this->firstname = $user['firstname'];
        // $this->lastname = $user['lastname'];

        // $_SESSION['login'] = $this->login;
        // $_SESSION['email'] = $this->email;
        // $_SESSION['firstname'] = $this->firstname;
        // $_SESSION['lastname'] = $this->lastname;

        // return [
        //     $this->id,
        //     $this->login,
        //     $this->email,
        //     $this->firstname,
        //     $this->lastname
        // ];
    }

    public function disconnect()
    {
        unset($_SESSION['id']);
        unset($_SESSION['login']);
        unset($_SESSION['email']);
        unset($_SESSION['firstname']);
        unset($_SESSION['lastname']);
    }

    public function delete()
    {
        $sql = 'DELETE FROM users WHERE id = ?';

        $delete = $this->db->prepare($sql);

        $delete->bind_param('i', $_SESSION['id']);

        $delete->execute();

        $this->disconnect();
    }
}

$test = new User;
// try {
//     if ($test->register('toto', 'toto', 'toto@toto.fr', 'toto', 'toto')) {
//         echo 'Inscription réussie';
//     }
// } catch (Exception $e) {
//     echo $e->getMessage();
// }

try {
    if ($test->connect('toto', 'toto')) {
        echo 'Connexion réussie';
    }
} catch (Exception $e) {
    echo $e->getMessage();
}

var_dump($_SESSION);

try {
    if ($test->update('tutu', 'tutu', 'tutu@tutu.fr', 'tutu', 'tutu')) {
        echo 'Mise à jour des informations réussie';
    }
} catch (Exception $e) {
    echo $e->getMessage();
}

var_dump($_SESSION);

// $test->disconnect();
// $test->delete();
// var_dump($test);
