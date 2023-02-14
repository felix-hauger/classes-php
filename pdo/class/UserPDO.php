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
    
    /**
     * @var array contains user infos
     */
    private $_infos;

    public function __construct($login = null, $password = null, $email = null, $firstname = null, $lastname = null)
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
        // Assign to $email false or $email filtered by filter_var, & throw error if false at the same time
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

        if ($user) {

            // check if password matches
            if (password_verify($this->_password, $user['password'])) {
                $this->_id = $user['id'];
                $this->_login = $user['login'];
                $this->_email = $user['email'];
                $this->_firstname = $user['firstname'];
                $this->_lastname = $user['lastname'];
                // $this->_infos = $user;
                // var_dump($this->_infos);

                if (session_status() === PHP_SESSION_ACTIVE) {
                    $this->_pdo = null;
                    $_SESSION['user_id'] = $this->_id;
                    $_SESSION['user'] = $this;
                    return $this;
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

        /**
     * update user infos in db
     * for parameters infos see class properties
     * @return object $this
     */
    public function update($login = null, $password = null, $email = null, $firstname = null, $lastname = null, Array $infos = []): object
    {
        if (!empty($infos)) {
            foreach ($infos as $info) {
                // var_dump($info);
                echo "$info \n";
            }
        }


        // Assign to $email false or $email filtered by filter_var, & throw error if false at the same time
        if (!$email = filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Format email invalide');
        }

        $search_logins = $this->verifyLogins($login, $email);

        if ($search_logins['found_login']) {
            throw new Exception('Le login existe déjà !');
        } elseif ($search_logins['found_email']) {
            throw new Exception('Adresse mail déjà utilisée');
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT, ['cost' => 10]);
    
            // update user infos in db
            $sql = 'UPDATE users SET login = :login, password = :password, email = :email, firstname = :firstname, lastname = :lastname WHERE id = :id';

            $update = $this->_pdo->prepare($sql);

            $update->bindParam(':login', $login);
            $update->bindParam(':password', $hashed_password);
            $update->bindParam(':email', $email);
            $update->bindParam(':firstname', $firstname);
            $update->bindParam(':lastname', $lastname);
            $update->bindParam(':id', $_SESSION['user_id']);
            var_dump($this->_id);
    
            // $update->bind_param('sssssi', $login, $hashed_password, $email, $firstname, $lastname, $this->_id);
    
            try {
                //code...
                if ($update->execute()) {
                    // update object infos that will be updated in the session variable
                    $this->_login = $login;
                    $this->_email = $email;
                    $this->_firstname = $firstname;
                    $this->_lastname = $lastname;
                }
            } catch (Exception $e) {
                throw new Exception('Erreur dans la mise à jour des information : ' . $e->getMessage());
            }
    


            // ----- Use $_infos to update?
    
            // return to get value, and we can display success message
            return $this;
        }

    }


    public function getAllInfos()
    {
        $sql = 'SELECT id, login, password, email, firstname, lastname FROM users WHERE id = :id';

        $select = $this->_pdo->prepare($sql);

        $select->bindParam(':id', $_SESSION['user_id'], PDO::PARAM_INT);
        var_dump($this->_pdo);

        if ($select->execute()) {
            $user_infos = $select->fetch(PDO::FETCH_ASSOC);
            var_dump($user_infos);
            $this->_infos = $user_infos;
            return $this->_infos;
        }
    }

    /**
     * Get used to get & update user infos
     */
    public function getId(): int
    {
        return $this->_id;
    }

    /**
     * Set used to get & update user infos
     */
    public function setId(int $_id): self
    {
        $this->_id = $_id;

        return $this;
    }

    /**
     * Get used to log in & update user infos in database
     */
    public function getLogin(): string
    {
        return $this->_login;
    }

    /**
     * Set used to log in & update user infos in database
     */
    public function setLogin(string $_login): self
    {
        $this->_login = $_login;

        return $this;
    }

    /**
     * Get used to log in & update user infos in database
     */
    public function getPassword(): string
    {
        return $this->_password;
    }

    /**
     * Set used to log in & update user infos in database
     */
    public function setPassword(string $_password): self
    {
        $this->_password = $_password;

        return $this;
    }

    /**
     * Get personal info
     */
    public function getEmail(): string
    {
        return $this->_email;
    }

    /**
     * Set personal info
     */
    public function setEmail(string $_email): self
    {
        $this->_email = $_email;

        return $this;
    }

    /**
     * Get personal info
     */
    public function getFirstname(): string
    {
        return $this->_firstname;
    }

    /**
     * Set personal info
     */
    public function setFirstname(string $_firstname): self
    {
        $this->_firstname = $_firstname;

        return $this;
    }

    /**
     * Get personal info
     */
    public function getLastname(): string
    {
        return $this->_lastname;
    }

    /**
     * Set personal info
     */
    public function setLastname(string $_lastname): self
    {
        $this->_lastname = $_lastname;

        return $this;
    }

    /**
     * Get contains user infos
     */
    public function getInfos(): array
    {
        return $this->_infos;
    }

    /**
     * Set contains user infos
     */
    public function setInfos(array $_infos): self
    {
        $this->_infos = $_infos;

        return $this;
    }
}


// var_dump($user_pdo);

// $test = $user_pdo->verifyLogins('toto', 'tot@toto.fr');

// var_dump($test);
