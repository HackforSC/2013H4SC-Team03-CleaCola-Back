<?php namespace LD\Model;

class User
{
    public $id;
    public $email;
    public $password;
    public $first_name;
    public $last_name;
    public $date_created;

    /**
     * @param $id
     * @return \LD\Model\User|null
     */
    public static function withId($id)
    {
        $stmt = \LD\Helper\DB::instance()->prepare('
            SELECT id, email, first_name, last_name, date_created, password
            FROM Users
            WHERE id = :id
        ');
        $stmt->execute(array(':id' => $id));

        $instance = new self();
        $stmt->setFetchMode(\PDO::FETCH_INTO, $instance);
        $user = $stmt->fetch();

        return $user;
    }

    /**
     * @param string $email
     * @param string $password not hashed!
     * @return bool|\LD\Model\User
     */
    public static function withEmailAndPassword($email, $password)
    {
        $stmt = \LD\Helper\DB::instance()->prepare('
            SELECT id, email, first_name, last_name, date_created, password
            FROM Users
            WHERE email = :email
        ');
        $stmt->execute(array(':email' => strtolower($email)));

        $stmt->setFetchMode(\PDO::FETCH_INTO, new self());
        $user = $stmt->fetch();

        if ($user == false) {
            return false;
        }

        if (password_verify($password, $user->password)) {
            $app = \Slim\Slim::getInstance();
            $app->setEncryptedCookie('user_id', $user->id);
        } else {
            return false;
        }

        return $user;
    }

    public static function withSession()
    {
        $app = \Slim\Slim::getInstance();
        $user_id = $app->getEncryptedCookie('user_id');

        return User::withId($user_id);
    }

    /**
     * @return string
     */
    public function getFullName()
    {
        return ucfirst($this->first_name) . ' ' . ucfirst($this->last_name);
    }

    public function getInitials()
    {
        return strtoupper(substr($this->first_name, 0, 1) . substr($this->last_name, 0, 1));
    }

    /**
     * @return bool
     */
    public function isWriter()
    {
        $stmt = \LD\Helper\DB::instance()->prepare('
            SELECT id
            FROM UserRoles
            WHERE user_id = :user_id AND role_id = :role_id
        ');
        $stmt->execute(array(
            ':user_id' => $this->id,
            ':role_id' => \LD\Model\Role::WRITER_ID
        ));
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($row === FALSE) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function isDeveloper()
    {
        $stmt = \LD\Helper\DB::instance()->prepare('
            SELECT id
            FROM UserRoles
            WHERE user_id = :user_id AND role_id = :role_id
        ');
        $stmt->execute(array(
            ':user_id' => $this->id,
            ':role_id' => \LD\Model\Role::DEVELOPER_ID
        ));
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($row === FALSE) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function isAdministrator()
    {
        $stmt = \LD\Helper\DB::instance()->prepare('
            SELECT id
            FROM UserRoles
            WHERE user_id = :user_id AND role_id = :role_id
        ');
        $stmt->execute(array(
            ':user_id' => $this->id,
            ':role_id' => \LD\Model\Role::ADMINISTRATOR_ID
        ));
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($row === FALSE) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function isPublisher()
    {
        $stmt = \LD\Helper\DB::instance()->prepare('
            SELECT id
            FROM UserRoles
            WHERE user_id = :user_id AND role_id = :role_id
        ');
        $stmt->execute(array(
            ':user_id' => $this->id,
            ':role_id' => \LD\Model\Role::PUBLISHER_ID
        ));
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($row === FALSE) {
            return false;
        }

        return true;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        $stmt = \LD\Helper\DB::instance()->prepare('
            SELECT Roles.id, Roles.alias, Roles.date_created
            FROM UserRoles
            INNER JOIN Roles ON Roles.id = UserRoles.role_id
            WHERE user_id = :user_id
        ');
        $stmt->execute(array(
            ':user_id' => $this->id
        ));
        $result = $stmt->fetchAll(\PDO::FETCH_CLASS, 'LD\Model\Role');

        return $result;
    }

    /**
     * @return array
     */
    public static function all()
    {
        $stmt = \LD\Helper\DB::instance()->prepare('
            SELECT id, email, first_name, last_name, date_created, password
            FROM Users
        ');
        $stmt->execute();

        $users = $stmt->fetchAll(\PDO::FETCH_CLASS, 'LD\Model\User');

        return $users;
    }

    /**
     * @return bool
     */
    public function create()
    {
        if (isset($this->id)) {
            return false;
        }

        // TODO: if email already in use, check if registered. If not, send verification email again

        $token = uniqid();

        $db = \LD\Helper\DB::instance();

        $stmt = $db->prepare('
            INSERT INTO Users (email, first_name, last_name, verify_email_token)
            VALUES (:email, :first_name, :last_name, :token)
        ');
        $stmt->execute(array(
            ':email' => $this->email,
            ':first_name' => $this->first_name,
            ':last_name' => $this->last_name,
            ':token' => $token
        ));

        $user_id = $db->lastInsertId();
        $this->id = $user_id;

        if (\Slim\Slim::getInstance()->getMode() == 'production') {
            $mail = new \LD\Helper\Mail();
            $mail->send(
                'Invitation to Dew Learning\'s Lesson Designer',
                'You have been invited to the Lesson Designer. http://' . $_SERVER['HTTP_HOST'] . '/v/' . $token,
                array($this->email));
        }

        return true;
    }

    /**
     * @param Role $role
     * @return bool
     */
    public function addRole(\LD\Model\Role $role)
    {
        $stmt = \LD\Helper\DB::instance()->prepare('
            INSERT INTO UserRoles (user_id, role_id)
            VALUES (:user_id, :role_id)
        ');
        return $stmt->execute(array(
            ':role_id' => $role->id,
            ':user_id' => $this->id
        ));
    }

    /**
     * @param string $token
     * @param string $password not hashed!
     * @return bool
     */
    public static function verifyEmailByTokenAndSetPassword($token, $password)
    {
        $stmt = \LD\Helper\DB::instance()->prepare('
            UPDATE Users
            SET password = :password
            WHERE verify_email_token = :token
        ');
        $stmt->execute(array(
            ':password' => password_hash($password, PASSWORD_BCRYPT),
            ':token' => $token
        ));

        // login in the user
        $stmt = \LD\Helper\DB::instance()->prepare('
            SELECT id, email, first_name, last_name, date_created, password
            FROM Users
            WHERE verify_email_token = :token
        ');
        $stmt->execute(array(':token' => $token));

        $instance = new self();
        $stmt->setFetchMode(\PDO::FETCH_INTO, $instance);
        $user = $stmt->fetch();

        $app = \Slim\Slim::getInstance();
        $app->setEncryptedCookie('user_id', $user->id);

        return true;
    }

}