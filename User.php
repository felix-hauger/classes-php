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

}

$test = new User;
var_dump($test);