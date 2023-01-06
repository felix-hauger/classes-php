<?php

require_once 'DbConnection.php';

class UserPDO extends DbConnection
{
    /**
     * @var int used to get & update user infos
     */
    private $_id;

    /**
     * @var string used to log in & update user infos in database
     */
    private $_login;

    /**
     * @var string used to log in & update user infos in database
     */
    private $_password;

    /**
     * @var string personal info
     */
    private $_email;

    /**
     * @var string personal info
     */
    public $_firstname;

    /**
     * @var string personal info
     */
    private $_lastname;

    public function __construct($login, $password, $email = null, $firstname = null, $lastname = null)
    {
        parent::__construct();

        $this->_login = $login;
        $this->_password = $password;
        $this->_email = $email;
        $this->_firstname = $firstname;
        $this->_lastname = $lastname;
    }

    public function verifyLogins($login, $email)
    {
        // are false until found in db
        $logins_array = [
            'found_login' => false,
            'found_email' => false
        ];

        $sql = 'SELECT `login`, `email` FROM users WHERE login = :login OR email = :email';

        $select = $this->_pdo->prepare($sql);

        $select->bindParam(':login', $login);
        $select->bindParam(':email', $email);

        $select->execute();

        $users = $select->fetchAll(PDO::FETCH_ASSOC);

        // var_dump($users);

        foreach ($users as $user) {

            // var_dump($user);
            if ($user['login'] === $login) {
                $logins_array['found_login'] = true;
            }

            if ($user['email'] === $email) {
                $logins_array['found_email'] = true;
            }
        }

        return $logins_array;
    }

    public function register($login, $password, $email, $firstname, $lastname)
    {
        // Assign $email false or $email filtered by filter_var, & throw error if false at the same time
        if (!$email = filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Format email invalide');
        } else {
            $search_logins = $this->verifyLogins($login, $email);

            if ($search_logins['found_login']) {
                throw new Exception('Le login existe est déjà utilisé');
            } elseif ($search_logins['found_email']) {
                throw new Exception('Adresse mail déjà utilisée');
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT, ['cost' => 10]);

                $sql = 'INSERT INTO users (login, password, email, firstname, lastname) VALUES (:login, :password, :email, :firstname, :lastname)';

                $insert = $this->_pdo->prepare($sql);

                $insert->bindParam(':login', $login);
                $insert->bindParam(':password', $hashed_password);
                $insert->bindParam(':email', $email);
                $insert->bindParam(':firstname', $firstname);
                $insert->bindParam(':lastname', $lastname);

                if ($insert->execute()) {
                    return true;
                } else {
                    throw new Exception('Échec lors de l\'inscription');
                }

            }
        }
    }

    public function connect()
    {
        $sql = 'SELECT id, login, password, email, firstname, lastname FROM users WHERE login = :login';

        $select = $this->_pdo->prepare($sql);

        $select->bindParam(':login', $this->_login);
        
        $select->execute();

        $user = $select->fetch(PDO::FETCH_ASSOC);

        if ($user !== null) {

            // check if password matches
            if (password_verify($this->_password, $user['password'])) {
                $this->_id = $user['id'];
                $this->_login = $user['login'];
                $this->_email = $user['email'];
                $this->_firstname = $user['firstname'];
                $this->_lastname = $user['lastname'];

                if (session_status() === PHP_SESSION_ACTIVE) {
                    $_SESSION['user_id'] = $this->_id;
                    return $this;
                } else {
                    return false;
                }
            }
        }

        throw new Exception('identifiants incorrects.');
    }

    public function isConnected(): bool
    {
        if (isset($_SESSION['user_id'])) {
            return true;
        } else {
            return false;
        }
    }


}


// var_dump($user_pdo);

// $test = $user_pdo->verifyLogins('toto', 'tot@toto.fr');

// var_dump($test);
