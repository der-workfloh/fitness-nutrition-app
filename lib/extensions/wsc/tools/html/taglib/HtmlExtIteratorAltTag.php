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
 * @file HtmlExtIteratorAlt.php
 * @namespace WSC\tools\html\taglib
 *
 * If the iterators' data-container is empty, this taglib
 * provides the possibility of automagically displaying an alternate like
 * "no data avaiable" or something like that.
 *
 * @author Florian Horn
 * @version
 * Version 1.0, 28.07.2010<br/>
 * Version 2.0, 15.11.2013<br/>
 *
 */

namespace WSC\tools\html\taglib;

use APF\core\pagecontroller\Document;

class HtmlExtIteratorAltTag extends Document
{
    /**
     * Tranform content
     * @return string
     */
    public function transform()
    {
        $oParent = $this->getParentObject();        
        $aData = $oParent->getDataContainer();

        if (is_array($aData) && count($aData) > 0) {
            $this->__Content = '';
        }

        return $this->__Content;
    }
}
