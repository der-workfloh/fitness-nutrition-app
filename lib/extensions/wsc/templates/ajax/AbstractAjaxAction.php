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
 * @file AbstractAjaxAction.php
 * @namespace WSC\templates\ajax
 *
 *
 *
 * @author Florian Horn
 * @version
 * Version 1.0, 26.08.2010<br/>
 * Version 1.1, 26.10.2010<br/>
 *
 */

namespace WSC\templates\ajax;

use WSC\core\action\AbstractAction;

abstract class AbstractAjaxAction extends AbstractAction
{
    protected $__sContent = '';


    /**
     * Set the result being printed
     * @param string $sResult
     */
    protected function __forwarding($sResult) 
    { 
        $this->__sContent = $sResult; 
    }

    
    
    /**
     * Get the generated output
     * @return string
     */
    public function getContent() 
    { 
        return $this->__sContent; 
    }
}
