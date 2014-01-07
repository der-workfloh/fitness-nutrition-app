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
 * @file DatabaseFactory.php
 * @namespace WSC\core\database
 *
 *
 *
 * @author Florian Horn
 * @version
 * Version 1.0, 04.03.2010<br/>
 * Version 1.1, 10.03.2011<br/>
 * Version 1.2, 16.05.2011<br/>
 * Version 1.3, 15.09.2011<br/>
 * Version 2.0, 07.11.2013<br/>
 *
 */

namespace WSC\core\database;

use APF\modules\genericormapper\data\GenericORMapperFactory;
use APF\core\service\APFService;
use APF\core\database\ConnectionManager;
use WSC\core\database\DatabaseWrapper;
use WSC\core\database\PDOWrapper as wsPDO;
use WSC\core\factory\AbstractStatic;

class DatabaseFactory extends AbstractStatic
{
    const ERRMODE_TRUE                      = true;
    const ERRMODE_FALSE                     = false;


    protected static $__sCurrentDriver      = 'MySQL';
    protected static $__connectionManager   = null;
    protected static $__bDebugMode          = false;
    protected static $__oDBWrapper          = null;


    /**
     * Cleanup database connections
     */
    public static function cleanup()
    {
        wsPDO::cleanup();
    }



    /**
     * Initialization
     */
    public static function initialize()
    {
        self::$__oDBWrapper = new DatabaseWrapper();
    }



    /**
     * Generiert einen ORM
     * @param string $sNamespace
     * @param string $sAppendix
     * @return GenericORMapper
     */
    public static function getOrm($sNamespace = 'Core\System', $sAppendix = 'default')
    {
        $ORMF = self::$__oDBWrapper->getService('APF\Modules\GenericORMapper\Data\GenericORMapperFactory', APFService::SERVICE_TYPE_SINGLETON);
        return $ORMF->getGenericORMapper($sNamespace, $sAppendix, self::$__sCurrentDriver);
    }



    /**
     * Generiert einen ORM mittels DI
     * @return GenericORMapper
     */
    public static function getDIOrm()
    {
        return self::$__oDBWrapper->getDIService('APF\services','GORM');
    }



    /**
     * Erzeugt einen direkten DB-Zugriff
     * @param $sDriver
     * @param $sNamespace
     * @param $sAppendix
     * @return ConnectionManager
     */
    public static function getDB($sDriver = null)
    {
        if ($sDriver !== null) {
            self::__setDriver($sDriver);
        }

        // -- Verbindung speichern
        if (empty(self::$__connectionManager)) {
            self::$__connectionManager = self::$__oDBWrapper->getService('APF\Core\Database\ConnectionManager');
        }
        $cM = self::$__connectionManager;

        return $cM->getConnection(self::$__sCurrentDriver);
    }


    
    /**
     * Erzeugt eine PDO Instanz für den gesetzen Treiber
     * @param string $sDriver
     * @return PDO
     */
    public static function getPDO($sDriver = null)
    {
        if ($sDriver !== null) {
            self::__setDriver($sDriver);
        }
        wsPDO::setLogger(self::$__oLogger);
        wsPDO::setErrorMode((self::$__bDebugMode) ? PDO::ERRMODE_WARNING : PDO::ERRMODE_SILENT);
        return wsPDO::initialize($sDriver);
    }



    /**
     * Gibt den aktuell gewählten Treiber zurück
     */
    public static function getDriver() { 
        return self::$__sCurrentDriver; 
    }

    
    
    /**
     * Setzt den aktuellen Treiber
     * @param string $sDriver
     */
    public static function setDriver($sDriver) { 
        self::__setDriver( $sDriver ); 
    }

    
    
    /**
     * Set the error mode
     * @param bool $Mode
     */
    public static function setErrorMode($Mode = self::ERRMODE_FALSE)
    {
        switch ($Mode) {
            case self::ERRMODE_FALSE:
                self::$__bDebugMode = self::ERRMODE_FALSE;
                break;
            case self::ERRMODE_TRUE:
                self::$__bDebugMode = self::ERRMODE_TRUE;
                break;
            default:
                throw new InvalidArgumentException('['.get_class().'::setErrorMode()] Invalid Argument');
        }
    }
    
    

    /**
     * Set the driver
     * @param string $sDriver
     */
    private static function __setDriver($sDriver)
    {
        if (empty($sDriver)) {
            throw new Exception('['.get_class().'::__setDriver()] Invalid Argument');
        }
        self::$__sCurrentDriver = $sDriver;
    }
}
