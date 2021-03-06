<?php

/*************************
 * UPGRADES THE DATABASE *
 * @author Matti Maier   *
 * Update 2.5.0			 *
 *************************/

// path to src/ folder
$PATH_TO_SRC = "src/";

?>

<html>
<head>
	<title>Database Update / Check</title>
</head>
<body>

<?php 

// include necessary libs
require_once "dirs.php";
require_once $PATH_TO_SRC . "data/systemdata.php";
require_once $PATH_TO_SRC . "presentation/widgets/error.php";

class UpdateDb {
	
	private $sysdata;
	private $db;
	private $regex;
	
	private $tabs; // existing tables
	private $mods; // existing modules
	
	function __construct() {
		// build DB connection
		$this->sysdata = new Systemdata();
		$this->db = $this->sysdata->dbcon;
		$this->regex = $this->sysdata->regex;
		
		$this->loadTabs();
		$this->loadMods();
	}
	
	private function loadTabs() {
		$tabs = $this->db->getSelection("SHOW TABLES");
		$tables = array();
		for($i = 1; $i < count($tabs); $i++) {
			array_push($tables, $tabs[$i][0]);
		}
		$this->tabs = $tabs;
	}
	
	private function loadMods() {
		$this->mods = $this->sysdata->getInnerModuleArray();
	}

	function message($msg) {
		echo "<i>$msg</i><br/>\n";
	}
	
	function addColumnToTable($table, $column, $type, $options = "") {
		$fields = $this->db->getFieldsOfTable($table);
		if(!in_array($column, $fields)) {
			$query = "ALTER TABLE $table ADD $column $type $options";
			$this->db->execute($query);
			$this->message("Column $column added to table $table.");
		}
		else {
			$this->message("Column $column already exists in table $table.");
		}
	}
	
	function addTable($table, $definition) {
		if(!in_array($table, $this->tabs)) {
			$this->db->execute($definition);
			$this->message("Table $table created.");
		}
		else {
			$this->message("Table $table already exists.");
		}
	}
	
	function addDynConfigParam($param, $default, $active = 1) {
		$confParams = $this->db->getSelection("SELECT param FROM configuration");
		$containsParam = false;
		foreach($confParams as $i => $row) {
			if($row["param"] == $param) {
				$containsParam = true;
				break;
			}
		}
		if(!$containsParam) {
			$query = "INSERT INTO configuration (param, value, is_active) VALUES ";
			$query .= "('$param', '$default', $active)";
			$this->db->execute($query);
			$this->message("Added configuration parameter $param.");
		}
		else {
			$this->message("<i>Configuration parameter $param exists.");
		}
	}
	
	function addModule($modname) {
		if(!in_array($modname, $mods)) {
			// add new module
			$query = 'INSERT INTO module (name) VALUES ("' . $modname . '")';
			$modId = $db->execute($query);
		
			$this->message("New module $modname (ID $modId) added.");
		
			// add privileges for super user
			$users = $this->sysdata->getSuperUsers();
		
			$query = "INSERT INTO privilege (user, module) VALUES ";
			for($i = 0; $i < count($users); $i++) {
				if($i > 0) $query .= ",";
				$query .= "(" . $users[$i] . ", " . $modId . ")";
			}
			if(count($users) > 0) {
				$db->execute($query);
				$this->message("Privileges for module $modId added for all super users.");
			}
			else {
				$this->message("Please add privileges yourself, since no super users are configured.");
			}
		
		}
		else {
			$this->message("Module $modname already exists.");
		}
	}
	
	function createFolder($path) {
		if(file_exists($path)) {
			$this->message("Folder $path already exists.");
		}
		else {
			if(mkdir($path)) {
				$this->message("Folder $path was created.");
			}
			else {
				$this->message("Failed to create folder $path.");
			}
		}
	}
}

$update = new UpdateDb();

?>


<p><b>This script updates BNote's database structure. Please make sure it is only executed once!</b></p>

<h3>Log</h3>
<p>
<?php 

// Task 1: Insert Configuration
$update->addDynConfigParam("allow_zip_download", "1");
$update->addDynConfigParam("rehearsal_show_max", "5");
$update->addDynConfigParam("updates_show_max", "5");
$update->addDynConfigParam("discussion_on", "1");

// Task 2.1: Adapt songs table
$update->addColumnToTable("song", "bpm", "int(3)", "AFTER length");
$update->addColumnToTable("song", "music_key", "varchar(40)", "AFTER bpm");

// Task 2.2: Add table song_solist
$update->addTable("song_solist", 
		"CREATE TABLE IF NOT EXISTS `song_solist` (
			`song` int(11) NOT NULL,
			`contact` int(11) NOT NULL,
			`notes` varchar(200) DEFAULT NULL,
			PRIMARY KEY (`song`,`contact`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

// Task 3: Add temporary zip configuration
$update->createFolder("data/share/_temp");

// Task 4.1: Adapt rehearsal table
$update->addColumnToTable("rehearsal", "approve_until", "datetime", "AFTER end");

// Task 4.2: Adapt concert table
$update->addColumnToTable("concert", "approve_until", "datetime", "AFTER end");

// Task 5: Adapt vote_option_user table
$update->addColumnToTable("vote_option_user", "choice", "int(1) DEFAULT 1");

// Task 6.1: Add table comment
$update->addTable("comment",
		"CREATE TABLE IF NOT EXISTS `comment` (
		`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		`author` int(11) NOT NULL,
		`created_at` datetime NOT NULL,
		`otype` char(2) NOT NULL,
		`oid` int(10) unsigned NOT NULL,
		`message` text,
		PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

// Task 6.2 Adapt user table
$update->addColumnToTable("user", "email_notification", "int(1) DEFAULT 1");

?>
<br/><br/>
<b><i>COMPLETE.</i></b>
</p>

</body>
</html>