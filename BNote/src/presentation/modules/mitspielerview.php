<?php

/**
 * View for members module.
 * @author matti
 *
 */
class MitspielerView extends AbstractView {

	/**
	 * Create the start view.
	 */
	function __construct($ctrl) {
		$this->setController($ctrl);
	}
	
	function start() {
		Writing::h1("Mitspieler");
		
		if($this->getData()->getSysdata()->getUsersContact() == "") return;
		$members = $this->getData()->getMembers($_SESSION["user"], false);
		
		$table = new Table($members);
		$table->renameAndAlign($this->getData()->getFields());
		$table->renameHeader("fullname", "Name");
		$table->write();
	}
}

?>