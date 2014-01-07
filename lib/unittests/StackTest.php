<?php
/**
 * <!--
 * This file is part of the wsCatalyst-Extensions (wsC) for the
 * Adventure-PHP-Framework published under
 * https://sourceforge.net/projects/wscatalyst.
 *
 * The wsC is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * The wsC is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with the wsC. If not, see http://www.gnu.org/licenses/lgpl-3.0.txt.
 * -->
 */

/**
 * @file StackTest.php
 * @namespace UNITTESTS
 *
 * Example Unittest
 *
 * @author Florian Horn
 * @version
 * Version 1.0, 26.02.2011<br/>
 *
 */

namespace UNITTESTS;

class StackTest extends \PHPUnit_Framework_TestCase
{
    public function testPushAndPop()
    {
        $stack = array();
        $this->assertEquals(0, \count($stack));
 
        \array_push($stack, 'foo');
        $this->assertEquals('foo', $stack[\count($stack)-1]);
        $this->assertEquals(1, \count($stack));
 
        $this->assertEquals('foo', \array_pop($stack));
        $this->assertEquals(0, \count($stack));
    }
}
