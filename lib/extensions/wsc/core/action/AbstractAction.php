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
 * @file AbstractAction.php
 * @namespace WSC\core\action
 *
 *
 *
 * @author Florian Horn
 * @version
 * Version 1.0, 26.02.2011<br/>
 * Version 1.1, 22.05.2011<br/>
 * Version 2.0, 08.11.2013<br/>
 *
 */

namespace WSC\core\action;

use APF\tools\link\LinkGenerator;
use APF\tools\link\Url;
use APF\core\logging\Logger;
use APF\core\logging\LogEntry;
use APF\core\frontcontroller\AbstractFrontcontrollerAction;
use APF\tools\request\RequestHandler;
use WSC\tools\link\RouterHandler;

abstract class AbstractAction extends AbstractFrontcontrollerAction
{
    protected $__oLogger    = null;
    protected $__bError     = false;
    
    
    
    /**
     * Konstruktor
     */
    public function __construct()
    {
        $this->__oLogger    =  Registry::retrieve('APF\core','Logger');
        $this->__KeepInURL  = true;
    }
    private function __clone(){}

    
    
    /**
     * Setzt einen Logeintrag
     * @param string $sMessage
     * @param string $sType
     * @return number
     */
    protected function __log($sMessage , $sType = LogEntry::SEVERITY_INFO)
    {
        if ($this->__oLogger !== null) {
            $this->__oLogger->logEntry(
                        'action', 
                        $sMessage, 
                        $sType
                    );
        }
        return 0;
    }

    
    /**
     * Leitet die Seite um und löst so den HTTPHeader auf.
     * @return void
     */
    protected function __forwarding($sPage = '', array $aParams = array())
    {
        /*
         * WORKAROUND
         * Bei AJAX-Request als Antwort $sPage zurueckgeben
         * Probleme bei "normaler" Weiterleitung via 303 (Headerfehler wird angegeben)
         * deswegen mit exit() eine 200 OK Ausgabe erzwingen
         */
        if (RequestHandler::getValue('engine', false) === 'ajax') {
            //header('HTTP/1.1 200 OK');
            header('Cache-Control: no-store, no-cache, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
            exit($sPage);
        }
        
        if (Registry::retrieve('APF\core','URLRewriting') === true) {
            $sLink = RouterHandler::getDomain().'/'.$sPage;
            foreach ($aParams as $k => $v) {
                $sLink .= '/'.$k.'/'.$v;
            }
        } else {
            $sLink = LinkGenerator::generateUrl(
                        Url::fromString(RouterHandler::getBaseUrl())
                        ->setQuery(($sPage !== '' ? array_merge(array('page' => $sPage), $aParams) : $aParams))
                    );
        }
        
        $this->__sendHeader(html_entity_decode($sLink));
        exit();
    }


    /**
     * Send the http header
     * @param string $sLink
     * @param string $sHeaderCode
     */
    protected function __sendHeader($sLink, $sHeaderCode = '303 See Other')
    {
        //HeaderManager::send($sLink,true,'303');
        header('HTTP/1.1 '.$sHeaderCode);
        header('Location: '.$sLink);
        header('Connection: close');
    }
}
