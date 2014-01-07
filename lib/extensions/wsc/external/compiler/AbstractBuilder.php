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
 * @file AbstractBuilder.php
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

use APF\core\configuration\ConfigurationManager;
use APF\core\configuration\provider\BaseConfiguration;
use APF\core\registry\Registry;

abstract class AbstractBuilder
{
    protected $__oConfiguration     = null;
    protected $__sBaseDir           = '';
    protected $__sOutputDir         = '';
    protected $__sEnvironment       = 'DEV';
    protected $__sAppName           = '';
    protected $__aIgnore            = array('Directories' => array(), 'Files' => array());
    protected $__aForce             = array();
    protected $__aProtect           = array();
    protected $__aCopy              = array();
    protected $__aMakros            = array();
    protected $__aRename            = array();
    protected $__aFusionizers       = array();
    protected $__aMinifiers         = array();
    
    
    
    /**
     * Constructor
     * @param BaseConfiguration $oConfig 
     */
    public function __construct(BaseConfiguration $oAppConfig, $sAppName = '')
    {
        $this->__oConfiguration = $oAppConfig;
        $this->__sAppName       = $sAppName;        
        $this->__sEnvironment   = Registry::retrieve('APF\core',"Environment");
        
        $oConfig = ConfigurationManager::loadConfiguration(
                    'WSC\external\compiler',
                    null,
                    null,
                    null,
                    'config.ini'
                );
        $this->__sBaseDir   = $oConfig->getSection("Settings")->getValue("BaseDir");
        $this->__sOutputDir = $oConfig->getSection("Settings")->getValue("OutputDir");
        
        $this->addAppDirInformation($oAppConfig,$sAppName);
    }
    
    
    
    /**
     * Add app specified directory information
     * @param BaseConfiguration $oAppConfig 
     * @param string $sAppName
     * @return void
     */
    public function addAppDirInformation(BaseConfiguration $oAppConfig, $sAppName = null)
    {
        if ($oAppConfig === null) {
            return;
        }
        
        if (!($oAppConfig instanceof BaseConfiguration)) {
            throw new \InvalidArgumentException('['.get_class().'::addAppDirInformation] '.
                    'Invalid argument!');
        }
        
        
        
        // --- Rename
        if ($oAppConfig->getSection( "Rename" ) &&
            count( $oAppConfig->getSection( "Rename" )->getValueNames() ) !== 0) {
            foreach ($oAppConfig->getSection( "Rename" )->getValueNames() as $Statement) {
                $this->__aRename[ $sAppName ][$Statement] =  $oAppConfig->getSection( "Rename" )->getValue($Statement);
            }
        }

        // --- Fusionizer
        if ($oAppConfig->getSection( "Fusionizer" ) &&
            count( $oAppConfig->getSection( "Fusionizer" )->getValueNames() ) !== 0) {
            foreach ($oAppConfig->getSection( "Fusionizer" )->getValueNames() as $Section) {
                $a = explode( "." , $Section , 3 );
                $this->__aFusionizers[ $sAppName ][ $a[0] ][ $a[1] ][ $a[2] ] = 
                        $oAppConfig->getSection( "Fusionizer" )->getValue($Section);
            }
        }

        // --- Minifier
        if ($oAppConfig->getSection( "Minifier" ) &&
            count( $oAppConfig->getSection( "Minifier" )->getValueNames() ) !== 0) {
            foreach ($oAppConfig->getSection( "Minifier" )->getValueNames() as $Section) {
                $a = explode( "." , $Section , 3 );
                $this->__aMinifiers[ $sAppName ][ $a[0] ][ $a[1] ][ $a[2] ] = 
                        $oAppConfig->getSection( "Minifier" )->getValue($Section);
            }
        }
        

        // --- Komponenten/Makros
        if ($oAppConfig->getSection( "Macros") &&
            count( $oAppConfig->getSection( "Macros" )->getValueNames() ) !== 0) {
            foreach ($oAppConfig->getSection( "Macros" )->getValueNames() as $Comp) {
                if ($oAppConfig->getSection( "Macros" )->getValue($Comp) === "true") {
                    $this->__aMakros[] = "M_".strtoupper( (string)$Comp );
                } else {
                    $this->__aMakros[] = "_M_".strtoupper( (string)$Comp );
                }
            }
        }
        
        switch (Registry::retrieve("APF\core","Environment")) {
            case "DEV": 
                $this->__aMakros[] = "DEBUG"; 
                $this->__aMakros[] = "_PUBLISH"; 
                break;
            case "TEST": 
                $this->__aMakros[] = "_DEBUG"; 
                $this->__aMakros[] = "_PUBLISH"; 
                break;
            case "LIVE": 
                $this->__aMakros[] = "_DEBUG"; 
                $this->__aMakros[] = "PUBLISH"; 
                break;
        }
        $this->__aMakros[] = "ENV_".Registry::retrieve("APF\core","Environment");
        
        
        // --- PHP-Version
        if ($oAppConfig->getSection( "PHP" ) &&
            $oAppConfig->getSection( "PHP" )->getValue("Version")) {
            $this->__aMakros[] = "PHP_".strtoupper(str_replace(".","_",($oAppConfig->getSection( "PHP" )->getValue( "Version" ))));
        }
        
        
        // --- App
        $this->__aMakros[] = strtoupper( $sAppName );
        
        
        // --- Unique-Anwendung
        $this->__aMakros = array_unique( $this->__aMakros );
    }
    
    
    
    
    /**
     * Add system specified directory information 
     * @param BaseConfiguration $oSysConfig
     * @return void
     */
    public function addSystemDirInformation(BaseConfiguration $oSysConfig)
    {
        if( $oSysConfig === NULL ) {
            return;
        }
        
        if (!($oSysConfig instanceof BaseConfiguration)) {
            throw new \InvalidArgumentException('['.get_class().'::addSystemDirInformation] '.
                    'Invalid argument!');
        }
        
        // --- Ignore-Pfade
        // Directories
        if ($oSysConfig->getSection( "Ignore" ) && $oSysConfig->getSection( "Ignore" )->getSection("Dirs") &&
            count( $oSysConfig->getSection( "Ignore" )->getSection("Dirs")->getValueNames() ) !== 0) {
            foreach ($oSysConfig->getSection( "Ignore" )->getSection("Dirs")->getValueNames() as $sDir) {
                $sRootDir = $this->__sBaseDir.DS.$oSysConfig->getSection( "Ignore" )->getSection("Dirs")->getValue($sDir);
                $aSubDirs = array();
                $aAllFiles = array();
                $this->__getDirectoryEntries($sRootDir, $aAllFiles, $aSubDirs);
                $this->__aIgnore['Directories'][] = $this->__sBaseDir.DS.$oSysConfig->getSection( "Ignore" )->getSection("Dirs")->getValue($sDir);
                $this->__aIgnore['Directories'] = \array_merge( $this->__aIgnore['Directories'], $aSubDirs);
                $this->__aIgnore['Files'] = \array_merge( $this->__aIgnore['Files'], $aAllFiles);
            }
        }
        
        // Files
        if ($oSysConfig->getSection( "Ignore" ) && $oSysConfig->getSection( "Ignore" )->getSection("Files") &&
            count( $oSysConfig->getSection( "Ignore" )->getSection("Files")->getValueNames() ) !== 0) {
            foreach ($oSysConfig->getSection( "Ignore" )->getSection("Files")->getValueNames() as $sFile) {
                $this->__aIgnore['Files'][] = $this->__sBaseDir.DS.$oSysConfig->getSection( "Ignore" )->getSection("Files")->getValue($sFile);
            }
        }
        
        // --- Force-Pfade
        if ($oSysConfig->getSection("Force") &&
            count( $oSysConfig->getSection( "Force" )->getValueNames() ) !== 0) {
            $Sec = $oSysConfig->getSection( "Force" )->getValueNames();
            for ($n = 0, $i = count( $Sec); $n < $i; ++$n) {
                $this->__aForce[] = $this->__sBaseDir.DS.$oSysConfig->getSection("Force")->getValue('Dirs.'.$n);
            }
        }
        
        // --- Protect-Pfade
        if ($oSysConfig->getSection("Protect") &&
            count( $oSysConfig->getSection( "Protect" )->getValueNames() ) !== 0) {
            $Sec = $oSysConfig->getSection( "Protect" )->getValueNames();
            for ($n = 0, $i = count( $Sec); $n < $i; ++$n) {
                $this->__aProtect[] = $this->__sBaseDir.DS.$oSysConfig->getSection("Protect")->getValue('Dirs.'.$n);
            }
        }
        
        // --- Dirs to be just copied, not compiled
        if ($oSysConfig->getSection("Copy") &&
            count( $oSysConfig->getSection("Copy")->getValueNames() ) !== 0) {
            $Sec = $oSysConfig->getSection( "Copy" )->getValueNames();
            for ($n = 0, $i = count( $Sec); $n < $i; ++$n) {
                $this->__aCopy[]    = $this->__sBaseDir.DS.$oSysConfig->getSection("Copy")->getValue('Dirs.'.$n);
                $this->__aIgnore[]  = $this->__sBaseDir.DS.$oSysConfig->getSection("Copy")->getValue('Dirs.'.$n);
                $this->__aProtect[] = $this->__sBaseDir.DS.$oSysConfig->getSection("Copy")->getValue('Dirs.'.$n);
            }
        }
        
        
        // --- Unique-Anwendung
        $this->__aIgnore['Directories'] = array_unique($this->__aIgnore['Directories']);
        $this->__aIgnore['Files'] = array_unique($this->__aIgnore['Files']);
        $this->__aCopy      = array_unique($this->__aCopy);
        $this->__aForce     = array_unique($this->__aForce);
        $this->__aProtect   = array_unique($this->__aProtect);
    }
    
    
    
    
    /**
     * Delete folder recursively except the subfolders declared in $aIngoreDirs
     * @param string $sDirEntry
     * @param array $aIgnoreDirs
     * @return void
     */
    protected function __clearFolder($sDirEntry , array $aIgnoreDirs = array())
    {
        if (is_dir($sDirEntry) && 
            !in_array( $sDirEntry, $aIgnoreDirs )) {
            /*
             * Delete all files and folders without subfolders
             * Get all folders with subfolders back
             */
            $aFoldersToBeKilled = $this->__deleteDirRecursive(
                        $sDirEntry,
                        array(),
                        $aIgnoreDirs
                    );

            // --- Delete deepest subfolder at first
            $aFoldersToBeKilled = array_reverse($aFoldersToBeKilled);

            // --- Stepping through
            foreach ($aFoldersToBeKilled as $Entry) {
                @rmdir($Entry);
            }
            
            return;
        }
        @unlink($sDirEntry);
    }



    /**
     * Creates an directory or throws an exception if it is not possible
     * @param string $sDir
     */
    protected function __createDir($sDir)
    {
        if (!@is_dir( $sDir ) && !@mkdir( $sDir , 0755 )) {
            throw new \RuntimeException( "Folder ".$sDir." could not be created!" );
        }
    }

    

    /**
     * Deletes a directory recursive and keep all subdirectories declared in
     * $aIgnoreDirs
     * @param string $sDir
     * @param array $aZomieFolders
     * @param array $aIgnoreDirs
     * @return array
     */
    protected function __deleteDirRecursive($sDir , array $aZomieFolders = array(), array $aIgnoreDirs = array())
    {
        if (in_array($sDir, $aIgnoreDirs)) { 
            return array();
        }

        $aZomieFolders[] = $sDir;

        // --- Handler oeffnen
        $fp = @opendir($sDir);

        /*
         * Verzeichniseintraege durchlaufen
         */
        while ($dir_file = @readdir($fp)) {
            if (($dir_file == '.') || ($dir_file == '..')) {
                continue;
            }

            $sDirNew = $sDir.DS.$dir_file;

            /*
             * Ist der Eintrag ein Verzeichnis?
             * - Wenn ja, ist er als "ignore" gekennzeichnet?
             *   - Wenn nein, unterverzeichnis durchforsten nach Dateien
             * - Wenn nein => Datei, diese wird geloescht
             */
            if (is_dir($sDirNew) &&
                !in_array($sDirNew, $aIgnoreDirs)) {
                $aZomieFolders = $this->__deleteDirRecursive(
                            $sDirNew,
                            $aZomieFolders,
                            $aIgnoreDirs
                        );
            } else {
                // --- Loeschen
                @unlink($sDirNew);
            }
        }

        // --- Handler schliessen
        @closedir($fp);

        return $aZomieFolders;
    }



    /**
     * Get the directory entry informations
     * @param string $sDir
     * @param array $aAllFiles
     * @param array $aAllDirs
     * @param array $aIgnoreDirs
     */
    protected function __getDirectoryEntries($sDir, array &$aAllFiles, array &$aAllDirs, array $aIgnoreDirs = array())
    {
        $handle = opendir($sDir);
        if (!$handle) {
            return;
        }

        while (false !== ($file = readdir($handle))) {
            if($file === '.' || $file === '..' || $file[0] === '.') {
                continue;
            }

            $dir = $sDir.DS.$file;
            if (is_dir($dir)) {
                if (!\in_array($dir, $aIgnoreDirs)) {
                    $aAllDirs[] = $dir;
                    $this->__getDirectoryEntries(
                                $dir,
                                $aAllFiles,
                                $aAllDirs,
                                $aIgnoreDirs
                            );
                }
                else {
                    continue;
                }
            }
            else {
                $aAllFiles[] = $dir;
            }
        }
    }



    /**
     * Check if the folder is empty
     * @param string $sFolder
     * @return boolean
     */
    protected function __checkIfFolderIsEmpty($sFolder)
    {
        $aFiles = array();
        $handle = opendir($sFolder);
        if (!$handle) {
            throw new \RuntimeException(
                    sprintf(
                            '['.get_class().'::__getDirectoryEntries()] '.
                            'Could not open folder "%s"', 
                            $sFolder
                            )
                    );
        }
        

        while (false !== ( $file = readdir($handle))) {
            if ($file != "." && $file != "..") {
                $aFiles[] = $file;
            }
        }
        closedir($handle);
        
        return count($aFiles) > 0 ? false : true;
    }



    /**
     * Delete all empty (no files, no subfolders) directories
     * @param string $sDir
     * @param array $aIgnoreDirs 
     */
    protected function __deleteEmptyDirectories($sDir, array $aIgnoreDirs = array())
    {
        $handle = opendir( $sDir );
        if (!$handle) {
            throw new \RuntimeException(
                    sprintf(
                            '['.get_class().'::__getDirectoryEntries()] '.
                            'Could not open folder "%s"',
                            $sDir
                            ) 
                    );
        }

        while (false !== ($file = readdir($handle))) {
            if ($file !== '.' && $file !== '..') {
                $dir = $sDir.DS.$file;
                if (is_dir($dir)) {
                    if (!in_array($file, $aIgnoreDirs)) {
                        $this->__deleteEmptyDirectories($dir, $aIgnoreDirs);
                        if ($this->__checkIfFolderIsEmpty($dir)) {
                            @rmdir( $dir );
                        }
                    }
                    else {
                        continue;
                    }
                }
                else {
                    continue;
                }
            }
        }
        closedir($handle);
    }
}
