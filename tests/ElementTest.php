<?php

namespace SledgehammerTests\Mvc;

use Sledgehammer\Mvc\Component\Element;
use SledgehammerTests\Core\TestCase;

class ElementTest extends TestCase
{
    public function testHasAttribute()
    {
        $el = new Element(['onClick' => 'alert("ok")']);
        $this->assertTrue($el->hasAttribute('onClick'));
        $this->assertTrue($el->hasAttribute('onclick'));
        $this->assertEquals('<div onclick="alert(&quot;ok&quot;)"></div>', (string) $el);
    }

    public function testSetAttribute()
    {
        $el = new Element(['tag' => 'a']);
        $el->setAttribute('href', 'http://github.com');
        $this->assertEquals('<a href="http://github.com"></a>', (string) $el);
        $el->setAttribute('HREF', 'http://example.com');
        $this->assertEquals('<a href="http://example.com"></a>', (string) $el);
    }

    public function testAttr()
    {
        $el = new Element(['title' => 'Hello']);
        $this->assertEquals('<div title="Hello"></div>', (string) $el);
        $this->assertEquals('Hello', $el->attr('title'), '.attr( attributeName ) returns the attribute');
        $this->assertEquals(null, $el->attr('foo'));
        //
        $el->attr(['disabled', 'title' => 'Goodbye']);
        $this->assertEquals('<div title="Goodbye" disabled></div>', (string) $el, '.attr( attributes ) set the attribute value');
        $this->assertTrue($el->attr('disabled'), 'attributes without value are converted to (boolean) true');

        $el->attr('title', 'Changed');
        $this->assertEquals('<div title="Changed" disabled></div>', (string) $el, '.attr( attribute, value) set the attribute');
        $el->attr('disabled', '')->attr('title', 'Chained');
        $this->assertEquals('<div title="Chained"></div>', (string) $el, '.attr( attribute, "") removed the attribute');
    }

    public function testAddClass()
    {
        $el = new Element(['class' => 'alert']);
        $el->addClass('alert-danger');
        $this->assertEquals('<div class="alert alert-danger"></div>', (string) $el);
    }
}
