<?php
/**
 * InputTest.
 */

namespace SledgehammerTests\Mvc;

/**
 * Unittest for the Input class.
 */
class InputTest extends TestCase
{
    public function test_text_input()
    {
        $input = new Input(array('name' => 'input1'));
        $this->assertSame('<input name="input1" type="text" />', view_to_string($input));
    }

    public function test_checkbox()
    {
        $checkbox = new Input(array('type' => 'checkbox'));
        $this->assertSame('<input type="checkbox" />', view_to_string($checkbox));

        $checkbox = new Input(array('type' => 'checkbox', 'label' => 'i agree'));
        $this->assertSame('<label><input type="checkbox" />&nbsp;i agree</label>', view_to_string($checkbox));
    }

    public function test_select()
    {
        $select = new Input(array('type' => 'select', 'options' => array('option1', 'option2')));
        $this->assertSame('<select><option>option1</option><option>option2</option></select>', view_to_string($select));
    }

    public function test_textarea()
    {
        $textarea = new Input(array('type' => 'textarea', 'value' => '"'));
        $this->assertSame('<textarea>&quot;</textarea>', view_to_string($textarea));
    }
}
