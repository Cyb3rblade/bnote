<?php
/**
 * View for configuration module.
 * @author matti
 *
 */
class AufgabenView extends CrudRefView {

	/**
	 * Create the start view.
	 */
	function __construct($ctrl) {
		$this->setController($ctrl);
		$this->setEntityName("Aufgabe");
		$this->setJoinedAttributes(array(
				"created_by" => array("name", "surname"),
				"assigned_to" => array("name", "surname")
				));
	}
	
	protected function showAdditionStartButtons() {
		$this->buttonSpace();
		$grpTask = new Link($this->modePrefix() . "addGroupTask", "Gruppenaufgabe hinzufügen");
		$grpTask->addIcon("add");
		$grpTask->write();
		
		$this->buttonSpace();
		if(isset($_GET["table"]) && $_GET["table"] == "completed") {
			$showOpen = new Link($this->modePrefix() . "start&table=open", "Offene Aufgaben anzeigen");
			$showOpen->addIcon("note");
			$showOpen->write();
		}
		else {
			$showCompleted = new Link($this->modePrefix() . "start&table=completed", "Abgeschlossene Aufgaben anzeigen");
			$showCompleted->addIcon("note");
			$showCompleted->write();
		}
	}
	
	protected function showAllTable() {
		// show table rows
		if(isset($_GET["table"]) && $_GET["table"] == "completed") {
			$data = $this->getData()->getTasks(false);
		}
		else {
			$data = $this->getData()->getTasks();
		}
		$table = new Table($data);
		$table->setEdit("id");
		$table->removeColumn("id");
		$table->renameHeader("creator", "Erstellt von");
		$table->renameHeader("assignee", "Verantwortlicher");
		$table->removeColumn(4);
		$table->removeColumn("created_by");
		$table->removeColumn(6);
		$table->removeColumn("assigned_to");
		if(!isset($_GET["table"]) || $_GET["table"] == "open") {
			$table->removeColumn(8);
			$table->removeColumn("completed_at");
		}
		$table->renameAndAlign($this->getData()->getFields());
		$table->setColumnFormat("created_at", "DATE");
		$table->setColumnFormat("due_at", "DATE");
		$table->write();
	}
	
	private function getAddForm($mode = "add") {
		$form = new Form($this->getEntityName() ." hinzuf&uuml;gen", $this->modePrefix() . $mode);
		$form->addElement("Titel", new Field("title", "", FieldType::CHAR));
		$form->addElement("Beschreibung", new Field("description", "", FieldType::TEXT));
		$form->addElement("Fällig am", new Field("due_at", "", FieldType::DATETIME));
		
		return $form;
	}
	
	protected function addEntityForm() {
		$form = $this->getAddForm();
		$form->addElement("Verantwortlicher", new Field("assigned_to", "", FieldType::REFERENCE));
		$currContactId = $this->getData()->getUserContactId();
		$form->setForeign("Verantwortlicher", "contact", "id", "CONCAT(name, ' ', surname)", $currContactId);
		$form->write();
	}
	
	function addGroupTask() {
		$form = $this->getAddForm("process_addGroupTask");
		$groups = $this->getData()->adp()->getGroups();
		$selector = new GroupSelector($groups, array(), "group");
		$form->addElement("Verantwortliche Gruppe(n)", $selector);
		$form->write();
		
		$this->verticalSpace();
		$this->backToStart();
	}
	
	function process_addGroupTask() {
		$groups = GroupSelector::getPostSelection($this->getData()->adp()->getGroups(), "group");
		$values = $_POST;
		$this->getData()->validate($values);
		
		foreach($groups as $i => $gid) {
			$contacts = $this->getData()->adp()->getGroupContacts($gid);
			for($j = 1; $j < count($contacts); $j++) {
				$values["Verantwortlicher"] = $contacts[$j]["id"];
				if($values["Verantwortlicher"] == "") continue;
				$this->getData()->create($values);
			}
		}
		new Message("Aufgabe hinzugefügt", "Die Aufgabe wurde allen Mitgliedern der ausgewählten Gruppen hinzugefügt.");
		$this->backToStart();
	}
	
	protected function viewDetailTable() {
		$dv = new Dataview();
		$task = $this->getData()->findByIdNoRef($_GET["id"]);
		$dv->autoAddElements($task);
		$dv->autoRename($this->getData()->getFields());
		$dv->removeElement("Erstellt von");
		$dv->addElement("Erstellt von", $this->getData()->getContactname($task["created_by"]));
		$dv->removeElement("Verantwortlicher");
		$dv->addElement("Verantwortlicher", $this->getData()->getContactname($task["assigned_to"]));
		$dv->write();
	}
	
	protected function additionalViewButtons() {
		if($this->getData()->isTaskComplete($_GET["id"])) {
			$markTodo = new Link($this->modePrefix() . "markTask&as=open&id=" . $_GET["id"], "Als offen markieren");
			$markTodo->addIcon("note");
			$markTodo->write();
		}
		else {
			$markComplete = new Link($this->modePrefix() . "markTask&as=complete&id=" . $_GET["id"], "Als abgeschlossen markieren");
			$markComplete->addIcon("checkmark");
			$markComplete->write();
		}
		$this->buttonSpace();
	}
	
	protected function editEntityForm() {
		$task = $this->getData()->findByIdNoRef($_GET["id"]);
		
		$form = new Form($this->getEntityName() ." bearbeiten", $this->modePrefix() . "edit_process&id=" . $_GET["id"]);
		$form->addElement("Titel", new Field("title", $task["title"], FieldType::CHAR));
		$form->addElement("Beschreibung", new Field("description", $task["description"], FieldType::TEXT));
		$form->addElement("Fällig am", new Field("due_at", Data::convertDateFromDb($task["due_at"]), FieldType::DATETIME));
		$form->addElement("Verantwortlicher", new Field("assigned_to", "", FieldType::REFERENCE));
		$form->setForeign("Verantwortlicher", "contact", "id", "CONCAT(name, ' ', surname)", $task["assigned_to"]);
		$form->write();
	}
	
	public function markTask() {
		if($_GET["as"] == "complete") {
			$is_complete = 1;
		}
		else {
			$is_complete = 0;
		}
		$tid = $_GET["id"];
		$this->getData()->markTask($tid, $is_complete);
		$this->view();
	}
}