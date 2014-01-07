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
 * @file CronjobTemplateManager.php
 * @namespace WSC\templates
 *
 *
 *
 * @author Florian Horn
 * @version
 * Version 1.0, 04.03.2010<br/>
 * Version 1.1, 26.02.2011<br/>
 * Version 2.0, 08.11.2013<br/>
 *
 */

namespace WSC\templates;

use APF\core\singleton\Singleton;
use APF\core\logging\LogEntry;
use APF\tools\request\RequestHandler;
use WSC\templates\AbstractTemplateManager;
use WSC\templates\cronjob\CronjobHandler;
use WSC\tools\link\RouterHandler;

class CronjobTemplateManager extends AbstractTemplateManager
{
    /**
     * StandardHandler Template
     * @return int
     */
    public function execute()
    {
        $CnfHd = $this->getConfiguration( 'APF\apps' , 'config.ini');
        
        // --- Call-Logger
        $this->__oLogger->logEntry(
                    'template',
                    sprintf('[cronjob] %s', RouterHandler::GetUrl())
                );
        
        $sPrivateKey = (string)$CnfHd->getSection('Client')->getValue('PrivateKey');
        
        // --- Um unbekannten Zugriff auf Cronjob zu schützen, muss der privateKey mit angegeben werden
        if (RequestHandler::getValue('privatekey',null) !== $sPrivateKey) {
            $this->__oLogger->logEntry(
                    'template',
                    '[cronjob] No valid private key!',
                    LogEntry::SEVERITY_ERROR
                );
            return -1;
        }

        // --- Cronjob-Handler laden
        $cjHd = Singleton::getInstance('WSC\templates\cronjob\CronjobHandler');
        $cjHd->setLogger($this->__oLogger);
        $cjHd->execute();
        if ($cjHd->hasError() === true) {
            $this->__oLogger->logEntry(
                    'template',
                    sprintf('[cronjob] %s', $cjHd->getError()),
                    LogEntry::SEVERITY_ERROR
                );
            return -1;
        }    

        $this->__sOutput = $cjHd->getContent();
    }
}
