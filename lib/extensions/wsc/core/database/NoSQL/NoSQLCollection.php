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
 * @file NoSQLCollection.php
 *
 *
 *
 * @author Florian Horn
 * @version
 * Version 1.0, 26.02.2011<br/>
 *
 */

class NoSQLCollection extends AbstractNoSQL
{
    private function __clone() {}
    public function __construct() {}

    /**
     *
     */
    public function set( MongoCollection $collection ) {  parent::$__Collection = $collection; }

    public function insert( array $a , $bSafe = false )
    {
        if( !$this->__validateCollection() ) return false;
        if( !parent::$__Collection->insert( $a , ($bSafe===true)?true:false ) )
        {
            return false;
        }
        return true;
    }
    public function update( array $criteria , array $newobj , array $options = array() )
    {
        if( !$this->__validateCollection() ) return false;
        if( !parent::$__Collection->update( $criteria , $newobj , $options ) )
        {
            return false;
        }
        return true;
    }
    public function remove( array $criteria , array $options = array() )
    {
        if( !$this->__validateCollection() ) return false;
        if( !parent::$__Collection->remove( $criteria , $options ) )
        {
            return false;
        }
        return true;
    }

    public function setIndex( array $keys , array $options )
    {
        if( !$this->__validateCollection() ) return false;
        if( !parent::$__Collection->ensureIndex( $keys , $options ) )
        {
            return false;
        }
        return true;
    }
    public function deleteIndex( $key )
    {
        if( !$this->__validateCollection() ) return false;
        if( !parent::$__Collection->deleteIndex( $key ) )
        {
            return false;
        }
        return true;
    }
    public function deleteIndexes( array $keys )
    {
        if( !$this->__validateCollection() ) return false;
        if( !parent::$__Collection->deleteIndex( $keys ) )
        {
            return false;
        }
        return true;
    }
    public function deleteAllIndexex()
    {
        if( !$this->__validateCollection() ) return false;
        if( !parent::$__Collection->deleteIndexex() )
        {
            return false;
        }
        return true;
    }





    /*
     *
     * Algorithmen
     *
     */

    /**
     * Returns the count of records
     * @return <int>
     */
    public function count()
    {
        if( !$this->__validateCollection() ) return false;
        return parent::$__Collection->count();
    }
    /**
     *
     * @param array $query
     * @param array $fields
     * @return <MongoCursor>
     */
    public function find( array $query = array() , array $fields = array() )
    {
        if( !$this->__validateCollection() ) return false;
        return parent::$__Collection->find( $query , $fields );
    }
    /**
     *
     * @param array $query
     * @param array $fields
     * @return <MongoCursor>
     */
    public function findOne( array $query = array() , array $fields = array() )
    {
        if( !$this->__validateCollection() ) return false;
        return parent::$__Collection->findOne( $query , $fields );
    }



    /**
     * Validates if a collection has been selected yet
     * @return <boolean>
     */
    private function __validateCollection()
    {
        if( !(parent::$__Collection instanceof MongoCollection ) )
        {
            return false;
        }
        return true;
    }
}

?>
