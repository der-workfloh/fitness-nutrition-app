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
 * @file NoSQLFactory.php
 *
 *
 *
 * @author Florian Horn
 * @version
 * Version 1.0, 26.02.2011<br/>
 *
 */

import('core::database','DatabaseHandlerException');
import('extensions::wsc::core::database::nosql','NoSQLHandler');
import('extensions::wsc::core::database::nosql','NoSQLDB');
import('extensions::wsc::core::database::nosql','NoSQLCollection');
import('extensions::wsc::core::database::nosql','NoSQLRecord');
class NoSQLFactory
{
    static private $__aAllowedClasses = array(
        0 => 'NoSQLHandler',
        1 => 'NoSQLDB',
        2 => 'NoSQLCollection',
        3 => 'NoSQLRecord'
    );

    private function __clone() {}
    private function __construct() {}

    static public function Factory( $Class )
    {
        if( !in_array( $Class , static::$__aAllowedClasses ) )
        {
            throw new Exception( sprintf( '[NoSQLFactory::Factory()] Class "%s" is not allowed to be loaded!' , $Class ) );
            return false;
        }
        $oClass = new $Class();
        return $oClass;
    }
}



class AbstractNoSQL extends APFObject
{
    static private $__Handler;
    static private $__DB;
    static private $__Collection;
    static private $__Record;
}

?>
