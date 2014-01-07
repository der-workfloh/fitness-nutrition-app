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
 * @file AbstractFactory.php
 * @namespace WSC\core\factory
 * 
 *
 *
 * @author Florian Horn
 * @version
 * Version 1.0, 30.08.2010<br/>
 * Version 1.1, 26.02.2011<br/>
 * Version 2.0, 07.11.2013<br/>
 *
 */

namespace WSC\core\factory;

use APF\core\registry\Registry;
use WSC\core\language\LanguageManager;
use WSC\core\factory\AbstractStatic;

abstract class AbstractFactory extends AbstractStatic
{
    /**
     * Creates a new instance if the object exists
     * @param string $sClass
     * @return APFObject
     */
    public static function factory( $sClass )
    {
        $oClass = new $sClass();
        $oClass->setContext(Registry::retrieve('APF\core', 'App'));
        $oClass->setLanguage(LanguageManager::getShortLocale());

        // --- Erstelle Instanz des Cronjobs
        return $oClass;
    }
}
