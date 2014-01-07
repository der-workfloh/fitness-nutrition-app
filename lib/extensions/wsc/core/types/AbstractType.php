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
 * @file AbstractType.php
 * @namespace WSC\core\types
 *
 *
 *
 * @author Florian Horn
 * @version
 * Version 1.0, 25.08.2010<br/>
 * Version 2.0, 13.11.2013<br/>
 *
 */

namespace WSC\core\types;

abstract class AbstractType
{
    /**
     * Default
     * @var mixed
     */
    protected $__default;

   
    
    /**
     * Clone
     */
    private function __clone() 
    {}
    
    
    
    /**
     * Construct
     * @param mixed $Value
     */
    public function __construct($Value)
    {
        $this->__internalContaining($Value);
    }

    
    
    /**
     * Internal containing
     */
    abstract protected function __internalContaining($Value);

    
    
    /**
     * toString
     * @return mixed
     */
    public function __toString()
    {
        return $this->__default;
    }

    
    
    /**
     * Invoke
     * @return mixed
     */
    public function __invoke()
    {
        return $this->__default;
    }
}
