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
 * @file PreAbstractBuilder.php
 * @namespace WSC\external\compiler
 *
 *
 * @author Florian Horn
 * @version
 * Version 1.0, 25.08.2011<br/>
 * Version 2.0, 03.11.2013<br/>
 *
 */

namespace WSC\external\compiler;

use WSC\external\compiler\AbstractBuilder;
use APF\core\configuration\ConfigurationManager;
use APF\core\configuration\provider\BaseConfiguration;
use APF\core\configuration\provider\ini\IniConfiguration;

class PreAbstractBuilder extends AbstractBuilder
{
    protected $__aAllDirs   = array();
    protected $__aAllFiles  = array(); 

        
    /**
     * Constructor
     * @param BaseConfiguration $oAppConfig 
     */
    public function __construct(BaseConfiguration $oSysConfig, array $aApps = array())
    {
        parent::__construct(new IniConfiguration());
        $this->addSystemDirInformation($oSysConfig);
        
        // --- Dateien die durchlesen werden
        $this->__aAllDirs   = array(); 
        $this->__aAllFiles  = array();
        $this->__aIgnore[]  = $this->__sOutputDir;
        
        // --- Analysiere Basis-Verzeichnisstruktur
        $this->__getDirectoryEntries($this->__sBaseDir, $this->__aAllFiles, $this->__aAllDirs, $this->__aIgnore);
        
        foreach ($aApps as $sApp) {
            $oAppConfig = ConfigurationManager::loadConfiguration(
                        'APF\apps',
                        $sApp,
                        null,
                        null,
                        "compiler.ini"
                    );
            $this->addAppDirInformation($oAppConfig, $sApp);
        }


        /*
         * Filterung
         */
        $this->__aAllDirs = \array_merge($this->__aAllDirs, $this->__aForce); 
        $this->__aAllDirs = \array_diff($this->__aAllDirs, $this->__aIgnore['Directories']);
        $this->__aAllFiles = \array_diff($this->__aAllFiles, $this->__aIgnore['Files']);
        
        // --- Add special directories and files after filtering
        $this->__aAllDirs[] = $this->__sBaseDir.'/lib/apps';
        $this->__aAllDirs[] = $this->__sBaseDir.'/lib/config/apps';
        foreach ($aApps as $sApp) {
            // --- Special files...
            $sSystemname = \strtolower($sApp);
            $sProject = $this->__sBaseDir."/lib/apps/".$sSystemname;
            $this->__aAllDirs[] = $sProject;
            $this->__aAllDirs[] = $this->__sBaseDir.'/lib/config/apps/'.$sSystemname;
            $this->__aAllFiles[]= $this->__sBaseDir.'/lib/config/apps/'.$sSystemname.'/'.$this->__sEnvironment.'_config.ini';
            $this->__getDirectoryEntries( $sProject , $this->__aAllFiles , $this->__aAllDirs , $this->__aIgnore );
            $this->__aMakros[] = $sSystemname;
        }
        

        /*
         * Sortierung
         */       
        $this->__aAllDirs = \array_unique($this->__aAllDirs, \SORT_REGULAR);
        $this->__aAllFiles = \array_unique($this->__aAllFiles, \SORT_REGULAR);
        
        $fCmpFunction = function ($a, $b)
                {
                    if (\strlen($a) === \strlen($b)) {
                        return 0;
                    }
                    return (\strlen($a) < \strlen($b)) ? -1 : 1;
                };
        \usort($this->__aAllDirs, $fCmpFunction);
        \usort($this->__aAllFiles, $fCmpFunction);
    }
}
