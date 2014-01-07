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
 * @file SystemMessage.php
 * @namespace WSC\core\systemmessage;
 *
 *
 *
 * @author Florian Horn
 * @version
 * Version 1.0, 26.02.2011<br/>
 * Version 2.0, 13.11.2013<br/>
 * 
 */

namespace WSC\core\systemmessage;

use WSC\tools\datetime\wsDate;
use WSC\core\factory\AbstractStatic;

class SystemMessage extends AbstractStatic
{
    private static $__aSystemMessages   = array();
    private static $__aErrorMessages    = array();
    
    
    
    /**
     * Fügt eine Systemnachricht hinzu
     * @param string $sMessage
     * @return int
     */
    public static function addSystemMessage($sMessage) 
    { 
        return self::__addMessage($sMessage); 
    }
    
    
    
    /**
     * Fügt mehrere Nachrichten hinzu
     * @param array $aMessages
     */
    public static function addSystemMessages(array $aMessages)
    {
        foreach ($aMessages as $m) {
            self::__addMessage($m);
        }
    }
    
    
    
    /**
     * Fügt eine Fehlernachricht hinzu
     * @param string $sMessage
     * @return int
     */
    public static function addErrorMessage($sMessage) 
    { 
        return self::__addMessage($sMessage, true); 
    }
    
    
    
    /**
     * Fügt mehrere Fehlernachrichten hinzu
     * @param array $aMessages
     */
    public static function addErrorMessages(array $aMessages)
    {
        foreach ($aMessages as $m) {
            self::__addMessage($m, true);
        }
    }

    
    
    /**
     * Get all error messages
     * @return array
     */
    public static function getErrorMessages() 
    { 
        return self::$__aErrorMessages; 
    }

    
    
    /**
     * Get all system messages
     * @return array
     */
    public static function getSystemMessages() 
    { 
        return self::$__aSystemMessages; 
    }

    
    
    /**
     * Check if error messages exists
     * @return boolean
     */
    public static function hasErrorMessages()
    {
        return \count(self::$__aErrorMessages) > 0 ? true : false;
    }

    
    
    /**
     * Check if system messages exists
     * @return boolean
     */
    public static function hasSystemMessages()
    { 
        return \count(self::$__aSystemMessages) > 0 ? true : false;
    }

    
    
    /**
     * Fügt intern eine Nachricht hinzu
     * @param string $sMessage
     * @param bool $bError
     * @return int
     */
    private static function __addMessage($sMessage, $bError = false)
    {
        if (\strlen(trim($sMessage)) === 0) {
            return -1;
        }

        $d = new wsDate();
        $aData = array( 
                'Timestamp' => $d->toTimestamp(),
                'Text' => sprintf('%s.', $sMessage) 
            );
        
        if ($bError === true) {
            self::$__aErrorMessages[] = $aData;
        } else {
            self::$__aSystemMessages[] = $aData;
        }
        
        return 0;
    }
    
}
