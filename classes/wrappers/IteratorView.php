<?php
/**
 * De ListView verbind de List en de View objecten waardoor je List objecten direct gebruiken in een template, of gegevens tonen via de Table of InteractiveTable componenten. 
 *
 * @package MVC
 */
namespace SledgeHammer;
class IteratorView extends Object implements \Iterator, \Countable {

	/**
	 * @var Iterator
	 */
	public $iterator;
	/**
	 * @var AbstractView
	 */
	public $view;

	/**
	 * 
	 * @param Iterator $list
	 * @param AbstractView $view
	 */		
	function __construct($iterator, $view) {
		$this->iterator = $iterator;
		$this->view = $view;
	}
	function current() {
		$data = $this->iterator->current();
		$this->view->setData($data);
		return clone $this->view; // Geef een kopie van het view object, anders zal een iterator_to_array niet werken. 
	}
	function next() {
		return $this->iterator->next();
	}
	function key() {
		return $this->iterator->key();
	}
	function valid() {
		return $this->iterator->valid();
	}
	function rewind() {
		return $this->iterator->rewind();
	}
	function count() {
		return $this->iterator->count();
	}
}
?>
