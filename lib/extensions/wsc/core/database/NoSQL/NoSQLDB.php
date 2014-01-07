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
 * @file NoSQLDB.php
 *
 *
 *
 * @author Florian Horn
 * @version
 * Version 1.0, 26.02.2011<br/>
 *
 */

class NoSQLDB extends AbstractNoSQL
{
    private function __clone() {}
    public function __construct()
    {
        parent::$__Collection = new NoSQLCollection();
    }

    /**
     *
     */
    public function set( MongoDB $db ) {  parent::$__DB = $db; }

    public function create() {}
    public function drop() {}

    /**
     *
     * @param <type> $collection
     * @return <type>
     */
    public function select( $collection = null )
    {
        if( !( $collection === null ) )
        {
            parent::$__Collection->set( parent::$__DB->$collection );
        }
        return parent::$__Collection;
    }

    /**
     * Returns the count of collections
     * @return <int>
     */
    public function count()
    {
        if( !(parent::$__DB instanceof MongoDB ) ) return false;
        return count( parent::$__DB->listCollections() );
    }
}


?>
