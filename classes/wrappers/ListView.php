<?php
/**
 * De ListView verbind de List en de View objecten waardoor je List objecten direct gebruiken in een template, of gegevens tonen via de Table of InteractiveTable componenten. 
 *
 * @package MVC
 */

class ListView extends Object implements Iterator, Countable {

	public
		$list,
		$view;

	/**
	 * 
	 * @param Iterator $list
	 * @param AbstractView $view
	 */		
	function __construct($list, $view) {
		$this->list = $list;
		$this->view = $view;
	}
	function current() {
		$data = $this->list->current();
		$this->view->setData($data);
		return clone $this->view; // Geef een kopie van het view object, anders zal een iterator_to_array niet werken. 
	}
	function next() {
		return $this->list->next();
	}
	function key() {
		return $this->list->key();
	}
	function valid() {
		return $this->list->valid();
	}
	function rewind() {
		return $this->list->rewind();
	}
	function count() {
		return $this->list->count();
	}
}
?>
