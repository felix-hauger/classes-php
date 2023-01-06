<?php

/**
 * Class User
 * 
 * Handles User service including using mysqli
 */
class User
{
    /**
     * @var mysqli used to init connection to database
     */
    private $db;

    /**
     * @var int used to get & update user infos
     */
    private $id;

    /**
     * @var string used to log in & update user infos in database
     */
    public $login;

    /**
     * @var string personal info, used to login & update user infos in database
     */
    public $email;

    /**
     * @var string personal info
     */
    public $firstname;

    /**
     * @var string personal info
     */
    public $lastname;

    /**
     * set connection to the database
     */
    public function __construct()
    {
        $this->db = new mysqli('localhost', 'root', '', 'classes');
        if ($this->db->connect_errno) {
            error_log('Erreur de connexion à la db : ' . $this->db->connect_errno);
        }
    }

    /**
     * check if login and email exist in database
     * @param string $login check the login column
     * @param string $email check the email column
     * @return array of 2 booleans
     */
    public function verifyLogins($login, $email)
    {
        // are false until found in db
        $logins_array = [
            'found_login' => false,
            'found_email' => false
        ];

        $sql = 'SELECT `login`, `email` FROM users WHERE login = ? OR email = ?';

        $select = $this->db->prepare($sql);

        $select->bind_param('ss', $login, $email);

        $select->execute();

        $result = $select->get_result();

        $users = $result->fetch_all(MYSQLI_ASSOC);

        // var_dump($users);

        foreach ($users as $user) {

            // var_dump($user);
            if ($user['login'] === $login) {
                $logins_array['found_login'] = true;
                // $foundLogin = true;
            }

            if ($user['email'] === $email) {
                // $foundEmail = true;
                $logins_array['found_email'] = true;
            }
        }
        
        return $logins_array;
    }

    /**
     * register a user in database
     * for parameters infos see class properties
     * @return boolean true after request is sent
     */
    public function register($login, $password, $email, $firstname, $lastname): bool
    {
        // Assign $checked_email $mail or false filtered by filter_var, & throw error if false at the same time
        if (!$checked_email = filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Erreur : format email invalide');
        }

        // check if login & email exist in database
        $search_logins = $this->verifyLogins($login, $checked_email);

        if ($search_logins['found_login']) {
            throw new Exception('Le login existe est déjà utilisé');
        } elseif ($search_logins['found_email']) {
            throw new Exception('Adresse mail déjà utilisée');
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT, ['cost' => 10]);
    
            $sql = 'INSERT INTO users (login, password, email, firstname, lastname) VALUES (?, ?, ?, ?, ?)';
    
            $insert = $this->db->prepare($sql);
    
            $insert->bind_param('sssss', $login, $hashed_password, $checked_email, $firstname, $lastname);
    
            $insert->execute();

            return true;
        }

    }

    /**
     * log user in session
     * @param string $login to compare to logins in db
     * @param string $password to check credentials
     * @return object $this
     */
    public function connect($login, $password): object
    {
        // check if login exists
        $sql = 'SELECT id, login, password, email, firstname, lastname FROM users WHERE login = ?';

        $select = $this->db->prepare($sql);

        $select->bind_param('s', $login);

        $select->execute();

        $result = $select->get_result();

        $user = $result->fetch_assoc();

        if ($user !== null) {

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

                $_SESSION['user'] = $this;

                return $this;
            }
        }

        throw new Exception('Erreur : identifiants incorrects.');
    }

    // public function checkCrentials()

    /**
     * checked if user is logged in session
     * @return boolean
     */
    public function isConnected(): bool
    {
        if (isset($_SESSION['user'])) {
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
    public function update($login, $password, $email, $firstname, $lastname): object
    {
        // Assign $checked_email $mail or false filtered by filter_var, & throw error if false at the same time
        if (!$checked_email = filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Erreur : format email invalide');
        }

        $search_logins = $this->verifyLogins($login, $checked_email);

        if ($search_logins['found_login']) {
            throw new Exception('Le login existe déjà !');
        } elseif ($search_logins['found_email']) {
            throw new Exception('Adresse mail déjà utilisée');
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT, ['cost' => 10]);
    
            // update user infos in db
            $sql = 'UPDATE users SET login = ?, password = ?, email = ?, firstname = ?, lastname = ? WHERE id = ?';
    
            $update = $this->db->prepare($sql);
    
            $update->bind_param('sssssi', $login, $hashed_password, $checked_email, $firstname, $lastname, $this->id);
    
            $update->execute();
    
            // update object infos that will be updated in the session variable
            $this->login = $login;
            $this->email = $email;
            $this->firstname = $firstname;
            $this->lastname = $lastname;
    
            // return to get value, and we can display success message
            return $this;
        }

    }

    public function getAllInfos(): array
    {
        return [
            'id' => $this->id,
            'login' => $this->login,
            'email' => $this->email,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname
        ];
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }

    /**
     * unset user infos from session
     */
    public function disconnect()
    {
        if (isset($_SESSION['user'])) {
            unset($_SESSION['user']);
        }
    }

    /**
     * delete user from database, then disconnect
     */
    public function delete()
    {
        $sql = 'DELETE FROM users WHERE id = ?';

        $delete = $this->db->prepare($sql);

        $delete->bind_param('i', $this->id);

        $delete->execute();

        $this->disconnect();
    }
}

session_start();
$user = new User;
// try {
//     if ($user->register('tete', 'tete', 'tete@tete.fr', 'tete', 'tete')) {
//         echo 'Inscription réussie' . "\n";
//     }
// } catch (Exception $e) {
//     echo $e->getMessage() . "\n";
// }

try {
    if ($user->connect('tyty', 'tyty')) {
        echo 'Connexion réussie ! Vous êtes maintenant connecté en tant que ' . $user->{'login'} . "\n";
    }
} catch (Exception $e) {
    echo $e->getMessage() . "\n";
}

// var_dump($_SESSION);

// try {
//     if ($user->update('tyty', 'tyty', 'tyty@tyty.fr', 'tyty', 'tyty')) {
//         echo 'Mise à jour des informations réussie';
//     }
// } catch (Exception $e) {
//     echo $e->getMessage() . "\n";
// }

// $user_infos = $user->getAllInfos();

// var_dump($user_infos);

$user->isConnected();

// $user->disconnect();
// $user->isConnected();
// var_dump($_SESSION);
// $user->delete();
// var_dump($user);
