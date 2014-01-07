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
 * @file HtmlErrorTag.php
 * @namespace WSC\tools\html\taglib
 *
 *
 *
 * @author Florian Horn
 * @version
 * Version 1.0, 23.08.2010<br/>
 *
 */

namespace WSC\tools\html\taglib;

use APF\core\pagecontroller\Document;
use APF\extensions\htmllist\taglib\html_taglib_list;
use WSC\core\systemmessage\SystemMessage;

class HtmlErrorTag extends Document
{
    /**
     * Tranform content
     * @return string
     */
    public function transform()
    {
        $a = SystemMessage::getErrorMessages();
        if (\count($a) !== 0) {
            /*
             * Erzeuge eine Liste mit Fehlern
             */
            $list = new html_taglib_list();
            $list->addList('list:unordered', array( 'id' => 'errorlist' ));
            $eList = $list->getListById('errorlist');

            foreach ($a as $e) {
                $eList->addElement($e['Text']);
            }

            $this->__Content = '<div class="error">
                    <p>Bei der Verarbeitung sind Fehler aufgetreten:</p>
                       '.$list->transform().'</div>';
        }

        return $this->__Content;
    }
}
