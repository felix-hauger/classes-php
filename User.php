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
        $sql = 'INSERT INTO users (login, password, email, firstname, lastname) VALUES (?, ?, ?, ?, ?)';

        $insert = $this->db->prepare($sql);

        $hashed_password = password_hash($password, PASSWORD_DEFAULT, ['cost' => 10]);

        if (!$checked_email = filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Erreur : format email invalide');
        }

        $insert->bind_param('sssss', $login, $hashed_password, $checked_email, $firstname, $lastname);

        $insert->execute();

        return [$login, $password, $email, $firstname, $lastname];
    }
}

$test = new User;
try {
    if ($test->register('test', 'test', 'test@test.fr', 'test', 'test')) {
        echo 'Inscription réussie';
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
var_dump($test);
