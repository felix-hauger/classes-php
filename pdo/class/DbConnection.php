<?php

abstract class DbConnection
{
    protected $_pdo;

    protected function __construct()
    {
        try {
            $this->_pdo = new PDO('mysql:host=localhost;dbname=classes;charset=utf8','root','');
            $this->_pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo 'Erreur de  connexion : ' . $e->getMessage();
            exit;
        }
    }
}

// $test = new DbConnection;

// var_dump($test);