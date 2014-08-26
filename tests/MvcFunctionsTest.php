<?php
/**
 * Test the implementation of MVC's global functions
 *
 * @package MVC
 */

namespace Sledgehammer;

class MvcFunctionsTest extends TestCase {

    function test_explode_xml_parmeters() {
        // array( $tag => $expected_result
        $expectations = array(
            // full tag
            '<img src="cms_images/DSCF4821.JPG" height="240" width="320">' => array(
                'src' => 'cms_images/DSCF4821.JPG',
                'height' => '240',
                'width' => '320',
            ),
            // no attributes
            '<br>' => array(),
            // unquoted value
            '<img src="cms_images/DSCF4821.JPG" height=240>' => array(
                'src' => 'cms_images/DSCF4821.JPG',
                'height' => '240',
            ),
            // boolean attribute
            'type="checkbox" checked>' => array(
                'type' => 'checkbox',
                'checked' => true
            ),
            // Excaped value
            'type="input" value="ol&eacute;">' => array(
                'type' => 'input',
                'value' => 'olé'
            ),
            // Weird but valid attributes (http://stackoverflow.com/questions/925994)
            ' 123ab="number?" va"lue=with_a_quote? a/b' => array(
                '123ab' => 'number?',
                'va"lue' => 'with_a_quote?',
                'a' => true,
                'b' => true
            ),
            // 
            'hello="world" <a href="#"' => array(
                'hello' => "world",
                '<a' => true,
                'href' => "#"
            )
        );
        foreach ($expectations as $tag => $expectation) {
            $this->assertEquals($expectation, explode_xml_attributes($tag));
        }
    }

    function test_implode_xml_parmeters() {
        // expected output  => input
        $expectations = [
            ' type="checkbox" checked' => [
                'type' => 'checkbox',
                'checked' => true
            ],
            ' type="input" value="ol&eacute;"' => array(
                'type' => 'input',
                'value' => 'olé'
            ),
            ' type="input" autofocus' => array(
                'type' => 'input',
                'autofocus'
            ),
        ];
        foreach ($expectations as $xml => $attributes) {
            $this->assertEquals($xml, implode_xml_attributes($attributes));
        }
    }
}
