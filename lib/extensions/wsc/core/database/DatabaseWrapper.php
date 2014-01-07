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
 * @file DatabaseWrapper.php
 * @namespace WSC\core\database
 *
 *
 *
 * @author Florian Horn
 * @version
 * Version 1.0, 30.08.2010<br/>
 * Version 1.1, 15.09.2011<br/>
 * Version 2.0, 07.11.2013<br/>
 *
 */

namespace WSC\core\database;

use APF\core\configuration\ConfigurationManager;
use APF\core\service\APFService;
use APF\core\service\ServiceManager;
use APF\core\service\DIServiceManager;
use APF\core\registry\Registry;
use WSC\core\language\LanguageManager;

class DatabaseWrapper
{
    /**
     * Konstruktor
     */
    public function __construct()
    {
        $this->__Context    = Registry::retrieve('APF\core', 'App');
        $this->__Language   = LanguageManager::getShortLocale();
    }
    private function __clone() {}



    /**
     * Maskiert den uebergebenen Wert
     * @param string $value
     * @return string
     */
    public function escapeValue($value)
    {
        return mysql_real_escape_string($value);
    }



    /**
     * Get configuration
     * @param string $sNamespace
     * @param string $sName
     * @return Configuration
     */
    public function getConfiguration($sNamespace, $sName)
    {
        return ConfigurationManager::loadConfiguration(
                    $sNamespace,
                    $this->__Context,
                    $this->__Language,
                    Registry::retrieve('APF\core','Environment'),
                    $sName
                );
    }


    /**
     * Get service object
     * @param string $sClass
     * @param string $sType
     * @param string $sInstanceId
     * @return APFObject
     */
    public function getService($sClass, $sType = APFService::SERVICE_TYPE_NORMAL, $sInstanceId = null)
    {
        return ServiceManager::getServiceObject(
                    $sClass, 
                    $this->__Context,
                    $this->__Language, 
                    $sType, 
                    $sInstanceId
                );
    }
    
    
    
    
    /**
     * Get service object via DI
     * @param string $sNamespace
     * @param string $sSection
     * @return APFObject
     */
    public function getDIService($sNamespace, $sSection)
    {
        return DIServiceManager::getServiceObject(
                    $sNamespace, 
                    $sSection,
                    $this->__Context,
                    $this->__Language
                );
    }
}
