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
 * @file LiveErrorHandler.php
 * @namespace WSC\core\errorhandler
 *
 *
 *
 * @author Florian Horn
 * @version
 * Version 1.0, 26.02.2011<br/>
 * Version 2.0, 08.11.2013<br/>
 *
 */

namespace WSC\core\errorhandler;

use APF\core\errorhandler\DefaultErrorHandler;
use APF\core\logging\LogEntry;

class LiveErrorHandler extends DefaultErrorHandler 
{
    /**
     * Process error
     * @param Exception $exception
     */
    public function handleError($errorNumber,$errorMessage,$errorFile,$errorLine)
    {
        // --- fill attributes
        $this->errorNumber     = $errorNumber;
        $this->errorMessage    = $errorMessage;
        $this->errorFile       = $errorFile;
        $this->errorLine       = $errorLine;

        // --- log error
        $this->logError();
    }
    
    /**
     * Write in log file
     */
    protected function logError()
    {
        $log = Registry::retrieve('APF\core','Logger');
        $log->logEntry(
                    'errors',
                    '['.($this->generateErrorID()).'] '.$this->errorMessage.
                    ' (Number: '.$this->errorNumber.', File: '.$this->errorFile.
                    ', Line: '.$this->errorLine.')',
                    LogEntry::SEVERITY_ERROR
                );
    }
}
