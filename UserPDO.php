<?php

require_once 'DbConnection.php';

class UserPDO extends DbConnection
{
    /**
     * @var int used to get & update user infos
     */
    private $id;

    /**
     * @var string used to log in & update user infos in database
     */
    private $login;

    /**
     * @var string personal info
     */
    private $email;

    /**
     * @var string personal info
     */
    private $firstname;

    /**
     * @var string personal info
     */
    private $lastname;

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
}

$user_pdo = new UserPDO();

// var_dump($user_pdo);

$test = $user_pdo->verifyLogins('toto', 'tot@toto.fr');

var_dump($test);

try {
    if ($user_pdo->register('tete', 'tete', 'tete@tete.fr', 'tete', 'tete')) {
        echo 'Inscription réussie';
    }
} catch (Exception $e) {
    echo 'Erreur : ' . $e->getMessage();
}