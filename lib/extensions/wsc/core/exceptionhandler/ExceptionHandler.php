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

/*
 * @file wsExceptionHandler.php
 * @namespace WSC\core\exceptionhandler
 *
 *
 *
 * @author Florian Horn
 * @version
 * Version 1.0, 25.10.2010<br/>
 *
 */

namespace WSC\core\exceptionhandler;

use APF\core\exceptionhandler\DefaultExceptionHandler;
use APF\core\logging\LogEntry;

class ExceptionHandler extends DefaultExceptionHandler
{
    protected $__bShow = true;


    /**
     * Process error
     * @param Exception $exception
     */
    public function handleException($exception)
    {
        // --- fill attributes
        $this->exceptionNumber    = $exception->getCode();
        $this->exceptionMessage   = $exception->getMessage();
        $this->exceptionFile      = $exception->getFile();
        $this->exceptionLine      = $exception->getLine();
        $this->exceptionTrace     = $exception->getTrace();
        $this->exceptionType      = get_class($exception);

        $message = '['.($this->generateExceptionID()).'] '.$this->exceptionMessage.
                ' (Number: '.$this->exceptionNumber.', File: '.$this->exceptionFile.
                ', Line: '.$this->exceptionLine.')';

        // --- log error
        $this->logException( $message );

        if( $this->__bShow )
        {
            print( $message );
        }
    }



    /**
     * Set if exception should be print out
     * @param bool $bShow
     */
    public function isPrintingExceptions( $bShow = true )
    {
        $this->__bShow = $bShow ? true : false;
    }



    /**
     * Write in log file
     * @param string $sMessage
     */
    protected function logException( $sMessage )
    {
        $log = Registry::retrieve('APF\core','Logger');
        $log->logEntry(
                    'exceptions',
                    $sMessage,
                    LogEntry::SEVERITY_WARNING
                );
    }
}
