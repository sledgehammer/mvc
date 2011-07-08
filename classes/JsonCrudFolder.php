<?php
/**
 * Folder die standaard CRUD acties op een Record uitvoerd.
 * 
 * @package MVC
 */
namespace SledgeHammer;
class JsonCrudFolder extends VirtualFolder {
	
	public
		$requireDataOnSave = 1; // int Controleer bij de create() & update() of er $_POST data is verstuurd.  
		
	protected 
		$record,
		$subject,
		$primaryKey = 'id'; // @var string $id  Wordt gebruik om te id uit de $_REQUEST te halen.  $idValue = $_POST[$this->id] 
		
	/**
	 * 
	 * @param Record $record in static mode
	 * @param array options array('subject' => 'Klant', 'protected', 'primarykey' => 'klt_id')
	 */
	function __construct($record, $options = array()) {
		parent::__construct();
		$this->handle_filenames_without_extension = true;
		$this->record = $record;
		if (isset($options['primarykey'])) {
			$this->primaryKey = $options['primarykey'];
		}
		if (isset($options['subject'])) {
			$this->subject = $options['subject'];
		} else {
			$this->subject = strtolower(get_class($this->record)); // @todo "cammelCase" omzetten naar "cammel case"
		}
	}

	function execute() {
		try {
			return parent::execute();
		}
		catch (\Exception $e) {
			return jsonError($e);
		}
	}
	
	/**
	 * Stuur de gegevens van het record naar de client
	 * 
	 * @throws Exception on failure
	 * @return Json
	 */
	function read() {
		$record = $this->record->find($_REQUEST[$this->primaryKey]);
		$data = get_object_vars($record);
		return new Json(array(
			'success' => true,
			'data' => $data
		));
	}
	
	/** 
	 * @throws Exception on failure
	 * @return Json
	 */
	function update() {
		$record = $this->record->find($_REQUEST[$this->primaryKey]);
		set_object_vars($record, $this->getNewValues());
		$record->save();
		return new Json(array(
			'success' => true,
			$this->primaryKey => $record->getId()
		));
	}
	
	/** 
	 * @throws Exception on failure
	 * @return Json
	 */
	function create() {
		$record = $this->record->create();
		set_object_vars($record, $this->getNewValues());
		$record->save();
		return new Json(array(
			'success' => true,
			$this->primaryKey => $record->getId()
		));
	}
	
	/**
	 * Aan de hand van de id bepalen of er een record toegevoegd of bijgewerkt moet worden.
	 *
	 * @throws Exception on failure
	 * @return Json
	 */
	function save() {
		if (empty($_REQUEST[$this->primaryKey])) {
			// Converteer een eventuele "" naar null
			if (value($_POST[$this->primaryKey]) === '') {
				$_POST[$this->primaryKey] = null;
			}
			return $this->create();
		} else {
			return $this->update();
		}
	}
	
	/**
	 * De record verwijderen
	 * 
	 * @throws Exception on failure
	 * @return Json
	 */
	function delete() {
		$this->record->delete($_POST[$this->primaryKey]);
		//throw new Exception('Verwijderen van '.$this->subject.' #'.$_POST[$this->primarykey].' is mislukt');
		return new Json(array(
			'success' => true
		));		
	}
	
	/**
	 * @todo $_POST data filteren zodat eventuele $_POST elementen geen fouten geven set_object_vars() 
	 * @return array
	 */
	protected function getNewValues() {
		if ($_SERVER['REQUEST_METHOD'] != 'POST') {
			throw new \Exception('Invalid request-method "'.$_SERVER['REQUEST_METHOD'].'", expecting "POST"');
		}
		if(count($_POST) < $this->requireDataOnSave) {
			throw new \Exception('Er zijn onvoldoende gegevens verstuurd. (Minimaal '.$this->requireDataOnSave.' $_POST variabele is vereist)');
		}
		return $_POST;
	}
}
?>
