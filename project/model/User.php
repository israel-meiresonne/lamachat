<?php

require_once 'framework/Model.php';
require_once 'Discussion.php';

/**
 * This class represents an User
 */
class User extends Model
{
    /**
     * Holds the user's pseudo
     * @var string
     */
    private $pseudo;

    /**
     * Holds the user's lastname
     * @var string
     */
    private $firstname;

    /**
     * Holds the user's lastname
     * @var string
     */
    private $lastname;

    /**
     * Holds the user's password
     * @var string
     */
    private $password;

    /**
     * Holds the user's informations 
     * + NOTE: use the information name as access key
     * @var string[]
     */
    private $informations;

    /**
     * Holds the user's profil picture
     * @var string
     */
    private $picture;

    /**
     * Holds the user's profil status
     * @var string
     */
    private $status;

    /**
     * Holds the user's permission
     * @var string
     */
    private $permission;

    /**
     * Holds user's discussion ordered from newest to oldest
     * + NOTE: use as access key the UNIX time of the last message sended in one discussion
     * @var Discussion[]
     */
    private $discussions;

    /**
     * Holds a private key
     * @var string
     */
    private $privK;

    /**
     * Holds a public key
     * @var string
     */
    private $pubK;

    /**
     * Access key for session valeus
     * @var string
     */
    public const SESSION_PRIVATE_K = "privk";
    public const SESSION_PUBLIC_K = "pubk";

    /**
     * Constructor for user
     * @param string $pseudo user's pseudo
     * @param string $password user's password
     * @param string $firstname user's firstname
     * @param string $lastname user's lastname
     */
    function __construct()
    {
        $args = func_get_args();
        switch (func_num_args()) {
            case 0:
                $this->__construct0();
                break;
            case 2:
                $this->__construct2($args[0], $args[1]);
                break;
            case 4:
                $this->__construct4($args[0], $args[1], $args[2], $args[3]);
                break;
        }
    }

    /**
     * Constructor for user
     * @param string $pseudo user's pseudo
     * @param string $password user's password
     */
    private function __construct0()
    {
    }

    /**
     * Constructor for user
     * @param string $pseudo user's pseudo
     * @param string $password user's password
     */
    private function __construct2($pseudo, $password)
    {
        $this->pseudo = $pseudo;
        $this->password = $password;
    }

    /**
     * Constructor for user
     * @param string $pseudo user's pseudo
     * @param string $password user's password
     * @param string $firstname user's firstname
     * @param string $lastname user's lastname
     */
    private function __construct4($pseudo, $password, $firstname, $lastname)
    {
        $this->pseudo = $pseudo;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->password = $this->encrypt($password);
    }

    /**
     * Getter for user's pseudo
     * @return string user's pseudo
     */
    public function getPseudo()
    {
        return $this->pseudo;
    }

    /**
     * Getter for user's firstname
     * @return string user's firstname
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Getter for user's lastname
     * @return string user's lastname
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Getter for user's informations
     * @return string[] user's informations
     */
    public function getInformations()
    {
        return $this->informations;
    }

    /**
     * Getter for user's picture
     * @return string user's picture
     */
    public function getPicture()
    {
        return $this->picture;
    }

    /**
     * Getter for user's discussions
     * @return Discussion[] user's discussions
     */
    public function getDiscussions()
    {
        return $this->discussions;
    }

    /**
     * Getter for user's status
     * @return string user's status
     */
    public function getStatus()
    {
        return $this->status;
    }


    /**
     * Setter for user's pseudo
     * @param string user's pseudo
     */
    public function setPseudo($pseudo)
    {
        $this->pseudo = $pseudo;
    }

    /**
     * Setter for user's firstname
     * @param string user's firstname
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
    }

    /**
     * Setter for user's lastname
     * @param string user's lastname
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
    }

    /**
     * Setter for user's picture
     * @param string user's picture
     */
    public function setPicture($picture)
    {
        $this->picture = $picture;
    }

    /**
     * Setter for user's status
     * @param string user's status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * Setter for user's permission
     * @param string user's permission
     */
    public function setPermission($permission)
    {
        $this->permission = $permission;
    }


    /**
     * Setter for keys attribut
     * @param string $privK user's private key
     * @param string $pubK user's public key
     */
    public function setkeys($privK, $pubK)
    {
        $this->privK = $privK;
        $this->pubK = $pubK;
    }

    /**
     * Initialize discussion
     */
    public function setDiscussions()
    {
        if (empty($this->pseudo) || empty($this->pseudo)) {
            throw new Exception("User's pseudo must first be initialized");
        }
        $sql = "SELECT * 
        FROM `Discussions` d
        JOIN `Participants` p ON d.discuID = p.discuId
        WHERE pseudo_ = '$this->pseudo'";
        $pdo = parent::executeRequest($sql);
        $this->discussions = [];
        if($pdo->rowCount() > 0){
            while($pdoLine = $pdo->fetch()){
                $discuID = $pdoLine["discuID"];
                $setDate = $pdoLine["discuSetDate"];
                $discuName = empty($pdoLine["discuName"]) ? null : $pdoLine["discuName"];
                $discu = new Discussion($discuID, $setDate, $discuName);
                $discu->setParticipants();
                $discu->setMessages();
                $this->discussions[strtotime($setDate)] = $discu;
            }
        }
        krsort($this->discussions);
    }

    /**
     * Set user's properties
     */
    public function setProperties()
    {
        if (empty($this->privK) || empty($this->pubK)) {
            throw new Exception("Private and public key must first be initialized");
        }
        $sql = "SELECT * 
        FROM `UsersKeys` uk
        JOIN `Users` u on uk.pseudo_ = u.pseudo
        WHERE privateK = '$this->privK' AND publicK = '$this->pubK'
        ORDER BY `uk`.`keySetDate` DESC";
        $userPDO = parent::executeRequest($sql);
        if ($userPDO->rowCount() == 1) {
            $user = $userPDO->fetch();
            $this->pseudo = $user["pseudo"];
            $this->firstname = $user["firstname"];
            $this->lastname = $user["lastname"];
            $this->picture = $user["picture"];
            $this->status = $user["status"];
            $this->permission = $user["permission"];


            $sql = "SELECT * FROM `Users_ Informations` WHERE pseudo_ = '$this->pseudo'";
            $infosPDO = parent::executeRequest($sql);
            if ($infosPDO->rowCount() > 0) {
                $this->informations = [];
                while ($infoLine = $infosPDO->fetch()) {
                    $this->informations[$infoLine["information_"]] = $infoLine["value"];
                }
            }
        }
    }

    /**
     * Register a new user in the database
     * @param Response $response to push in occured errors
     * @param Session $session user's session
     * @return boolean true if the user is registered else false
     */
    public function signUp($response, $session)
    {
        if (!$this->pseudoExist()) {
            $sql = "INSERT INTO `Users`(`pseudo`, `password`, `firstname`, `lastname`) VALUES (?,?,?,?)";
            $pdoState = parent::executeRequest($sql, array($this->pseudo, $this->password, $this->firstname, $this->lastname));
            if ($pdoState->errorInfo()[0] != "00000") {
                $errMsg = "désolé, une erreur s'est produite lors de votre inscription!";
                $response->addError($errMsg, MyError::FATAL_ERROR);
                return false;
            }
            return $this->saveKeys($response, $session);
        }
        $errMsg = "ce pseudo existe déjà!";
        $response->addError($errMsg, ControllerSign::INPUT_PSEUDO);
        return false;
    }

    /**
     * Sign in the user to system
     * @param Response $response to push in occured errors
     * @param Session $session user's session
     * @return boolean true if the user is sign in else false
     */
    public function signIn($response, $session)
    {
        if ($this->pseudoExist()) {
            $sql = "SELECT `password` FROM `Users` WHERE `pseudo` = '$this->pseudo'";
            $passHash = parent::executeRequest($sql)->fetch()["password"];
            if (!($this->passMatchHash($this->password, $passHash))) {
                $errMsg = "le pseudo ou le mot de passe est incorrect!";
                $response->addError($errMsg, ControllerSign::INPUT_PSEUDO);
                $response->addError($errMsg, ControllerSign::INPUT_PSW);
                return false;
            }
            return $this->saveKeys($response, $session);
        }
        $errMsg = "ce pseudo n'existe pas!";
        $response->addError($errMsg, ControllerSign::INPUT_PSEUDO);
        return false;
    }

    /**
     * Check if the user's pseudo is already in the database
     * @return boolean true if the pseudo exist in the database else false
     */
    public function pseudoExist()
    {
        $sql = "SELECT `pseudo` FROM `Users` WHERE `pseudo` = '$this->pseudo'";
        $pseudos = parent::executeRequest($sql);
        return ($pseudos->rowCount() > 0);
    }

    /**
     * Check if user exist in the database and full its attributs
     * @return boolean true if the user exist in the database else false
     */
    public function userExist()
    {
        if (empty($this->privK) || empty($this->pubK)) {
            throw new Exception("Private and public key must first be initialized");
        }
        $sql = "SELECT * 
        FROM `UsersKeys`
        WHERE privateK = '$this->privK' AND publicK = '$this->pubK'
        ORDER BY `keySetDate` DESC";
        $keys = parent::executeRequest($sql);
        if ($keys->rowCount() == 1) {
            return true;
        }
        return false;
    }

    /** 
     * Crypt the password passed in parm
     * @param string $password password to crypt
     * @return string password's hashcode
     */
    private function encrypt($password)
    {
        return password_hash(sha1($password), PASSWORD_BCRYPT);
    }

    /** 
     * Check if the hashcode of password passed match the hashcode given in param
     * @param string $password the password to check
     * @param string $hashcode the hashcode to match
     * @return boolean true if the password match the hashcode given in param else false
     */
    private function passMatchHash($password, $hashcode)
    {
        return password_verify(sha1($password), $hashcode);
    }

    /**
     * Set user's session key and save the keys in database      
     * @param Response $response to push in occured errors    
     * @param Session $session user's session
     * @return boolean true if user's session keys were setted and saved in database else false
     */
    private function saveKeys($response, $session)
    {
        $privK = $this->getPrivateKey();
        $pubK = $this->getPublicKey();

        // save keys in the database
        $sql = "INSERT INTO `UsersKeys`(`pseudo_`, `keySetDate`, `privateK`, `publicK`) VALUES (?,?,?,?)";
        $pdoState = parent::executeRequest($sql, array($this->pseudo, date('Y-m-d H:i:s'), $privK, $pubK));
        if ($pdoState->errorInfo()[0] != "00000") {
            $errMsg = "désolé, une erreur s'est produite lors de votre connection!";
            $response->addError($errMsg, MyError::FATAL_ERROR);
            return false;
        }
        $session->setAttribute(self::SESSION_PRIVATE_K, $privK);
        $session->setAttribute(self::SESSION_PUBLIC_K, $pubK);
        return true;
    }

    /**
     * Generate a public and private key
     */
    private function generateKeys()
    {
        $config = array(
            "digest_alg" => "sha512",
            "private_key_bits" => 4096,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        );
        $keys = openssl_pkey_new($config);
        openssl_pkey_export($keys, $privK);
        $pubK = openssl_pkey_get_details($keys);
        $pubK = $pubK["key"];

        $this->privK = $privK;
        $this->pubK = $pubK;
    }

    /**
     * To get the private key
     * @return string the private key
     */
    private function getPrivateKey()
    {
        if ((!isset($this->privK)) || empty($this->privK)) {
            $this->generateKeys();
        }
        return $this->privK;
    }

    /**
     * To get the public key
     * @return string the public key
     */
    private function getPublicKey()
    {
        if ((!isset($this->pubK)) || empty($this->pubK)) {
            $this->generateKeys();
        }
        return $this->pubK;
    }
}
