<?php
/**
 * InputTest.
 */

namespace SledgehammerTests\Mvc;

use Sledgehammer\Mvc\Component\Input;
use SledgehammerTests\Core\TestCase;

/**
 * Unittest for the Input class.
 */
class InputTest extends TestCase
{
    public function test_text_input()
    {
        $input = new Input(array('name' => 'input1'));
        $this->assertSame('<input name="input1" />', (string) $input);
    }

    public function test_checkbox()
    {
        $checkbox = new Input(array('type' => 'checkbox'));
        $this->assertSame('<input type="checkbox" />', (string) $checkbox);

        $checkboxWithLabel = new Input(array('type' => 'checkbox', 'label' => 'i agree'));
        $this->assertSame('<label><input type="checkbox" />&nbsp;i agree</label>', (string) $checkboxWithLabel);
        
        $checked1 = new Input(array('type' => 'checkbox', 'checked'));
        $this->assertSame('<input type="checkbox" checked />', (string) $checked1);
    }

    public function test_select()
    {
        $select = new Input(array('type' => 'select', 'options' => array('option1', 'option2')));
        $this->assertSame('<select><option>option1</option><option>option2</option></select>', (string) $select);
    }

    public function test_textarea()
    {
        $textarea = new Input(array('type' => 'textarea', 'value' => '"'));
        $this->assertSame('<textarea>&quot;</textarea>', (string) $textarea);
    }
    
    public function test_value()
    {
        $input = new Input(['value' => '1']);
        $this->assertSame('1', $input->getValue());
        $this->assertSame('1', $input->getAttribute('value'));

        $checkbox = new Input(['type' => 'checkbox']);
        $this->assertSame(false, $checkbox->getValue());
        $this->assertSame(null, $checkbox->getAttribute('value'));
        $this->assertSame('<input type="checkbox" />', (string) $checkbox);

        $checkboxWithValue = new Input(['type' => 'checkbox', 'value' => 'something']);
        $this->assertSame(null, $checkboxWithValue->getValue());
        $this->assertSame('something', $checkboxWithValue->getAttribute('value'));

        $this->assertSame('<input type="checkbox" value="something" />', (string) $checkboxWithValue);
    }
}
