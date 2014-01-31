<?php

/**
 * Interface definition for BNote App (BNA) connection.
 * @author Matti
 *
 */
interface iBNA {
	
	/**
	 * @return Returns all rehearsals.
	 */
	public function getRehearsals();
	
	/**
	 * Retrieves all rehearsals for a user and whether he/she participates or not.
	 * @param Integer $user ID of the user.
	 * @return All rehearsal with participation information for the user.
	 */
	public function getRehearsalsWithParticipation($user);

	/**
	 * @return Returns all concerts.
	 */
	public function getConcerts();
	
	/**
	 * @return Returns all contacts.
	 */
	public function getContacts();
	
	/**
	 * @return Returns all locations.
	 */
	public function getLocations();
	
	/**
	 * <strong>Since version 2.4.3 this method is not implemented by default and does not
	 * return any results. Please make sure to use other methods!</strong>
	 * @deprecated Please use getLocations() and other methods to retrieve the addresses.
	 * @return Returns all addresses.
	 */
	public function getAddresses();
	
	/**
	 * <b>Use this function only to fetch all data for a user once!</b>
	 * @return Returns all rehearsals, concerts, contacts, location and addresses.
	 */
	public function getAll();
	
	/**
	 * Gets the participation choice of a user for a rehearsal.
	 * @param Integer $rid Rehearsal ID
	 * @param Integer $uid User ID
	 * @return 1 if the user participates, 0 if not, -1 if not chosen yet.
	 */
	public function getParticipation($rid, $uid);
	
	/**
	 * Saves the participation of a user in a rehearsal.
	 * @param Integer $rid Rehearsal ID
	 * @param Integer $uid User ID
	 * @param Integer $part 1=participates, 0=does not participate, 2=maybe participates
	 * @param String $reason Optional parameter to give a reason for not participating.
	 */
	public function setParticipation($rid, $uid, $part, $reason);
}

/******************************************
 * Abstract Implementation Class		  *
*******************************************/

// connect to application
$dir_prefix = "../../";
global $dir_prefix;

require_once $dir_prefix . "dirs.php";
require_once $dir_prefix . $GLOBALS["DIR_DATA"] . "database.php";
require_once $dir_prefix . $GLOBALS["DIR_DATA"] . "regex.php";
require_once $dir_prefix . $GLOBALS["DIR_DATA"] . "systemdata.php";
require_once $dir_prefix . $GLOBALS["DIR_DATA"] . "fieldtype.php";
require_once $dir_prefix . $GLOBALS["DIR_DATA"] . "abstractdata.php";
require_once $dir_prefix . $GLOBALS["DIR_DATA"] . "applicationdataprovider.php";
require_once $dir_prefix . $GLOBALS["DIR_DATA_MODULES"] . "startdata.php";
require_once $dir_prefix . $GLOBALS["DIR_DATA_MODULES"] . "mitspielerdata.php";
require_once $dir_prefix . $GLOBALS["DIR_DATA_MODULES"] . "locationsdata.php";

$GLOBALS["DIR_WIDGETS"] = $dir_prefix . $GLOBALS["DIR_WIDGETS"];
require_once($GLOBALS["DIR_WIDGETS"] . "error.php");

/**
 * Scheme for BNote Interface implementation.
 * @author Matti
 *
 */
abstract class AbstractBNA implements iBNA {
	
	/**
	 * Database instance.
	 * @var Database
	 */
	protected $db;
	
	/**
	 * System data instance.
	 * @var Systemdata
	 */
	protected $sysdata;
	
	/**
	 * Data Access Object for "Start" Module which contains
	 * many valuable functions for this interface.
	 * @var StartData
	 */
	protected $startdata;
	
	/**
	 * The user ID assoicated with the PIN.
	 * @var Integer
	 */
	protected $uid;
	
	function __construct() {
		$this->sysdata = new Systemdata($GLOBALS["dir_prefix"]);
		$this->db = $this->sysdata->dbcon;
		global $system_data;
		$system_data = $this->sysdata;
		$this->uid = -1;
		global $dir_prefix;
		$this->startdata = new StartData($dir_prefix);
		
		$this->authentication();
		$this->route();
	}
	
	/**
	 * Authenticates users with pin.
	 */
	protected function authentication() {
		if(!isset($_GET["pin"])) {
			header("HTTP/1.0 403 Permission Denied.");
			exit();
		}
		else {
			$pin = $_GET["pin"];
		
			$this->uid = $this->db->getCell($this->db->getUserTable(), "id", "pin = $pin");
		
			if($this->uid == null || $this->uid < 1) {
				header("HTTP/1.0 403 Permission Denied.");
				exit();
			}
		}
	}
	
	/**
	 * Routes a request to the correct function.
	 */
	protected function route() {
		$function = "";
		if(!isset($_GET["func"])) {
			header("HTTP/1.0 400 Function not specified.");
			exit();
		}
		else {
			$function = $_GET["func"];
		}
		
		if($function == "getParticipation" || $function == "setParticipation") {
			if(!isset($_GET["rehearsal"])) {
				header("HTTP/1.0 412 Insufficient Parameters.");
				exit();
			}
			else if($function == "getParticipation") {
				$this->getParticipation($_GET["rehearsal"], $this->uid);
			}
			else if($function == "setParticipation") {
				if(!isset($_GET["participation"])) {
					header("HTTP/1.0 412 Insufficient Parameters.");
					exit();
				}
				$part = $_GET["participation"];
				if($part > 1 || $part < -1) {
					$part = -1;
				}
				$reason = "";
				if(isset($_GET["reason"])) {
					$reason = $_GET["reason"];
				}
				$this->setParticipation($_GET["rehearsal"], $this->uid, $part, $reason);
			}
		}
		else if($function == "getRehearsalsWithParticipation") {
			$this->getRehearsalsWithParticipation($this->uid);
		}
		else {
			$this->$function();
		}
	}
	
	/* METHODS TO IMPLEMENT BY SUBCLASSES */
	
	/**
	 * Prints out a statement with which the document start,
	 * e.g. "<?xml ...><entities>".
	 */
	protected abstract function beginOutputWith();
	
	/**
	 * Prints out a statement with which the document ends,
	 * e.g. "</entities>".
	 */
	protected abstract function endOutputWith();
	
	/**
	 * Prints the entities out.
	 * @param Array $entities SQL selection array with the entities.
	 * @param String $nodeName Name of the node in case required, e.g. singluar.
	 */
	protected abstract function printEntities($entities, $nodeName);
	
	
	/* DEFAULT IMPLEMENTATIONS */
	
	function getRehearsals() {
		$entities = $this->startdata->getUsersRehearsals($this->uid);
		$this->printEntities($entities, "rehearsal");
	}
	
	function getRehearsalsWithParticipation($user) {
		if($this->sysdata->isUserSuperUser($this->uid)
				|| $this->sysdata->isUserMemberGroup(1, $this->uid)) {
			$query = "SELECT * ";
			$query .= "FROM rehearsal r LEFT JOIN rehearsal_user ru ON ru.rehearsal = r.id ";
			$query .= "WHERE end > now() AND (ru.user = $user || ru.user IS NULL) ";
			$query .= "ORDER BY begin ASC";
			$rehs = $this->db->getSelection($query);
			$this->printEntities($rehs, "rehearsal");
		}
		else {
			// only get rehearsals for user considering phases and groups
			$rehs = $this->startdata->getUsersRehearsals($this->uid);
			
			// manually join participation
			array_push($rehs[0], "participate");
			array_push($rehs[0], "reason");
			
			for($i = 1; $i < count($rehs); $i++) {
				$rid = $rehs[$i]["id"];
				$query = "SELECT * FROM rehearsal_user WHERE rehearsal = $rid AND user = " . $this->uid;
				$part = $this->db->getRow($query);
				if($part == null) {
					$part = array( "participate" => "", "reason" => "" );
				}
				$rehs[$i]["participate"] = $part["participate"];
				$rehs[$i]["reason"] = $part["reason"];
			}
			
			$this->printEntities($rehs, "rehearsal");
		}
	}
	
	function getConcerts() {
		$concerts = $this->startdata->getUsersConcerts($this->uid);
		$this->printEntities($concerts, "concert");
	}
	
	function getContacts() {
		$msd = new MitspielerData($GLOBALS["dir_prefix"]);
		$contacts = $msd->getMembers($this->uid);

		// TODO: do this right instead of this hack
		for ($i = 0; $i < count($contacts); $i++)
		{
			$contact = $contacts[$i];
			$id = $contact["id"];
			$contact["id"] = "bnoteInstance/contact/" . $id;
			$contact["type"] = "contact";
		//	print_r($contact);
			$contacts[$i] = $contact;
		}
		$this->printEntities($contacts, "contact");
	}
	
	function getLocations() {
		$locData = new LocationsData($GLOBALS["dir_prefix"]);
		$locs = $locData->findAllJoined(array(
			"address" => array("street", "city", "zip")
		));
		$this->printEntities($locs, "location");
	}

	function getAddresses() {
		// this method should not be used anymore starting from version 2.4.3
	}
	
	function getAll() {
		$this->beginOutputWith();
		
		//$this->getRehearsals(); echo "\n";
		$this->getRehearsalsWithParticipation($this->uid); echo "\n";
		
		$this->getConcerts(); echo "\n";
		$this->getContacts(); echo "\n";
		$this->getLocations(); echo "\n";
		$this->getAddresses(); echo "\n";
		$this->endOutputWith();
	}
	
	function getParticipation($rid, $uid) {
		$_SESSION["user"] = $uid;
		$res = $this->startdata->doesParticipateInRehearsal($rid);
		unset($_SESSION["user"]);
		return $res;
	}
	
	function setParticipation($rid, $uid, $part, $reason) {
		$_GET["rid"] = $rid;
		$_SESSION["user"] = $uid;
		
		if($part == 1) {
			// participate
			$_GET["status"] = "yes";
		}
		elseif($part == 2) {
			// maybe participate
			$_POST["rehearsal"] = $rid;
			$_GET["status"] = "maybe";
		}
		else {
			// do not participate
			$_POST["rehearsal"] = $rid;
			$_GET["status"] = "no";
		}
		if($reason == "") {
			$_POST["explanation"] = "nicht angegeben";
		}
		else {
			$_POST["explanation"] = $reason;
		}
		$this->startdata->saveParticipation();
		unset($_SESSION["user"]);
		echo "true";
	}
	
}

?>