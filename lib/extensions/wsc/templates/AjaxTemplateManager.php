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
 * @file AjaxTemplateManager.php
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
use WSC\templates\AbstractTemplateManager;
use WSC\templates\ajax\AjaxHandler;
use WSC\tools\link\RouterHandler;

class AjaxTemplateManager extends AbstractTemplateManager
{
    /**
     * StandardHandler Template
     * @return int
     */
    public function execute()
    {
        // --- Call-Logger
        $this->__oLogger->logEntry(
                    'template',
                    sprintf('[ajax] %s', RouterHandler::GetUrl())
                );

        // --- AJAX-Handler laden
        $aHd = Singleton::getInstance('WSC\templates\ajax\AjaxHandler');
        $aHd->setLogger($this->__oLogger);
        $aHd->execute();
        if ($aHd->hasError() === true) {
            $this->__oLogger->logEntry(
                    'template',
                    sprintf('[ajax] %s', $aHd->getError()),
                    LogEntry::SEVERITY_ERROR
                );
            return -1;
        }

        $this->__sOutput = $aHd->getContent();
    }
}
