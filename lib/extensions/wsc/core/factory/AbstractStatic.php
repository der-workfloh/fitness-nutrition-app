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
 * @file AbstractStatic.php
 * @namespace WSC\core\factory
 * 
 *
 * @author Florian Horn
 * @version
 * Version 1.0, 31.08.2010<br/>
 * Version 1.1, 17.10.2010<br/>
 * Version 2.0, 03.11.2013<br/>
 *
 */ 

namespace WSC\core\factory;

use APF\core\singleton\Singleton;
use APF\core\configuration\ConfigurationManager;
use APF\core\registry\Registry;
use APF\core\logging\Logger;
use APF\core\service\APFService;

abstract class AbstractStatic
{
    protected static $__bError      = false;
    protected static $__sError      = '';
    protected static $__oLogger     = NULL;
    protected static $__sContext    = '';
    protected static $__sLanguage   = '';


    private function __clone() {}
    private function __construct() {}

    
    
    /**
     * Set the used logger
     * @param Logger $oLogger
     */
    public static function setLogger(Logger $oLogger) 
    {
        self::$__oLogger = $oLogger;
    }

    
    
    /**
     * Returns an instance of wsLogger
     * @return Logger
     */
    public static function getLogger()
    {
        return self::$__oLogger;
    }

    
    
    /**
     * Returns a service object according to the current application context.
     * @param string $namespace
     * @param string $serviceName
     * @param string $type
     * @return APFObject
     */
    protected static function __getServiceObject($namespace , $serviceName , $type = APFService::SERVICE_TYPE_SINGLETON)
    {
        $serviceManager = Singleton::getInstance('APF\core\service\ServiceManager');
        return $serviceManager->getServiceObject(
                    $namespace,
                    $serviceName,
                    self::$__sLanguage,
                    self::$__sContext,
                    $type
                );
    }

    
    
    /**
     * Returns a service object, that is initialized by dependency injection.
     * @param string $namespace
     * @param string $name
     * @return APFObject
     */
    protected static function __getDIServiceObject($namespace , $name)
    {
        $diServiceMgr = Singleton::getInstance('APF\core\service\DIServiceManager');
        return $diServiceMgr->getServiceObject(
                    $namespace,
                    $name,
                    self::$__sLanguage,
                    self::$__sContext
                );
    }

    
    
    /**
     * Returns a initialized service object according to the current application context.
     * @param string $namespace
     * @param string $serviceName
     * @param string|array $initParam
     * @param string $type
     * @return APFObject
     */
    protected static function __getAndInitServiceObject($namespace , $serviceName , $initParam , $type = APFService::SERVICE_TYPE_SINGLETON)
    {
        $serviceManager = Singleton::getInstance('APF\core\service\ServiceManager');
        return $serviceManager->getAndInitServiceObject(
                    $namespace,
                    $serviceName,
                    self::$__sLanguage,
                    self::$__sContext,
                    $initParam,
                    $type
                );
    }

    

    /**
     * Returns a configuration object according to the current application context and the given
     * parameters.
     * @param string $namespace
     * @param string $configName
     * @param boolean $parseSubsections
     * @return Configuration
     */
    protected static function getConfiguration($namespace , $configName)
    {
        return ConfigurationManager::loadConfiguration(
                    $namespace,
                    Registry::retrieve('APF\core','App'),
                    self::$__sLanguage,
                    self::$__sContext,
                    $configName
                );
    }


    
    /**
     * Get context
     * @return string
     */
    public static function getContext() 
    { 
        return self::$__sContext; 
    }

    
    
    /**
     * Get language
     * @return string
     */
    public static function getLanguage() 
    { 
        return self::$__sLanguage;
    }



    /**
     * Set context
     * @param string $sContext
     */
    public static function setContext($sContext) 
    { 
        self::$__sContext = $sContext; 
    }

    
    
    /**
     * Set language
     * @param string $sLanguage
     */
    public static function setLanguage($sLanguage) 
    { 
        self::$__sLanguage = $sLanguage; 
    }


    
    /**
     * Prüft, ob ein Fehler aufgetreten ist.
     * @return bool
     */
    public static function hasError()
    {
        return self::$__bError;
    }

    
    
    /**
     * Falls ein Fehler auftrat, gibt die Methode die Fehlermeldung zurück.
     * @return string
     */
    public static function getError()
    {
        return self::$__sError;
    }
}
