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
 * @file NoSQLHandler.php
 *
 *
 *
 * @author Florian Horn
 * @version
 * Version 1.0, 26.02.2011<br/>
 *
 */

class NoSQLHandler extends AbstractNoSQL
{
    private function __clone() {}
    public function __construct()
    {
        parent::$__DB = new NoSQLDB();
    }

    /**
     *
     * @todo: Default-DB waehlen
     * @todo: Default-Collection waehlen (?)
     * @return <boolean>
     */
    public function authenticate() 
    {
        try
        {
            /*
             * @todo: Auslesen der Configuration
             *  - User
             *  - PW
             *  - Host
             *  - Port
             *  - Default-DB
             */
            $user   = 'public';
            $pw     = 'local';
            $host   = '127.0.0.1';
            $port   = '27017';
            $db     = 'wsc_dev';
            
            parent::$__Handler = new Mongo( 'mongodb://'.$user.':'.$pw.'@'.$host.':'.$port.'/'.$db );
            if( !(parent::$__Handler instanceof Mongo) )
            {
                throw new DatabaseHandlerException( '[NoSQLHandler::authenticate()] Database connection could not be established!' );
                return false;
            }
            parent::$__DB->set( parent::$__Handler->$db );
        }
        catch( MongoConnectionException $e )
        {
            throw new DatabaseHandlerException( '[NoSQLHandler::authenticate()] Database connection could not be established because of "'.$e->getMessage().'"!' );
            return false;
        }
        return true;
    }

    /**
     *
     * @param <type> $db
     * @return <MongoDB>
     */
    public function db( $db = null )
    {
        if( !( $db === null ) )
        {
            parent::$__DB->set( parent::$__Handler->$db );
        }
        return parent::$__DB;
    }
}

?>
