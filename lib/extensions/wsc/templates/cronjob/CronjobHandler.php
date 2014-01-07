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
 * @file CronjobHandler.php
 * @namespace WSC\templates\cronjob
 *
 * 
 * 
 * @author Florian Horn
 * @version
 * Version 1.0, 25.03.2010<br/>
 * Version 1.1, 26.10.2010<br/>
 * Version 1.2, 29.04.2011<br/>
 * Version 1.3, 27.07.2011<br/>
 * Version 2.0, 08.11.2013<br/>
 * 
 */

namespace WSC\templates\cronjob;

use APF\core\configuration\ConfigurationManager;
use WSC\templates\cronjob\FactoryCronjob;
use WSC\core\handler\AbstractHandler;

class CronjobHandler extends AbstractHandler
{
    /**
     * Executes the handler
     * @return void
     */
    public function execute()
    {
        // --- lade Config-File
        $oCnf = ConfigurationManager::loadConfiguration(
                    'WSC\templates\cronjobs',
                    Registry::retrieve('APF\core','App'),
                    null,
                    null,
                    'default_cronjobs.ini'
                );
        $aSections = $oCnf->getSectionNames();


        foreach ($aSections as $sCronjob) {            
            $sClass     = $oCnf->getSection($sCronjob)->getValue('Class');
            $sTiming    = $oCnf->getSection($sCronjob)->getValue('Timing');
            
            // --- Lade Cronjob-Klasse
            $oCJ = FactoryCronjob::factory($sClass);
            if (FactoryCronjob::hasError() === true) {
                $this->__sError = FactoryCronjob::getError();
                $this->__bError = true;
                return -1;
            }

            $oCJ->setCronjobName($sClass);
            $oCJ->initialize();

            $this->__sOutput = "Check Cronjob $sCronjob with Timing: $sTiming - "
                    .( ($oCJ->isRunable( $sTiming )===true)?'Yes':'No' )."<br/>";
            
            if( !$oCJ->isRunable( $sTiming ) ) {
                continue;
            }
            
            // --- Führe Cronjob aus
            $oCJ->run();

            $this->__sOutput .=  $oCJ->getContent();
            
            $this->__sOutput .=  "Cronjob $sCronjob run.<br/><br/>";

            // --- Log
            $this->__log('[cronjob]-'.$sCronjob.' erfolgreich ausgeführt');

        }
    }
}
