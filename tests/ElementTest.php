<?php

namespace SledgehammerTests\Mvc;

use Sledgehammer\Mvc\Component\Element;
use SledgehammerTests\Core\TestCase;

/**
 * Unittest for the Input class.
 */
class ElementTest extends TestCase
{
    public function testAttr()
    {
        $el = new Element(array('title' => 'Hello'));
        $this->assertEquals('<div title="Hello"></div>', (string) $el);
        $this->assertEquals('Hello', $el->attr('title'), '.attr( attributeName ) returns the attribute');
        $this->assertEquals(null, $el->attr('foo'));
        //
        $el->attr(array('disabled', 'title' => 'Goodbye'));
        $this->assertEquals('<div title="Goodbye" disabled></div>', (string) $el, '.attr( attributes ) set the attribute value');
        $this->assertTrue($el->attr('disabled'), 'attributes without value are converted to (boolean) true');

        $el->attr('title', 'Changed');
        $this->assertEquals('<div title="Changed" disabled></div>', (string) $el, '.attr( attribute, value) set the attribute');
        $el->attr('disabled', '')->attr('title', 'Chained');
        $this->assertEquals('<div title="Chained"></div>', (string) $el, '.attr( attribute, "") removed the attribute');
    }

    public function testAddClass()
    {
        $el = new Element(array('class' => 'alert'));
        $el->addClass('alert-danger');
        $this->assertEquals('<div class="alert alert-danger"></div>', (string) $el);
    }
}
