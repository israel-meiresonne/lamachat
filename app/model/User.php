<?php

require_once 'framework/Model.php';
require_once 'controller/ControllerSecure.php';
require_once 'model/Discussion.php';
require_once 'model/Message.php';

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
     * Holds the user's birthdate
     * @var string
     */
    private $birthdate;

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
     * Holds user's contacts
     * + NOTE: use the user's pseudo as access key
     * @var User[]
     */
    private $contacts;

    /**
     * Holds the relationship between the contact and a user
     * @var string 
     */
    private $relationship;

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
     * Holds db's Informations table
     * @var string[]
     */
    private static $SUPPORTED_INFOS;

    /**
     * Holds the directory where to store user's profil picture
     * @var string
     */
    public const PICTURE_DIR = "content/images/user-profile/";

    /**
     * Holds the directory where to store user's profil picture
     * @var string
     */
    private const DEFAULT_PICTURE = "default-user-picture.png";
    private const DEFAULT_INFO = "—";

    /**
     * datas access keys
     * + value must match User class's attributes (same case)
     */
    public const KEY_PSEUDO = "pseudo";
    public const KEY_FIRSTNAME = "firstname";
    public const KEY_LASTNAME = "lastname";
    public const KEY_BIRTHDATE = "birthdate";
    public const KEY_PSW = "password";
    public const KEY_PICTURE = "picture";
    public const KEY_STATUS = "status";
    public const KEY_PERMISSION = "permission";

    /**
     * @var string holds admin permission
     */
    public const PERMIT_ADMIN = "admin";
    /**
     * @var string holds user permission
     */
    public const PERMIT_USER = "user";
    /**
     * @var string holds banished permission
     */
    public const PERMIT_BANISHED = "banished";
    /**
     * @var string holds banished permission
     */
    public const PERMIT_DELETED = "deleted";

    /**
     * Holds list of supported picture extension
     */
    public const VALID_EXTENSIONS = ["jpg", "jpeg", "png"];

    /**
     * Holds staus's max length
     */
    public const STATUS_MAX_LENGTH = 250;

    /**
     * Holds staus's max length
     */
    public const INFO_MAX_LENGTH = 25;

    /**
     * Access key for session valeus
     * @var string
     */
    public const SESSION_PRIVATE_K = "privk";
    public const SESSION_PUBLIC_K = "pubk";

    /**
     * Relationship available values
     * @var string
     */
    public const KNOW = "know";
    public const BLOCKED = "blocked";

    /**
     * PDOStatement success code
     * @var string
     */
    public const PDO_SUCCEESS = "00000";

    /**
     * Holds the time (sec) to try again to update message feed
     * @var int
     */
    public const TIME_UPDATE_FEED = 2;

    /**
     * Holds the max time to sleep code
     * @var int
     */
    private static $MAX_SLEEP = 2;

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
            case 1:
                $this->__construct1($args[0]);
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
     * @param string $pseudo user's pseudo
     */
    private function __construct1($pseudo)
    {
        $sql = "SELECT * 
        FROM `Users`
        WHERE `pseudo` = '$pseudo'";
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
            $this->informations = [];
            if ($infosPDO->rowCount() > 0) {
                while ($infoLine = $infosPDO->fetch()) {
                    $this->informations[$infoLine["information_"]] = $infoLine["value"];
                }
            }
        }
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
        $this->pseudo = strtolower($pseudo);
        $this->firstname = strtolower($firstname);
        $this->lastname = strtolower($lastname);
        $this->password = $this->encrypt($password);
    }

    /**
     * Getter for user's pseudo
     * @return string user's pseudo
     */
    public function getPseudo()
    {
        (!isset($this->pseudo)) ? $this->setProperties() : null;
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
     * Getter for user's birthdate
     * @return string user's birthdate
     */
    public function getBirthdate()
    {
        (!isset($this->birthdate)) ? $this->setProperties() : null;
        if (empty($this->birthdate)) {
            $date = "aucune";
        } else {
            $time = strtotime($this->birthdate);
            $date = date("j/m/Y", $time);
        }
        return $date;
    }

    /**
     * Getter for user's informations
     * @return string[] user's informations
     */
    public function getInformations()
    {
        (!isset($this->informations)) ? $this->setProperties() : null;
        return $this->informations;
    }

    /**
     * To get user's informations using input name as access key
     * @return string[] informations's input name
     */
    public function getInfosInputName()
    {
        $infos = $this->getInformations();
        $newInfos = [];
        if (count($infos) > 0) {
            foreach ($infos as $info => $value) {
                $inputName = self::valueToInputName($info);
                $newInfos[$inputName] = $value;
            }
        }
        return $newInfos;
    }

    /**
     * Convert a value to a input name
     * @param string $value to convert
     * @return string a input name
     */
    public static function valueToInputName($value)
    {
        return strtolower(str_replace(" ", "_", $value));
    }

    /**
     * Getter for user's picture
     * @return string user's picture
     */
    public function getPicture()
    {
        (!isset($this->picture)) ? $this->setProperties() : null;
        return $this->picture;
    }

    /**
     * To get picture's valid extension separed with commat
     * @return string picture's valid extension separed with commat
     */
    public static function picExtensionsToString()
    {
        $string = "";
        foreach (self::VALID_EXTENSIONS as $key => $ext) {
            if ($key == 0) {
                $string .= "." . $ext;
            } else {
                $string .= ", ." . $ext;
            }
        }
        return $string;
    }

    /**
     * Getter for user's status
     * @return string user's status
     */
    public function getStatus()
    {
        (!isset($this->status)) ? $this->setProperties() : null;
        return $this->status;
    }

    /**
     * Getter for user's permission
     * @return string user's permission
     */
    public function getPermission()
    {
        (!isset($this->permission)) ? $this->setProperties() : null;
        return $this->permission;
    }

    /**
     * Getter for user's discussions
     * @return Discussion[] user's discussions
     */
    public function getDiscussions()
    {
        (!isset($this->discussions)) ? $this->setDiscussions() : null;
        return $this->discussions;
    }

    /**
     * To one discussion from user's discussions
     * @param string $discuID discussion's id
     * @return Discussion user's discussion
     */
    public function getDiscussion($discuID)
    {
        $discussions = $this->getDiscussions();
        foreach ($discussions as $unix => $discussion) {
            if ($discussion->getDiscuID() == $discuID) {
                return $discussions[$unix];
                break;
            }
        }
        throw new Exception("Discussion with id '$discuID' don't exist");
    }

    /**
     * Getter for user's contact
     * @return User[] user's contacts
     */
    public function getContacts()
    {
        return $this->contacts;
    }

    /**
     * Getter for contact's relationship
     * @return string contact's relationship
     */
    public function getRelationship()
    {
        return $this->relationship;
    }

    /**
     * Set user's properties
     */
    public function setProperties()
    {
        if ((empty($this->privK) || empty($this->pubK)) && empty($this->pseudo)) {
            throw new Exception("Private and public key or pseudo must first be initialized");
        }
        if (isset($this->privK) && isset($this->pubK)) {
            $sql = "SELECT * 
            FROM `UsersKeys` uk
            JOIN `Users` u on uk.pseudo_ = u.pseudo
            WHERE privateK = '$this->privK' AND publicK = '$this->pubK'
            ORDER BY `uk`.`keySetDate` DESC";
        } else {
            $sql = "SELECT * FROM `Users` WHERE pseudo = '$this->pseudo'";
        }
        $userPDO = parent::executeRequest($sql);
        if ($userPDO->rowCount() == 1) {
            $user = $userPDO->fetch();
            $this->pseudo = $user["pseudo"];
            $this->firstname = $user["firstname"];
            $this->lastname = $user["lastname"];
            $this->birthdate = $user["birthdate"];
            $this->picture = $user["picture"];
            $this->status = $user["status"];
            $this->permission = $user["permission"];

            $sql = "SELECT * FROM `Users_ Informations` WHERE pseudo_ = '$this->pseudo'";
            $infosPDO = parent::executeRequest($sql);
            $this->informations = [];
            if ($infosPDO->rowCount() > 0) {
                while ($infoLine = $infosPDO->fetch()) {
                    $this->informations[$infoLine["information_"]] = $infoLine["value"];
                }
            }
            $sql = "SELECT * FROM `Informations`";
            $pdo = parent::executeRequest($sql);
            $infos = $pdo->fetchAll(PDO::FETCH_COLUMN);
            foreach ($infos as $info) {
                if (!key_exists($info, $this->informations)) {
                    $this->informations[$info] = self::DEFAULT_INFO;
                }
            }
            ksort($this->informations);
        }
    }

    /**
     * To update user's properties submited
     * @param Response $response to push in occured errors
     * @param Request $request holds submited datas
     */
    public function updateProperties(Response $response, Request $request)
    {
        $sql = "";
        if ($request->existingParameter(User::KEY_PSEUDO)) {
            $pseudo = $request->getParameter(User::KEY_PSEUDO);
            $pseudo = strtolower($pseudo);
            $sqlUser = "SELECT * FROM `Users` WHERE `pseudo` = '$pseudo'";
            $pdo = $this->executeRequest($sqlUser);
            $tab = $pdo->fetchAll();
            if (count($tab) > 0) {
                $errMsg = "Le pseudo '$pseudo' est déjà utilisé";
                $response->addError($errMsg, self::KEY_PSEUDO);
            } else {
                $sql .= "UPDATE `Users` SET `pseudo`='$pseudo'";
            }
        }
        if (!$response->containError()) {
            if ($request->existingParameter(User::KEY_FIRSTNAME)) {
                $data = $request->getParameter(User::KEY_FIRSTNAME);
                $data = strtolower($data);
                $sql .= (empty($sql)) ? "UPDATE `Users` SET `firstname`='$data'" : ", `firstname`='$data'";
            }

            if ($request->existingParameter(User::KEY_LASTNAME)) {
                $data = $request->getParameter(User::KEY_LASTNAME);
                $data = strtolower($data);
                $sql .= (empty($sql)) ? "UPDATE `Users` SET `lastname`='$data'" : ", `lastname`='$data'";
            }

            if ($request->existingParameter(User::KEY_STATUS)) {
                $data = $request->getParameter(User::KEY_STATUS);
                $sql .= (empty($sql)) ? "UPDATE `Users` SET `status`='$data'" : ", `status`='$data'";
            }

            if ($request->existingParameter(User::KEY_BIRTHDATE)) {
                $data = $request->getParameter(User::KEY_BIRTHDATE);
                preg_match(ControllerSecure::DATE_REGEX, strtolower($data), $matches);
                $d = $matches[1];
                $m = $matches[2];
                $y = $matches[3];
                $time = mktime(null, null, null, $m, $d, $y);
                $date = date("Y-m-d", $time);
                $sql .= (empty($sql)) ? "UPDATE `Users` SET `birthdate`='$date'" : ", `birthdate`='$date'";
            }
            if (!empty($_FILES[User::KEY_PICTURE])) {
                $oldPicture = $this->getPicture();
                $newpicture = Discussion::generateDateCode(20);
                $file = $_FILES[User::KEY_PICTURE];
                $fileName = $file["name"];
                $ext = pathinfo($fileName, PATHINFO_EXTENSION);
                $newpicture .= "." . $ext;
                $sql .= (empty($sql)) ? "UPDATE `Users` SET `picture`='$newpicture'" : ", `picture`='$newpicture'";
            }
            $pseudo = $this->getPseudo();
            $sql .= (!empty($sql)) ? " WHERE `pseudo`='$pseudo'; " : "";

            $infoKeys = array_keys($this->getInformations());
            if (count($infoKeys) > 0) {
                foreach ($infoKeys as $info) {
                    $input = self::valueToInputName($info);
                    if ($request->existingParameter($input)) {
                        $informations = $this->getInformations();
                        if ($informations[$info] != self::DEFAULT_INFO) {
                            $value = strtolower($request->getParameter($input));
                            $sql .= " UPDATE `Users_ Informations` SET `value` = '$value' WHERE `Users_ Informations`.`pseudo_` = '$pseudo' AND `Users_ Informations`.`information_` = '$info'; ";
                        } else {
                            $value = strtolower($request->getParameter($input));
                            $sql .= " INSERT INTO `Users_ Informations`(`pseudo_`, `information_`, `value`) VALUES ('$pseudo', '$info', '$value'); ";
                        }
                    }
                }
            }

            if (!empty($sql)) {
                $pdo = $this->executeRequest($sql);
                if ($pdo->errorInfo()[0] != self::PDO_SUCCEESS) {
                    $errMsg = $pdo->errorInfo()[1];
                    $response->addError($errMsg, MyError::FATAL_ERROR);
                } else {
                    try {
                        $this->setProperties();
                    } catch (\Throwable $th) {}
                }
            }

            if ((!$response->containError()) && (!empty($_FILES[User::KEY_PICTURE]))) {
                if ($oldPicture != self::DEFAULT_PICTURE) {
                    unlink(self::PICTURE_DIR . $oldPicture);
                }
                $location = self::PICTURE_DIR . $newpicture;
                move_uploaded_file($file["tmp_name"], $location);
                $response->addResult(User::KEY_PICTURE, $location);
            }

            if (!$response->containError()) {
                foreach ($_POST as $attr => $value) {
                    if (property_exists($this, $attr)) {
                        $response->addResult($attr, $this->{$attr});
                    }
                }

                if (count($infoKeys) > 0) {
                    foreach ($infoKeys as $key) {
                        $input = self::valueToInputName($key);
                        if ($request->existingParameter($input)) {
                            $response->addResult($input, $this->informations[$key]);
                        }
                    }
                }
            }
        }
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
     * Setter for contact's relationship
     */
    public function setRelationship($relationship)
    {
        $this->relationship = $relationship;
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
        // $this->setProperties();
        (!isset($this->pseudo)) ? $this->setProperties() : null;
        $pseudo = $this->getPseudo();
        // if (empty($this->pseudo)) {
        //     throw new Exception("User's pseudo must first be initialized");
        // }
        $sql = "SELECT * 
        FROM `Discussions` d
        JOIN `Participants` p ON d.discuID = p.discuId
        WHERE pseudo_ = '$pseudo'";
        $pdo = parent::executeRequest($sql);
        $this->discussions = [];
        if ($pdo->rowCount() > 0) {
            while ($pdoLine = $pdo->fetch()) {
                $discu = $this->createDiscussion($pdoLine);
                $unix = strtotime($discu->getSetDate());
                $this->discussions[$unix] = $discu;
            }
        }
        krsort($this->discussions);
    }

    /**
     * Create a new Discussion
     * @param string[] $pdoLine line from database witch contain discussion's properties
     * @return Discussion a Discussion instance
     */
    public function createDiscussion($pdoLine)
    {
        $discuID = $pdoLine["discuID"];
        $setDate = $pdoLine["discuSetDate"];
        $discuName = empty($pdoLine["discuName"]) ? null : $pdoLine["discuName"];
        $discu = new Discussion($discuID, $setDate, $discuName);
        $discu->setParticipants();
        $discu->setMessages();
        return $discu;
    }

    /**
     * Remove current user from a discussion
     * @param string $discuID discussion's id
     * @param Response $response to push in occured errors
     */
    public function removeDiscussion($discuID, Response $response)
    {
        $sql = "DELETE FROM `Discussions` WHERE `discuID` = '$discuID'";
        $pdo = parent::executeRequest($sql);
        if ($pdo->errorInfo()[0] != self::PDO_SUCCEESS) {
            $errMsg = $pdo->errorInfo()[1];
            $response->addError($errMsg, MyError::FATAL_ERROR);
        } else {
            $this->unsetDiscussion($discuID);
        }
    }

    /**
     * To destroy a discussion
     * @param string $discuID discussion's id
     */
    private function unsetDiscussion($discuID)
    {
        $discussions = $this->getDiscussions();
        if (count($discussions) > 0) {
            foreach ($discussions as $key => $discu) {
                if ($discu->getDiscuID() == $discuID) {
                    $this->discussions[$key] = null;
                    unset($this->discussions[$key]);
                }
            }
        }
    }

    /**
     * To send textual a message
     * @param Response $response to push in occured errors
     * @param string $discuID discussion's id
     * @param string $message message to send
     * @return Meassage message sent
     */
    public function sendMessage(Response $response, $discuID, $message)
    {
        $from = $this;
        $type = Message::MSG_TYPE_TEXT;
        $status = Message::MSG_STATUS_SEND;
        $pubK = $this->getPublicKey();
        $encrypted = Message::encrypt($pubK, $message);
        $privK = $this->getPrivateKey();
        $msgObj = new Message($privK, $from, $type, $encrypted, $status);
        $msgObj->sendMessage($response, $discuID);
        return $msgObj;
    }

    /**
     * To mark all messages from a discussion as read
     * @param Response $response to push in occured errors
     * @param string $discuID discussion's id
     */
    public function readMessage($response, $discuID)
    {
        $pseudo = $this->getPseudo();
        $sql = 'UPDATE `Messages` SET `msgStatus` = "' . Message::MSG_STATUS_READ . '" WHERE `discuId` = "' . $discuID . '" AND `from_pseudo` != "' . $pseudo . '"';
        $pdo = $this->executeRequest($sql);
        if ($pdo->errorInfo()[0] != self::PDO_SUCCEESS) {
            $errMsg = $pdo->errorInfo()[1];
            $response->addError($errMsg, MyError::FATAL_ERROR);
        }
    }

    /**
     * Setter for user's contacts attribut
     */
    public function setContacts()
    {
        $pseudo = $this->getPseudo();
        $this->contacts = [];
        $sql = "SELECT * 
        FROM `Contacts` c
        JOIN `Users` u ON c.contact = u.pseudo
        WHERE pseudo_ = '$pseudo'";
        $pdo = parent::executeRequest($sql);
        if ($pdo->rowCount() > 0) {
            while ($pdoLine = $pdo->fetch()) {
                $user = $this->createContact($pdoLine);
                $this->contacts[$user->getPseudo()] = $user;
            }
        }
    }

    /**
     * Create a new User
     * @param string[] $pdoLine line from database witch contain user's properties
     * @return User a User instance
     */
    protected function createContact($pdoLine)
    {
        $pseudo = $this->getPseudo();
        $contact = new User();
        $contactPseu = (!empty($pdoLine["contact"])) ? $pdoLine["contact"] : $pdoLine["pseudo"];
        // var_dump($contactPseu);
        $contact->setPseudo($contactPseu);
        $contact->setFirstname($pdoLine["firstname"]);
        $contact->setLastname($pdoLine["lastname"]);
        $contact->setPicture($pdoLine["picture"]);
        $contact->setStatus($pdoLine["status"]);
        $contact->setPermission($pdoLine["permission"]);
        $sql = "SELECT * FROM `Contacts` WHERE `pseudo_` = '$pseudo' AND `contact` = '$contactPseu'";
        $pdo = parent::executeRequest($sql);
        ($pdo->rowCount() > 0) ? $contact->setRelationship($pdo->fetch()["contactStatus"]) : null;
        return $contact;
    }

    /**
     * Create a new User
     * @param string[] $pdoLine line from database witch contain user's properties
     * @return User a User instance
     */
    public static function createUser($pdoLine)
    {
        $user = new User();
        $user->pseudo = $pdoLine["pseudo"];
        $user->firstname = $pdoLine["firstname"];
        $user->lastname = $pdoLine["lastname"];
        $user->birthdate = $pdoLine["birthdate"];
        $user->picture = $pdoLine["picture"];
        $user->status = $pdoLine["status"];
        $user->permission = $pdoLine["permission"];
        // $user->relationship = $pdoLine["contactStatus"];
        return $user;
    }

    /**
     * Register a new user in the database
     * @param Response $response to push in occured errors
     * @param Session $session user's session
     * @return boolean true if the user is registered else false
     */
    public function signUp(Response $response, Session $session)
    {
        if (!$this->pseudoExist()) {
            $sql = "INSERT INTO `Users`(`pseudo`, `password`, `firstname`, `lastname`) VALUES (?,?,?,?)";
            $pdoState = parent::executeRequest($sql, array($this->pseudo, $this->password, $this->firstname, $this->lastname));
            if ($pdoState->errorInfo()[0] != self::PDO_SUCCEESS) {
                $errMsg = "désolé, une erreur s'est produite lors de votre inscription!";
                $response->addError($errMsg, MyError::FATAL_ERROR);
                return false;
            }
            return $this->saveKeys($response, $session);
        }
        $errMsg = "ce pseudo existe déjà!";
        $response->addError($errMsg, self::KEY_PSEUDO);
        return false;
    }

    /**
     * Sign in the user to system
     * @param Response $response to push in occured errors
     * @param Session $session user's session
     * @return boolean true if the user is sign in else false
     */
    public function signIn(Response $response, $session)
    {
        if ($this->pseudoExist()) {
            $sql = "SELECT `password` FROM `Users` WHERE `pseudo` = '$this->pseudo'";
            $passHash = parent::executeRequest($sql)->fetch()["password"];
            if (!($this->passMatchHash($this->password, $passHash))) {
                $errMsg = "le pseudo ou le mot de passe est incorrect!";
                $response->addError($errMsg, self::KEY_PSEUDO);
                $response->addError($errMsg, self::KEY_PSW);
                return false;
            }
            return $this->saveKeys($response, $session);
        }
        $errMsg = "ce pseudo n'existe pas!";
        $response->addError($errMsg, self::KEY_PSEUDO);
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
    private function saveKeys(Response $response, $session)
    {
        $privK = $this->getPrivateKey();
        $pubK = $this->getPublicKey();

        // save keys in the database
        $sql = "INSERT INTO `UsersKeys`(`pseudo_`, `keySetDate`, `privateK`, `publicK`) VALUES (?,?,?,?)";
        $pdoState = parent::executeRequest($sql, array($this->pseudo, date('Y-m-d H:i:s'), $privK, $pubK));
        if ($pdoState->errorInfo()[0] != self::PDO_SUCCEESS) {
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

    /**
     * Perform add of a contact to the current user
     * @param string $pseudo contact to add's pseudo
     * @param Response $response to push in occured errors
     */
    public function addContact($pseudo, Response $response)
    {
        if (empty($this->pseudo)) {
            throw new Exception("User's pseu must be initialized");
        }
        $sql = "INSERT INTO `Contacts`(`pseudo_`, `contact`) VALUES ('$this->pseudo','$pseudo')";
        $pdo = parent::executeRequest($sql);
        if ($pdo->errorInfo()[0] != self::PDO_SUCCEESS) {
            $errMsg = "désolé, une erreur s'est produite lors de l'ajout de '$pseudo' à vos contacts!";
            $response->addError($errMsg, MyError::FATAL_ERROR);
        }
    }

    /**
     * Perform an remove of a contact from the current user
     * @param string $pseudo contact to remove's pseudo
     * @param Response $response to push in occured errors
     */
    public function removeContact($pseudo, Response $response)
    {
        if (empty($this->pseudo)) {
            throw new Exception("User's pseu must be initialized");
        }
        $sql = "DELETE FROM `Contacts` WHERE `Contacts`.`pseudo_` = '$this->pseudo' AND `Contacts`.`contact` = '$pseudo'";
        $pdo = parent::executeRequest($sql);
        if ($pdo->errorInfo()[0] != self::PDO_SUCCEESS) {
            $errMsg = "désolé, une erreur s'est produite lors de la suppression de '$pseudo'!";
            $response->addError($errMsg, MyError::FATAL_ERROR);
        }
    }

    /**
     * Perform a blockage of a contact from the current user
     * @param string $contactPseu contact to block's pseudo
     * @param Response $response to push in occured errors
     */
    public function blockContact($contactPseu, Response $response)
    {
        $pseudo = $this->getPseudo();
        $sql = "UPDATE `Contacts` SET `contactStatus` = 'blocked' WHERE `Contacts`.`pseudo_` = '$pseudo' AND `Contacts`.`contact` = '$contactPseu';";
        $pdo = parent::executeRequest($sql);
        if ($pdo->errorInfo()[0] != self::PDO_SUCCEESS) {
            $errMsg = "désolé, une erreur s'est produite lors de la tentative de bloquer '$contactPseu'!";
            $response->addError($errMsg, MyError::FATAL_ERROR);
        } else {
            $discus = $this->getDiscussions();
            $discuID = null;
            foreach ($discus as $discu) {
                $participants = $discu->getParticipants();
                if (key_exists($contactPseu, $participants) && key_exists($pseudo, $participants)) {
                    $discuID = $discu->getDiscuID();
                    break;
                }
            }
            (!empty($discuID))? $this->removeDiscussion($discuID, $response) : null;
        }
    }

    /**
     * Perform a unlock of a contact from the current user
     * @param string $pseudo contact to unlock's pseudo
     * @param Response $response to push in occured errors
     */
    public function unlockContact($pseudo, Response $response)
    {
        if (empty($this->pseudo)) {
            throw new Exception("User's pseu must be initialized");
        }
        $sql = "UPDATE `Contacts` SET `contactStatus` = 'know' WHERE `Contacts`.`pseudo_` = '$this->pseudo' AND `Contacts`.`contact` = '$pseudo';";
        $pdo = parent::executeRequest($sql);
        if ($pdo->errorInfo()[0] != self::PDO_SUCCEESS) {
            $errMsg = "désolé, une erreur s'est produite lors de la tentative de débloquage de '$pseudo'!";
            $response->addError($errMsg, MyError::FATAL_ERROR);
        }
    }

    // private function createRelationship()

    /**
     * Create a new discussion between current user and his contact
     * @param string $pseudo pseudo of the contact to discus with
     * @param Response $response to push in occured errors
     * @return Discussion|null discussion between current user and his contact
     */
    public function writeContact($contactPseu, Response $response)
    {
        $pseudo = $this->getPseudo();
        $sql = "SELECT *
        FROM `Participants` p1
        JOIN `Discussions` d ON p1.discuId = d.discuID
        WHERE p1.pseudo_ = '$pseudo' 
            AND p1.discuId IN (SELECT p2.discuId
                               FROM `Participants` p2
                               WHERE p2.pseudo_ = '$contactPseu')";
        $pdo = parent::executeRequest($sql);
        if ($pdo->errorInfo()[0] == self::PDO_SUCCEESS) {
            $this->unlockContact($contactPseu, $response);
            if (($pdo->rowCount() == 1)) {
                $pdoLine = $pdo->fetch();
                return $this->createDiscussion($pdoLine);
            } else {
                $discu = $this->createDiscussionWith($contactPseu, $response);
                $discu->setParticipants();
                $discu->setMessages();
                return $discu;
            }
        } else {
            $errMsg = "désolé, une erreur s'est produite lors de votre tentative d'écrire à '$contactPseu'!";
            $response->addError($errMsg, MyError::FATAL_ERROR);
        }
        return null;
    }

    /**
     * Create a new discussion between current user and his contact
     * @param string $pseudo pseudo of the contact to discus with
     * @param Response $response to push in occured errors
     * @return Discussion|null discussion between current user and his contact
     */
    private function createDiscussionWith($pseudo, Response $response)
    {
        if (empty($this->pseudo)) {
            throw new Exception("User's pseu must be initialized");
        }
        $sql = "INSERT INTO `Discussions` (`discuID`, `discuSetDate`) VALUES (?, ?);
                INSERT INTO `Participants` (`discuId`, `pseudo_`) VALUES (?, ?), (?, ?);";
        $discuID = Discussion::generateDateCode(25);
        $setDate = date('Y-m-d H:i:s');
        $datas = [
            $discuID,
            $setDate,
            $discuID,
            $this->pseudo,
            $discuID,
            $pseudo
        ];
        $pdo = parent::executeRequest($sql, $datas);
        if ($pdo->errorInfo()[0] == self::PDO_SUCCEESS) {
            return new Discussion($discuID, $setDate);
        } else {
            $errMsg = "désolé, une erreur s'est produite lors de votre tentative d'écrire à '$pseudo'!";
            $response->addError($errMsg, MyError::FATAL_ERROR);
        }
        return null;
    }

    /**
     * To search a contact in database
     * @param string $search contact's pseudo, firsname or lasname
     * @param Response $response to push in occured errors
     * @return User[] liste of contact matching the search
     */
    public function searchContact($search, Response $response)
    {
        $pseudo = $this->getPseudo();
        $contacts = [];
        if (!empty($search)) {
            $sql = "SELECT * 
            FROM `Users` u
            WHERE u.pseudo != '$pseudo' AND ("; //(u.pseudo LIKE '%pseud%' OR u.firstname LIKE '%%' OR u.lastname LIKE '%%')";
            $words = explode(" ", $search);
            $nbW = count($words);
            for ($i = 0; $i < $nbW; $i++) {
                $sql .= (0 < $i && $i < $nbW) ? " OR" : "";
                $clause = " u.pseudo LIKE '%$words[$i]%' OR u.firstname LIKE '%$words[$i]%' OR u.lastname LIKE '%$words[$i]%'";
                $sql .= $clause;
            }
            $sql .= ") ORDER BY `u`.`pseudo` ASC";

            $pdo = parent::executeRequest($sql);

            if ($pdo->errorInfo()[0] == self::PDO_SUCCEESS) {
                $pseudos = $pdo->fetchAll(PDO::FETCH_COLUMN);
                $pseudos = $this->filterSearch($response, $pseudos);
                $nbPseu = count($pseudos);
                if ($nbPseu > 0) {
                    $sql = "SELECT * 
                    FROM `Users` u
                    WHERE (";
                    for ($i = 0; $i < $nbPseu; $i++) {
                        $sql .= (0 < $i && $i < $nbPseu) ? " OR" : "";
                        $clause = " `pseudo` = '$pseudos[$i]'";
                        $sql .= $clause;
                    }
                    $sql .= ") ORDER BY `u`.`pseudo` ASC";
                    // var_dump($sql);
                    $pdo = parent::executeRequest($sql);
                    while ($pdoLine = $pdo->fetch()) {
                        $contact = $this->createContact($pdoLine);
                        array_push($contacts, $contact);
                    }
                }
            } else {
                $errMsg = "désolé, une erreur s'est produite lors de la recherche de '$search'!";
                $response->addError($errMsg, MyError::FATAL_ERROR);
            }
        }
        return $contacts;
    }

    /**
     * Filter the search
     * + remove pseudo that blocked the current user
     * @param Response $response to push in occured errors
     * @param string[] $pseudos pseudo to filter
     * @return array of valid pseudos
     */
    private function filterSearch(Response $response, $pseudos)
    {
        $pseudo = $this->getPseudo();
        $sql = "SELECT * FROM `Contacts` WHERE pseudo_ != '$pseudo' AND contact = '$pseudo' AND contactStatus = 'blocked'";
        $pdo = parent::executeRequest($sql);
        if ($pdo->errorInfo()[0] == self::PDO_SUCCEESS) {
            $badPseudos = $pdo->fetchAll(PDO::FETCH_COLUMN);
            if (count($badPseudos) > 0) {
                foreach ($badPseudos as $badPseudo) {
                    if (in_array($badPseudo, $pseudos)) {
                        $key = array_search($badPseudo, $pseudos);
                        $pseudos[$key] = null;
                        unset($pseudos[$key]);
                    }
                }
            }
        } else {
            $errMsg = "désolé, une erreur s'est produite lors de la recherche!";
            $response->addError($errMsg, MyError::FATAL_ERROR);
        }
        return $pseudos;
    }

    // /**
    //  * To update feed's messages
    //  * @param Response $response to push in occured errors
    //  * @param string $discuID discussion's id
    //  * @param object $lastMsg feed's last message
    //  * + last message's id: $lastMsg->{Message::KEY_MSG_ID}
    //  * @param object[] $messages feed's message to update status
    //  * + message's id: $msgs[0->x]->{Message::KEY_MSG_ID}
    //  * + message's status: $msgs[0->x]->{Message::KEY_STATUS}
    //  */
    // public function updateFeed(Response $response, $discuID, $lastMsg, $messages)
    // {
    //     if (!empty($lastMsg->{Message::KEY_MSG_ID})) {
    //         $discussion = $this->getDiscussion($discuID);
    //         $msgSetDate = $discussion->getMessage($lastMsg->{Message::KEY_MSG_ID})->getSetDate();
    //     } else {
    //         $msgSetDate = date('Y-m-d H:i:s', 0);
    //     }

    //     if (count($messages) > 0) {
    //         $this->checkMessageStatus($response, $discuID, $messages);
    //     }

    //     $this->getLastForeignMessages($response, $discuID, $msgSetDate);
    //     $this->getLastMessage($response, $discuID, $msgSetDate);

    //     if (!$response->containError()) {
    //         $response->addResult(Discussion::DISCU_ID, $discuID);
    //     }
    // }

    // /**
    //  * To check if message(s) have been read
    //  * @param Response $response to push in occured errors
    //  * @param string $discuID discussion's id
    //  * @param object[] $messages feed's message to update status
    //  * + message's id: $msgs[0->x]->{Message::KEY_MSG_ID}
    //  * + message's status: $msgs[0->x]->{Message::KEY_STATUS}
    //  */
    // private function checkMessageStatus(Response $response, $discuID, $messages)
    // {
    //     $sql = 'SELECT * FROM `Messages` WHERE `discuId`= "' . $discuID . '" AND `msgStatus` = "' . Message::MSG_STATUS_SEND . '"';
    //     $pdo = $this->executeRequest($sql);
    //     if ($pdo->errorInfo()[0] != self::PDO_SUCCEESS) {
    //         $errMsg = $pdo->errorInfo()[1];
    //         $response->addError($errMsg, MyError::FATAL_ERROR);
    //     } else {
    //         $msgIDs = $pdo->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_GROUP);
    //         $toUpdate = [];
    //         foreach ($messages as $message) {
    //             $msgID = $message->{Message::KEY_MSG_ID};
    //             if (!key_exists($msgID, $msgIDs)) {
    //                 array_push($toUpdate, $msgID);
    //             }
    //         }
    //         (count($toUpdate) > 0) ? $response->addResult(Message::KEY_MSG_ID, $toUpdate) : null;
    //     }
    // }

    // /**
    //  * To get a discussion's last messages
    //  * + messages find are pushed in $response
    //  * @param Response $response to push in occured errors
    //  * @param string $discuID discussion's id
    //  * @param string $msgSetDate date of the last message sent in the discussion 
    //  * + format: 'YYYY-MM-DD HH:MM:SS'
    //  */
    // private function getLastForeignMessages(Response $response, $discuID, $msgSetDate)
    // {
    //     $pseudo = $this->getPseudo();
    //     // $msgSetDate = $message->getSetDate();
    //     $sql = "SELECT * FROM `Messages` 
    //         WHERE (`discuId`= '$discuID') 
    //             AND (`from_pseudo` != '$pseudo') 
    //             AND (`msgSetDate` > '$msgSetDate')
    //         ORDER BY `Messages`.`msgSetDate` DESC";
    //     $pdo = $this->executeRequest($sql);
    //     if ($pdo->errorInfo()[0] != self::PDO_SUCCEESS) {
    //         $errMsg = $pdo->errorInfo()[1];
    //         $response->addError($errMsg, MyError::FATAL_ERROR);
    //     } else {
    //         if ($pdo->rowCount() > 0) {
    //             $lastMsgs = [];
    //             while ($pdoLine = $pdo->fetch()) {
    //                 $newMsg = $this->createMessage($pdoLine);
    //                 $key = strtotime($newMsg->getSetDate());
    //                 $lastMsgs[$key] = $newMsg;
    //             }
    //             ksort($lastMsgs);
    //             $response->addResult(Message::KEY_MESSAGE, $lastMsgs);
    //         }
    //     }
    // }

    /**—————————————————————————————————————————————————————————————————————————————————————————————————— */
    /**————————————————————————————————————————— V2.0 DOWN ——————————————————————————————————————————————— */
    /**—————————————————————————————————————————————————————————————————————————————————————————————————— */

    /**
     * To check if message(s) have the given status
     * @param Response $response to push in occured errors
     * @param string $discuID discussion's id
     * @param string[] $msgIDs id of messages to check status
     * @return string[] id of $msgIDs that match the given status
     */
    public function checkMessageStatus(Response $response, $status, $discuID, $msgIDs)
    {
        $toUpdate = [];
        // $sql = 'SELECT * FROM `Messages` WHERE `discuId`= "' . $discuID . '" AND `msgStatus` = "' . Message::MSG_STATUS_SEND . '"';
        $sql = "SELECT * FROM `Messages` WHERE `discuId`= '" . $discuID . "' AND `msgStatus` = '" . $status . "'; "; // read
        $pdo = $this->executeRequest($sql);
        if ($pdo->errorInfo()[0] != self::PDO_SUCCEESS) {
            $errMsg = $pdo->errorInfo()[1];
            $response->addError($errMsg, MyError::FATAL_ERROR);
        } else {
            $dbMsgIDs = $pdo->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_GROUP);
            // $toUpdate = [];
            foreach ($msgIDs as $msgID) {
                // $msgID = $message->{Message::KEY_MSG_ID};
                // var_dump($msgID);
                if (key_exists($msgID, $dbMsgIDs)) {
                    array_push($toUpdate, $msgID);
                }
            }
            // (count($toUpdate) > 0) ? $response->addResult(Message::KEY_STATUS, $toUpdate) : null;
        }
        return $toUpdate;
    }

    /**
     * To get a discussion's last messages
     * + messages find are pushed in $response
     * @param Response $response to push in occured errors
     * @param string $discuID discussion's id
     * @param string $msgSetDate date of the last message sent in the discussion 
     * + format: 'YYYY-MM-DD HH:MM:SS'
     * @return Message[] discussion's last messages
     */
    public function getLastForeignMessages(Response $response, $discuID, $msgSetDate)
    {
        $lastMsgs = [];
        $pseudo = $this->getPseudo();
        // $msgSetDate = $message->getSetDate();
        $sql = "SELECT * FROM `Messages` 
            WHERE (`discuId`= '$discuID') 
                AND (`from_pseudo` != '$pseudo') 
                AND (`msgSetDate` > '$msgSetDate')
            ORDER BY `Messages`.`msgSetDate` DESC";
        $pdo = $this->executeRequest($sql);
        if ($pdo->errorInfo()[0] != self::PDO_SUCCEESS) {
            $errMsg = $pdo->errorInfo()[1];
            $response->addError($errMsg, MyError::FATAL_ERROR);
        } else {
            if ($pdo->rowCount() > 0) {
                // $lastMsgs = [];
                while ($pdoLine = $pdo->fetch()) {
                    $newMsg = $this->createMessage($pdoLine);
                    $key = strtotime($newMsg->getSetDate());
                    $lastMsgs[$key] = $newMsg;
                }
                ksort($lastMsgs);
                // $response->addResult(Message::KEY_MESSAGE, $lastMsgs);
            }
        }
        return $lastMsgs;
    }

    /**
     * To get a discussion's last message
     * + messages find are pushed in $response
     * @param Response $response to push in occured errors
     * @param string $discuID discussion's id
     * @param string $msgSetDate date of the last message sent in the discussion 
     * + format: 'YYYY-MM-DD HH:MM:SS'
     * @return Message discussion's last message
     */
    public function getLastMessage(Response $response, $discuID, $msgSetDate)
    {
        $lastMsg = null;
        $pseudo = $this->getPseudo();
        // $msgSetDate = $message->getSetDate();
        $sql = "SELECT * FROM `Messages` 
            WHERE (`discuId`= '$discuID') 
                AND (`msgSetDate` > '$msgSetDate')
            ORDER BY `Messages`.`msgSetDate` DESC";
        $pdo = $this->executeRequest($sql);
        if ($pdo->errorInfo()[0] != self::PDO_SUCCEESS) {
            $errMsg = $pdo->errorInfo()[1];
            $response->addError($errMsg, MyError::FATAL_ERROR);
        } else {
            if ($pdo->rowCount() > 0) {
                $pdoLine = $pdo->fetch();
                $lastMsg = $this->createMessage($pdoLine);
                // while ($pdoLine = $pdo->fetch()) {
                //     $newMsg = $this->createMessage($pdoLine);
                //     $key = strtotime($newMsg->getSetDate());
                //     $lastMsgs[$key] = $newMsg;
                // }
                // ksort($lastMsgs);
                // $response->addResult(Message::KEY_LAST_MSG, $lastMsg);
            }
        }
        return $lastMsg;
    }

    /**
     * Get user's new discussions
     * + its check if id of discussion are all in user's discussion attribut
     * + new discussion are discussion that no appear in user's page
     * @param Response $response to push in occured errors
     * @param string[] $discuID discussion's id
     * @return Discussion[] user's new discussions
     */
    public function getNewDiscussions($discuIDs)
    {
        $newDiscus = [];
        $discussions = $this->getDiscussions();
        if (empty($discuIDs)) {
            return $discussions;
        } else {
            foreach ($discussions as $unix => $discussion) {
                $discuID = $discussion->getDiscuID();
                if (!in_array($discuID, $discuIDs)) {
                    $newDiscus[$unix] = $discussions[$unix];
                }
            }
        }
        krsort($newDiscus);
        return $newDiscus;
    }

    /**
     * Create message object with db line from Messages table
     * @param string[] $pdoLine db line from Messages table
     * @return Message message object
     */
    private function createMessage($pdoLine)
    {
        $msgID = $pdoLine["msgID"];
        $pseudo = $pdoLine["from_pseudo"];
        $from = new User($pseudo);
        $privK = $pdoLine["msgPrivateK"];
        $type = $pdoLine["msgType"];
        $msg = $pdoLine["msg"];
        $status = $pdoLine["msgStatus"];
        $setDate = $pdoLine["msgSetDate"];
        $msgObj = new Message($privK, $from, $type, $msg, $status, $msgID, $setDate);
        return $msgObj;
    }

    /*———————————————————————————— ADMIN FUNCTION DOWN ——————————————————————*/
    /**
     * To update a user's permission attribut
     * @param Response $response to push in occured errors
     * @param string $pseudo pseudo of the user to update his permission
     * @param string $permi permission to give to the user
     */
    public function updatePermission(Response $response, $pseudo, $permi)
    {
        if (($permi != self::PERMIT_ADMIN)
            && ($permi != self::PERMIT_USER)
            && ($permi != self::PERMIT_BANISHED)
            && ($permi != self::PERMIT_DELETED)
        ) {
            throw new Exception("The permission '$permi' is not supported");
        }
        $user = $this->getUser($pseudo);
        $userPermi = $user->getPermission();
        if (($userPermi == self::PERMIT_DELETED) && ($permi == self::PERMIT_BANISHED)) {
            $errMsg = "Vous ne pouvez pas banir un utilisateur supprimé";
            $response->addError($errMsg, MyError::FATAL_ERROR);
        }

        if (($userPermi == self::PERMIT_BANISHED) && ($permi == self::PERMIT_DELETED)) {
            $errMsg = "Vous ne pouvez pas supprimer un utilisateur bani";
            $response->addError($errMsg, MyError::FATAL_ERROR);
        }
        if (!$response->containError()) {
            $sql = "UPDATE `Users` SET `permission` = '$permi' WHERE `Users`.`pseudo` = '$pseudo';";
            $pdo = $this->executeRequest($sql);
            if ($pdo->errorInfo()[0] != self::PDO_SUCCEESS) {
                $errMsg = $pdo->errorInfo()[1];
                $response->addError($errMsg, MyError::FATAL_ERROR);
            }
        }
    }

    /**
     * To get a user with his pseudo
     * @return User a user
     */
    public function getUser($pseudo)
    {
        $sql = "SELECT * FROM `Users` WHERE `pseudo` = '$pseudo'";
        $pdo = self::executeRequest($sql);
        $pdoLine = $pdo->fetch();
        $user = self::createUser($pdoLine);
        return $user;
    }
    /*———————————————————————————— STATS DOWN ———————————————————————————————*/
    /*———————————————————————————— STATS DOWN ———————————————————————————————*/
    /**
     * To get number of user registered
     * @return int number of user registered
     */
    public static function getNbUsers()
    {
        $sql = "SELECT COUNT(*) as 'nbUser' FROM `Users`";
        $pdo = parent::executeRequest($sql);
        $nbUser = (int) $pdo->fetchAll(PDO::FETCH_COLUMN)[0];
        return $nbUser;
    }

    /**
     * To get all users
     * @return User[] all users
     */
    public static function getUsers()
    {
        $sql = "SELECT * FROM `Users` ORDER BY `Users`.`pseudo` ASC";
        $pdo = self::executeRequest($sql);
        $users = [];
        while ($pdoLine = $pdo->fetch()) {
            $user = self::createUser($pdoLine);
            $users[$user->pseudo] = $user;
            // array_push($users, $user);
        }
        return $users;
    }
}
