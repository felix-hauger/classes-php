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
        if ($this->db->connect_errno){
            error_log('Erreur de connexion : ' . $this->db->connect_errno);
        }
    }

    public function register($login, $password, $email, $firstname, $lastname)
    {
        $sql = 'INSERT INTO users (login, password, email, firstname, lastname) VALUES (?, ?, ?, ?, ?)';

        $insert = $this->db->prepare($sql);

        $insert->bind_param('sssss', $login, $password, $email, $firstname, $lastname);

        $insert->execute();

        return [$login, $password, $email, $firstname, $lastname];
    }

}

$test = new User;
$test->register('admin', 'admin', 'felix.hauger@laplateforme.io', 'Félix', 'HAUGER');
var_dump($test);