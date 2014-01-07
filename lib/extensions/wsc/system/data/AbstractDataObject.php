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
 * @file AbstractData.php
 * @namespace WSC\system\data
 *
 *
 *
 * @author Florian Horn
 * @version
 * Version 1.0, 16.10.2010<br/>
 * Version 1.1, 09.02.2011<br/>
 * Version 1.2, 15.04.2011<br/>
 * Version 2.0, 13.11.2013<br/>
 *
 */

namespace WSC\system\data;

use APF\core\pagecontroller\APFObject;
use APF\core\registry\Registry;
use WSC\core\language\LanguageManager;

abstract class AbstractDataObject extends APFObject
{
    /**
     * Errors
     * @var array
     */
    protected $__aErrors    = array();
    
    
    
    /**
     * Data
     * @var array
     */
    protected $__aData      = array();



    /**
     * Creates an instance inherited from AbstractData
     */
    public function __construct()
    {
        $this->__Context    = Registry::retrieve('APF\core', 'App');
        $this->__Language   = LanguageManager::getShortLocale();
    }
    
    
    
    /**
     * Cloning object
     */
    protected function __clone() 
    {}



    /**
     * Get data object value
     * @param string $sKey
     * @return unknown
     */
    public function __get($sKey)
    {
        if (!array_key_exists($sKey, $this->__aData)) {
            throw new \InvalidArgumentException( '['.get_class().'::__get()] Invalid argument!');
        }
        return $this->__aData[$sKey];
    }



    /**
     * Set an attribute with its value
     * @param string $sKey
     * @param mixed $Value
     */
    public function __set($sKey, $Value)
    {
        if (!array_key_exists($sKey, $this->__aData)) {
            throw new \InvalidArgumentException( '['.get_class().'::__get()] Invalid argument!');
        }
        $this->__aData[$sKey] = $Value;
    }



    /**
     * Saves the object into the database<br/>
     * if the record (id) already exists, the data will be overwritten.<br/>
     * if the record does not exist, the behavior is the same as saveCopy()
     * @return void
     */
    abstract public function insert();



    /**
     * Saves the object into the database<br/>
     * if the id exists, the data will not be overwritten.<br/>
     * a new record will be created.
     * @return void
     */
    abstract public function update();



    /**
     * Deletes the object
     * @return void
     */
    abstract public function delete();

}
