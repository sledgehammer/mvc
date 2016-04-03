<?php

namespace SledgehammerTests\Mvc;

use Sledgehammer\Mvc\Component\Pagination;
use SledgehammerTests\Core\TestCase;

class PaginationTest extends TestCase
{
    public function test_pagination()
    {
        $pager = new Pagination(2, 1, array('href' => '#page'));
        $this->assertEquals(\Sledgehammer\component_to_string($pager), '<div class="pagination"><ul>
	<li class="active"><a href="#page1">1</a></li>
	<li><a href="#page2">2</a></li>
	<li><a href="#page2">&raquo;</a></li>
</ul></div>', 'Pagination should not render a prev button');
    }
}
