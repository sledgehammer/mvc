<?php
/**
 * FormTest
 */
namespace Sledgehammer;
/**
 * Unittest for the Form class
 * @package MVC
 */
class FormTest extends TestCase {

	function test_render() {
		$form = new Form(array(
			'action' => '/',
			'fields' => array(
				new Input(array('name' => 'field1'))
			)
		));
		$this->assertSame("<form method=\"post\" action=\"/\">\n<input type=\"text\" name=\"field1\" />\n</form>", view_to_string($form));
	}

	function test_render_with_labels() {
		$form = new Form(array(
			'action' => '/',
			'fields' => array(
				'Label1' => new Input(array('name' => 'field1'))
			)
		));
		$this->assertSame("<form method=\"post\" action=\"/\">\n<label>Label1</label><input type=\"text\" name=\"field1\" />\n</form>", view_to_string($form));
	}

	function test_render_with_control_groups() {
		$form = new Form(array(
			'action' => '/',
			'class' => 'form-horizontal',
			'fields' => array(
				'Label1' => new Input(array('name' => 'field1'))
			)
		));
		$this->assertSame("<form method=\"post\" action=\"/\" class=\"form-horizontal\">\n<div class=\"control-group\"><label class=\"control-label\">Label1</label><div class=\"controls\"><input type=\"text\" name=\"field1\" /></div></div>\n</form>", view_to_string($form));
	}

	function test_import() {
		$form = new Form(array(
			'action' => '/',
			'fields' => array(
				new Input(array('name' => 'field1'))
			)
		));
		$data = $form->import($error, array('field1' => 'value1'));
		// @todo assertions
	}

}

?>