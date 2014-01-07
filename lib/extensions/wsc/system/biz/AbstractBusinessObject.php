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
 * @file AbstractBiz.php
 * @namespace WSC\system\biz
 *
 *
 *
 * @author Florian Horn
 * @version
 * Version 1.0, 31.08.2010<br/>
 * Version 1.1, 25.10.2010<br/>
 * Version 1.2, 26.02.2011<br/>
 * Version 1.3, 15.04.2011<br/>
 * Version 2.0, 13.11.2013<br/>
 *
 */

namespace WSC\system\biz;

use APF\core\pagecontroller\APFObject;
use APF\core\registry\Registry;
use WSC\tools\datetime\wsDate;
use WSC\core\language\LanguageManager;

abstract class AbstractBusinessObject extends APFObject
{
    const VALID_DATE_MODE_ON    = true;
    const VALID_DATE_MODE_OFF   = false;
    const FLOAT_APPENDIX_SIZE   = 2;
    
    
    
    /**
     * Errors
     * @var array
     */
    protected $__aErrors        = array();
    
    
    
    /**
     * Validation date
     * @var wsDate 
     */
    protected $__dValid         = null;
    
    
    
    /**
     * Flag if validation date is used
     * @var boolean
     */
    protected $__bUseValid      = self::VALID_DATE_MODE_OFF;



    /**
     * Creates an instance inherited from AbstractBiz
     */
    public function __construct()
    {
        $this->__Context    = Registry::retrieve('APF\core', 'App');
        $this->__Language   = LanguageManager::getShortLocale();
        $this->__dValid     = new wsDate();
    }
    
    
    
    /**
     * Cloning object
     */
    protected function __clone() 
    {}



    /**
     * Returns if the objects attributes are valid
     * @return boolean
     */
    public function isValid()
    {
        if ($this->__hasError()) {
            return false;
        }

        return true;
    }



    /**
     * Saves the object into the database<br/>
     * if the record (id) already exists, the data will be overwritten.<br/>
     * if the record does not exist, the behavior is the same as saveCopy()
     * @return void
     */
    public function update()
    {
        // do nothing
    }



    /**
     * Saves the object into the database<br/>
     * if the id exists, the data will not be overwritten.<br/>
     * a new record will be created.
     * @return void
     */
    public function insert()
    {
        // do nothing
    }



    /**
     * Deletes the object
     * @return void
     */
    public function delete()
    {
        // do nothing
    }
    
    
    
    /**
     * Set validation date to check asynchronous changes
     * @param wsDate $Date 
     */
    public function setValidationDate(wsDate $Date) 
    { 
        $this->__dValid = $Date; 
    }
    
    
    
    /**
     * Set if the validation date should be checked
     * @param boolean $bUsage 
     */
    public function setValidationDateMode($bUsage = self::VALID_DATE_MODE_ON)
    {
        switch ($bUsage) {
            case self::VALID_DATE_MODE_ON:
                $this->__bUseValid = self::VALID_DATE_MODE_ON;
                break;
            case self::VALID_DATE_MODE_OFF:
                $this->__bUseValid = self::VALID_DATE_MODE_OFF;
                break;
            default:
                throw new \InvalidArgumentException('['.\get_class().'::setValidationDateMode()] '.
                        'Invalid argument!');
        }
    }



    /**
     * Check if errors has been occured
     * @return bool
     */
    public function hasError() 
    { 
        return !$this->isValid(); 
    }

    
    
    /**
     * Returns an array of occured errors
     * @return array
     */
    public function getErrors() 
    { 
        return $this->__aErrors; 
    }

    
    
    /**
     * Check if errors occured
     * @return bool
     */
    protected function __hasError() 
    { 
        return \count( $this->__aErrors ) > 0; 
    }



    /**
     * Returns a normed float value as string
     * @param string $sValue
     * @return string
     */
    protected function __separateStringToFloat($sValue)
    {
        $aSeparation = \explode('.', $sValue);
        if (\count($aSeparation) > 2 ) {
            throw new \InvalidArgumentException('['.get_class().'::__separateStringToFloat()] '.
                    'Invalid argument');
        }

        // --- Build up float appendix
        $sApp = \array_key_exists(1,$aSeparation) ? $aSeparation[1] : '';
        while( \strlen($sApp) < self::FLOAT_APPENDIX_SIZE ) {
            $sApp .= '0';
        }
        
        return $aSeparation[0].'.'.$sApp;
    }
    
    
    
    /**
     * Cast a string to a float
     * @param float $fValue
     * @return float 
     */
    protected function __castFloatToString($fValue)
    {
        return (\floor($fValue * 100))/100;
    }
}
