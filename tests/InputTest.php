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
    public function testTextInput()
    {
        $input = new Input(['name' => 'input1']);
        $this->assertSame('<input name="input1" />', (string) $input);
    }

    public function testCheckbox()
    {
        $checkbox = new Input(['type' => 'checkbox']);
        $this->assertSame('<input type="checkbox" />', (string) $checkbox);

        $checkboxWithLabel = new Input(['type' => 'checkbox', 'label' => 'i agree']);
        $this->assertSame('<label><input type="checkbox" />&nbsp;i agree</label>', (string) $checkboxWithLabel);
        
        $checked1 = new Input(['type' => 'checkbox', 'checked']);
        $this->assertSame('<input type="checkbox" checked />', (string) $checked1);
    }

    public function testSelect()
    {
        $select = new Input(['type' => 'select', 'options' => ['option1', 'option2']]);
        $this->assertSame('<select><option>option1</option><option>option2</option></select>', (string) $select);
    }

    public function testTextarea()
    {
        $textarea = new Input(['type' => 'textarea', 'value' => '"']);
        $this->assertSame('<textarea>&quot;</textarea>', (string) $textarea);
    }
    
    public function testValue()
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
