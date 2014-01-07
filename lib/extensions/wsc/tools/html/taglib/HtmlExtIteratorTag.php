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
 * @file HtmlExtIteratorTag.php
 * @namespace WSC\tools\html\taglib
 *
 * Extends the iterator with a method to get the datacontainer
 * 
 *
 * @author Florian Horn
 * @version
 * Version 1.0, 28.07.2010<br/>
 * Version 2.0, 15.11.2013<br/>
 *
 */

namespace WSC\tools\html\taglib;

use APF\tools\html\taglib\HtmlIteratorTag;
use WSC\tools\html\taglib\HtmlExtIteratorAltTag;

class HtmlExtIteratorTag extends HtmlIteratorTag
{
    /**
     * Konstruktor
     */
    public function  __construct()
    {
        $this->__TagLibs[] = new TagLib('WSC\tools\html\taglib\HtmlExtIteratorAltTag','iterator','alt');
    }

    /**
     * Ermoeglicht den Zugriff auf den DataContainer von außen.
     * @return array DataContainer
     */
    public function getDataContainer()
    {
        return $this->dataContainer;
    }
}
