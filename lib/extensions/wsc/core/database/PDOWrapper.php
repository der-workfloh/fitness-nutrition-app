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
 * @file PDOWrapper.php
 * @namespace WSC\core\database
 * 
 *
 *
 * @author Florian Horn
 * @version
 * Version 1.0, 09.08.2010<br/>
 * Version 1.1, 16.02.2011<br/>
 * Version 1.2, 10.03.2011<br/>
 * Version 1.3, 16.05.2011<br/>
 * Version 2.0, 07.11.2013<br/>
 *
 */

namespace WSC\core\database;

use WSC\core\factory\AbstractStatic;

class PDOWrapper extends AbstractStatic
{
    const ERROR_NO_CONNECTION           = 300;
    const ERROR_WRONG_DRIVER            = 301;
    const ERROR_CONNECTION_FAILED       = 302;
    const ERROR_WRONG_CONFIG_SECTION    = 303;
    const DATABASE_DRIVER_SQLITE        = 'sqlite';
    const DATABASE_DRIVER_SQLITE2       = 'sqlite2';
    const DATABASE_DRIVER_OCI           = 'oci';
    const DATABASE_DRIVER_ODBC          = 'odbc';
    const DATABASE_DRIVER_DBLIB         = 'dblib';
    const DATABASE_DRIVER_IBMDB2        = 'ibmdb2';
    const DATABASE_DRIVER_MYSQL         = 'mysql';
    const DATABASE_DRIVER_MYSQL_X       = 'mysqlx';
    

    protected static $__aPDOInstance    = array();
    protected static $__ERROR_MODE      = PDO::ERRMODE_SILENT;
    protected static $__oCnf            = NULL;
    
    
    
    /**
     * Initialize PDO Wrapper
     * @param string $sDriver
     * @return PDO|int
     */
    public static function initialize($sDriver = null)
    {
        if (self::$__oCnf === null) {
            self::$__oCnf = self::getConfiguration('APF\core\database', 'connections.ini');
        }

        if ($sDriver === null) {
            return self::ERROR_WRONG_DRIVER;
        }

        if (!wsPDO::hasInstance($sDriver)) {
            if (self::$__oCnf->getSection($sDriver) === null) {
                return self::ERROR_WRONG_CONFIG_SECTION;
            }            

            // --- Wenn es noch keine Verbindung gibt, wird eine Instanz erstellt
            wsPDO::setInstance( 
                        self::$__oCnf->getSection( $sDriver )->getValue('DB.User'),
                        self::$__oCnf->getSection( $sDriver )->getValue('DB.Pass'),
                        self::$__oCnf->getSection( $sDriver )->getValue('DB.Name'),
                        self::$__oCnf->getSection( $sDriver )->getValue('DB.Host'),
                        self::$__oCnf->getSection( $sDriver )->getValue('DB.Type'),
                        $sDriver 
                    );
        }
        $oPDO = wsPDO::getInstance($sDriver);
        return $oPDO;
    }


    
    /**
     * Closes the builded up connections
     */
    public static function cleanup()
    {
        $n = count(self::$__aPDOInstance);
        for($i = 0; $i < $n; ++$i) {
            self::$__aPDOInstance[$i] = null;
        }
    }



    /**
     * Set the current connection
     * @param string $sUser
     * @param string $sPassword
     * @param string $sDBName
     * @param string $sDBHost
     * @param string $sDriver
     * @return int
     */
    public static function setInstance($sUser, $sPassword, $sDBName, $sDBHost = "127.0.0.1", $sDriver = self::DATABASE_DRIVER_MYSQL, $sKey = "default")
    {
        /*
         * Database Connection
         */
        $d = strtolower($sDriver);
        $sConnectionQuery = '';
        // --- SQLite
        switch ($d) {
            case self::DATABASE_DRIVER_SQLITE:
                $sConnectionQuery = $sDriver.":".$sDBName; 
                break;
            case self::DATABASE_DRIVER_SQLITE2:
                $sConnectionQuery = $sDriver.":".$sDBName; 
                break;
            case self::DATABASE_DRIVER_OCI:
                $sConnectionQuery = $sDriver.":dbname=$sDBName;charset=UTF-8"; 
                break;
            case self::DATABASE_DRIVER_ODBC:
                $sConnectionQuery = $sDriver.":Driver={Microsoft Access Driver (*.mdb)};Dbq=$sDBName;Uid=$sUser"; 
                break;
            case self::DATABASE_DRIVER_DBLIB:
                $sConnectionQuery = $sDriver.":host=$sDBHost;dbname=$sDBName";
                break;
            case self::DATABASE_DRIVER_IBMDB2:
                $sConnectionQuery = $sDriver."DRIVER={IBM DB2 ODBC DRIVER};DATABASE=$sDBName; HOSTNAME=$sDBHost; PROTOCOL=TCPIP";
                break;
            case self::DATABASE_DRIVER_MYSQL:
            case self::DATABASE_DRIVER_MYSQL_X:
                $sConnectionQuery = "mysql:dbname=$sDBName;host=$sDBHost";
                break;
            default:
                self::$__oLogger->logEntry('Wrong database driver selected' , 'db_pdo');
                return self::ERROR_WRONG_DRIVER;
        }


        try {
            $aConfig = array(PDO::ATTR_PERSISTENT => true);
            $DB = new PDO(
                        $sConnectionQuery,
                        $sUser,
                        $sPassword,
                        $aConfig 
                    );
        } catch (PDOException $e) {
            self::$__oLogger->logEntry( 
                        sprintf('Connection failed: %s', $e->getMessage())."\n".
                        sprintf('Connection String: %s , %s , %s',
                                $sConnectionQuery , $sUser , $sPassword )."\n\n" ,
                        'db_pdo'
                    );
            return self::ERROR_CONNECTION_FAILED;
        }


        $DB->setAttribute(PDO::ATTR_ERRMODE, self::$__ERROR_MODE);
        $DB->exec("SET CHARACTER SET utf8");

        self::$__aPDOInstance[$sKey] = $DB;

        return $DB;
    }



    /**
     * Get the current instance of pdo
     * @return PDO|int
     */
    public static function getInstance($sKey = "default")
    {
        if (!array_key_exists($sKey , self::$__aPDOInstance)) {
            self::$__oLogger->logEntry(
                        sprintf('Instance key %s not found',
                                $sKey ),
                        'db_pdo'
                    );
            return self::ERROR_NO_CONNECTION;
        }
        return self::$__aPDOInstance[$sKey];
    }
    
    
    
    /**
     * Get all instances as an array
     * @return array
     */
    public static function getInstancesAsArray() { 
        return self::$__aPDOInstance; 
    }



    /**
     * Checks if instance is available
     * @param string $sKey
     * @return bool
     */
    public static function hasInstance($sKey = "default")
    {
        return array_key_exists($sKey, self::$__aPDOInstance) ? true : false;
    }
    
    

    /**
     * Set the error mode
     * @param PDO::ERRMODE_* $Mode
     */
    public static function setErrorMode($Mode = PDO::ERRMODE_SILENT)
    {
        switch ($Mode) {
            case PDO::ERRMODE_SILENT:   
                self::$__ERROR_MODE = PDO::ERRMODE_SILENT;
                break;
            case PDO::ERRMODE_WARNING:  
                self::$__ERROR_MODE = PDO::ERRMODE_WARNING;
                break;
            default:
                throw new InvalidArgumentException('['.get_class().'::setErrorMode()] Invalid Argument');
        }
    }
}
