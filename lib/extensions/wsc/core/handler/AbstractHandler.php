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
 * @file AbstractHandler.php
 * @namespace WSC\core\handler
 *
 * 
 *
 * @author Florian Horn
 * @version
 * Version 1.1, 26.10.2010<br/>
 * Version 1.2, 26.02.2011<br/>
 * Version 2.0, 08.11.2013<br/>
 */

namespace WSC\core\handler;

use APF\core\pagecontroller\APFObject;
use APF\core\logging\Logger;
use APF\core\logging\LogEntry;

abstract class AbstractHandler extends APFObject
{
    const DEBUG_MODE_ON     = true;
    const DEBUG_MODE_OFF    = false;

    protected $__sOutput    = '';
    protected $__bDebug     = self::DEBUG_MODE_OFF;
    protected $__bError     = false;
    protected $__sError     = '';
    protected $__oLogger    = null;



    private function __clone(){}


    /**
     * Creates an instance inherited from AbstractHandler
     * @return void
     */
    public function __construct()
    {
        $this->setContext(Registry::retrieve('APF\core', 'App'));
        $this->setLanguage(LanguageManager::getShortLocale());
    }



    /**
     * Set the logger instance
     * @param Logger $oLogger
     * @return void
     */
    public function setLogger(Logger $oLogger)
    {
        $this->__oLogger = $oLogger;
    }



    /**
     * Main processing
     * @return void
     */
    abstract public function execute();



    /**
     * (De-)activates the debuge mode
     * @param bool $bDebugMode
     */
    public function setDebugMode($bDebugMode = self::DEBUG_MODE_ON)
    {
        switch ($bDebugMode) {
            case self::DEBUG_MODE_ON:
                $this->__bDebug = self::DEBUG_MODE_ON;
                break;
            case self::DEBUG_MODE_OFF:
                $this->__bDebug = self::DEBUG_MODE_OFF;
                break;
            default:
                throw new \InvalidArgumentException(
                        '['.get_class().'::setDebugMode()] '.
                        'Invalid argument!');
        }
    }



    /**
     * Returns the generated output
     * @return string
     */
    public function getContent() 
    { 
        return $this->__sOutput; 
    }

    
    
    /**
     * Prüft, ob ein Fehler aufgetreten ist.
     * @return bool
     */
    public function hasError() 
    { 
        return $this->__bError; 
    }

    
    
    
    /**
     * Falls ein Fehler auftrat, gibt die Methode die Fehlermeldung zurück.
     * @return string
     */
    public function getError() 
    { 
        return $this->__sError; 
    }



    /**
     * Logs a message
     * @param string $sMessage
     * @param string $sType
     * @return void
     */
    protected function __log($sMessage , $sType = LogEntry::SEVERITY_INFO)
    {
        if ($this->__oLogger === null) { 
            return;
        }
        $this->__oLogger->logEntry( 
                    'handler',
                    $sMessage,
                    $sType
                );
    }
}
