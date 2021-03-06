<?php

/**
 * Help view.
 * @author matti
 *
 */
class HilfeView extends AbstractView {
	
	private $introPages = array(
			"ovB7s2dIwCU" => "vid", // introduction video
			"bnote_news" => "Neuerungen in BNote",
			"sicherheit" => "Sicherheitshinweise",
			"support" => "Support / Kontakt"
	);
	
	private $helpPagesDir = "data/help/";
	
	// alphabetically, format: name of the html-file => title
	private $helpPages = array(
			"abstimmung" => "Modul Abstimmung",
			"aufgaben" => "Modul Aufgaben",
			"konfiguration" => "Modul Konfiguration",
			"kontakte" => "Modul Kontakte",
			"mitspieler" => "Modul Mitspieler",
			"proben" => "Modul Proben",
			"probenphase" => "Modul Probenphase",
			"repertoire" => "Modul Repertoire",
			"share" => "Modul Share"
	);
	
	// format: code => description
	private $videos = array(
			"ovB7s2dIwCU" => "Grundlagen und Einführungsvideo",
			"6OTzjJbMsHY" => "Mitgliedersicht",
			"dVJYFbWgj4E" => "Administrations&uuml;berblick",
			"CXCbngJM8zU" => "Benutzer und Kontakte",
			"PCWTS0jq-24" => "Kommunikation",
			"VGCWdZr3reU" => "Locations",
// 			"dWlnssimDzs" => "Repertoire",
// 			"jAmp2H7GaDg" => "Probe",
			"UsqMTUEWNiw" => "Konzerte",
			"A09dIMCfuig" => "Website"
// 			"kbBNbmlC__U" => "Tutorial 10 - Share"
	);
	
	function __construct($ctrl) {
		$this->setController($ctrl);
	}
	
	function start() {
		Writing::h1("Hilfe");
		
		// Help Navigation
		?>
		<table class="help_navigator">
			<tr>
			<td class="help_navigator_menu">
				<div class="help_navigator_menu_topic">Einführung</div>
				<?php
				// show an introduction video
				$active = false;
				foreach($this->introPages as $code => $page) {
					if(isset($_GET["page"]) && $_GET["page"] == $helpPageId) $active = true;
					else if(isset($_GET["vid"]) && $_GET["vid"] == $code) $active = true;
					
					if($page == "vid") {
						$this->writePageLink($this->videos[$code], $this->modePrefix() . "start&vid=" . $code, $active);
					}
					else {
						$this->writePageLink($page, $this->modePrefix() . "start&page=" . $code, $active);
					}
					$active = false;
				}
				?>
						
				<div class="help_navigator_menu_topic">Video Tutorials</div>
				<?php 
				// show all the videos available for this software
				foreach($this->videos as $code => $vid) {
					if($code == "ovB7s2dIwCU") continue;
					if(isset($_GET["vid"]) && $_GET["vid"] == $code) $active = true;
					$this->writePageLink($vid, $this->modePrefix() . "start&vid=" . $code, $active);
					$active = false;
				}
				
				?>
				
				<div class="help_navigator_menu_topic">Hilfeseiten</div>
				<?php
				// show help pages available for this software
				foreach($this->helpPages as $helpPageId => $helpPageTitle) {
					if(isset($_GET["page"]) && $_GET["page"] == $helpPageId) $active = true;
					$this->writePageLink($helpPageTitle, $this->modePrefix() . "start&page=" . $helpPageId, $active);
					$active = false;
				}
				?>
				
			</td>
			<td class="help_navigator_content">
				<?php 
				if(isset($_GET["vid"])) {
					Writing::h2("Video Tutorial");
					echo '<iframe width="560" height="315" src="http://www.youtube.com/embed/' . $_GET["vid"] . '" frameborder="0" allowfullscreen></iframe>';
				}
				else if(isset($_GET["page"])) {
					if(isset($this->helpPages[$_GET["page"]])) $title = $this->helpPages[$_GET["page"]];
					else if(isset($this->introPages[$_GET["page"]])) $title = $this->introPages[$_GET["page"]];
					else $title = $_GET["page"];
					
					echo '<span class="help_page_title">' . $title . '</span>';
					include $this->helpPagesDir . $_GET["page"] . ".html";
				}
				else {
					Writing::p("Bitte wähle eine Hilfeseite.");
				}
				?>
			</td>
			</tr>
		</table>
		<?php
	}
	
	private function writePageLink($title, $href, $isActive = false) {
		$divCls = "help_navigator_menu_page";
		if($isActive) $divCls = "help_navigator_menu_page_active";
		echo '  <a class="help_navigator_menu_page_link" href="' . $href . '">';
		echo '<div class="' . $divCls . '">';
		echo $title . '</div></a>';
	}
	
}

?>