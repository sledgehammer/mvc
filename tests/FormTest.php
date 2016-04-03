<?php

namespace SledgehammerTests\Mvc;

use Sledgehammer\Mvc\Component\Form;
use Sledgehammer\Mvc\Component\Input;
use SledgehammerTests\Core\TestCase;

/**
 * Unittest for the Form class.
 */
class FormTest extends TestCase
{
    public function test_render()
    {
        $form = new Form(array(
            'fields' => array(
                new Input(array('name' => 'field1')),
            ),
        ));
        $this->assertSame("<form method=\"post\">\n<fieldset>\n\t<input name=\"field1\" type=\"text\" />\n</fieldset>\n</form>", \Sledgehammer\component_to_string($form));
    }

    public function test_render_with_labels()
    {
        $form = new Form(array(
            'fieldset' => false,
            'fields' => array(
                'key1' => new Input(array('name' => 'field1', 'label' => 'Label1')),
            ),
        ));
        $this->assertSame("<form method=\"post\">\n\t<label>Label1</label><input name=\"field1\" type=\"text\" />\n</form>", \Sledgehammer\component_to_string($form));
    }

    public function test_import()
    {
        $form = new Form(array(
            'action' => '/',
            'fields' => array(
                'key1' => new Input(array('name' => 'field1')),
            ),
        ));
        $data = $form->import($error, array('field1' => 'value1'));
        $this->assertEquals(array('key1' => 'value1'), $data);
    }
}
