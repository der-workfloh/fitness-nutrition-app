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
 * @file Copier.php
 * @namespace WSC\external\compiler
 *
 *
 * @author Florian Horn
 * @version
 * Version 1.0, 21.10.2010<br/>
 * Version 1.1, 26.02.2011<br/>
 * Version 1.2, 25.08.2011<br/>
 * Version 2.0, 03.11.2013<br/>
 *
 */

namespace WSC\external\compiler;

use WSC\external\compiler\PreAbstractBuilder;
use APF\core\configuration\provider\BaseConfiguration;

class Copier extends PreAbstractBuilder
{
    /**
     * Additional directories to ignore
     * @var array
     */
    protected $__aAddIgnore = array();
    
    
    
    /**
     *
     * @param BaseConfiguration $oSysConfig
     * @param array $aApps
     * @return void
     */
    public function __construct(BaseConfiguration $oSysConfig, array $aApps = array())
    {
        parent::__construct($oSysConfig, $aApps);        
        $this->startCopyingFiles();
    }



    /**
     * Start copying
     * @return void
     */
    public function startCopyingFiles()
    {
        if (empty($this->__aCopy)) { 
            return;
        }

        /*
         * Filtering directories, which already exists
         */
        clearstatcache();
        foreach ($this->__aCopy as $value) {
            if (@is_dir(str_replace($this->__sBaseDir,$this->__sOutputDir,$value))) {
                $this->__aAddIgnore[] = $value;
            }
        }
        
        /*
         * Files which should not be compiled, just copied
         */
        foreach ($this->__aCopy as $value) {
            if (in_array($value, $this->__aAddIgnore)) {
                continue;
            }
            
            $sOutDir = str_replace($this->__sBaseDir, $this->__sOutputDir, $value);
            $sSrcDir = $value;

            // --- Wenn schon vorhanden, gehe weiter
            if (!@is_dir($sOutDir)) {
                // --- Grundverzeichnis anlegen
                $this->__createDir($sOutDir);
            }

            // --- Verzeichnisse anlegen
            $tmpDirs = array(); 
            $aDummy = array();
            $this->__getDirectoryEntries(
                        $sSrcDir,
                        $aDummy,
                        $tmpDirs,
                        $this->__aIgnore 
                    );

            // --- Unterverzeichnisse anlegen
            foreach ($tmpDirs as $sDirToCreate) {
                $this->__createDir(str_replace($this->__sBaseDir, $this->__sOutputDir, $sDirToCreate));
            }

            // --- Dateien suchen und analysieren
            $aFiles = $this->__parseSubFolders($sSrcDir);

            // --- Kopieren
            foreach ($aFiles as $sFile) {
                if (file_exists($sFile)) {
                    copy($sFile, str_replace($this->__sBaseDir,$this->__sOutputDir,$sFile));
                }
            }
        }
    }



    /**
     * Parse subfolders
     * @param string $sCurrentFolder
     * @return array
     */
    protected function __parseSubFolders($sCurrentFolder)
    {
        $tmpDirs   = array();
        $tmpFiles  = array();
        $this->__getDirectoryEntries(
                    $sCurrentFolder,
                    $tmpFiles,
                    $tmpDirs,
                    $this->__aIgnore 
                );
        
        $tmpFiles = array_unique($tmpFiles);
        
        return $tmpFiles;
    }
}
