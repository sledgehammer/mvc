<?php
/**
 * A Document is a standalone component, that can't be wrapped in a layout.
 * Example documents are: Json, FileDocument and HTMLDocument
 *
 * @package MVC
 */
namespace SledgeHammer;
interface Document extends Component {
	
	/**
	 * Determines if the component is a Document.
	 * This allows errors to be wrapped in a layout.
	 * 
	 * @return bool
	 */
	function isDocument();

	/**
	 * The HTTP headers for this type of document.
	 * 
	 * @return array
	 */
	function getHeaders();
}
?>
