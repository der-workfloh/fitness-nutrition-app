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
 * @file ProjectHelperManager.php
 * @namespace WSC\external\projecthelper
 *
 *
 * PROJECTHELPER
 *
 * @author Florian Horn
 * @version
 * Version 1.0, 08.08.2011<br/>
 * Version 2.0, 08.11.2013<br/>
 *
 */

namespace WSC\external\projecthelper;

use APF\core\configuration\Configuration;
use WSC\core\factory\AbstractStatic;

class ProjectHelperManager extends AbstractStatic
{
    const DIRECTORY_CREATE_RIGHTS               = 0755;
    
    protected static $__sLogFileName            = "ph.log";
    protected static $__sAppName                = '';
    protected static $__sBaseDir                = '';
    protected static $__sSourceDir              = '';
    protected static $__sScheme                 = 'default';
    protected static $__aIgnorePattern          = array();
    protected static $__tStartTime              = 0;
    protected static $__tStopTime               = 0;
    protected static $__aAllDirs                = array();
    protected static $__aAllFiles               = array();
    protected static $__aDirectoryInfos         = array();
    protected static $__iFileCounter            = 0;
    protected static $__oConfig                 = NULL;
    protected static $__oSchemeConfig           = NULL;
    protected static $__sAppDir                 = 'lib/apps';
    protected static $__sSchemeAppPlaceholder   = '{{{APP}}}';



    /**
     * Bereite den ProjectHelper vor
     * @param Configuration $Config
     * @param array $aOptions
     */
    public static function prepare(Configuration $Config, array $aOptions = array())
    {
        /*
         * Konfiguration abspeichern
         */
        self::$__oConfig = $Config;


        /*
         * Logfile anlegen
         */
        if ($Config->getSection('Settings')->getValue('Log') === 'true') {
            $sBaseDir = dirname( __FILE__ );
            $fh = fopen( $sBaseDir."/".self::$__sLogFileName,"w");
            fwrite($fh, "PROJECTHELPER LOG\n\n\n" );
            fclose($fh);
        }



        /*
         * Lade Einstellungen
         */
        self::__writeToLog("Lade Einstellungen");
        self::__setBasicSettings($Config, $aOptions);


        /*
         * Analysiert die Datei und Ordner-Strukturen
         */
        self::__writeToLog("Starte Vorbereitungen");
        self::__prepareScheme();
    }

    
    
    /**
     * Starte den Vorgang
     * @return void
     */
    public static function start()
    {
        self::__writeToLog("Starte den Vorgang");

        self::$__tStartTime = explode(" ", microtime());
        /*
         * Compiler ausführen
         */
        self::__start();


        self::$__tStopTime = explode(" ", microtime());
        self::__writeToLog("\n\nVorgang erfolgreich beendet!" , true);
    }



    /**
     * Lade Einstellungen
     *
     *
     * @param Configuration $Config
     * @param array $aOptions
     */
    protected static function __setBasicSettings(Configuration $Config, array $aOptions = array())
    {
        /*
         * Basis-Einstellungen
         */
        self::$__sAppName = strtolower($aOptions['AppName']);
        self::$__sBaseDir = $Config->getSection('Settings')->getValue('BaseDir');
        self::$__sSourceDir = self::$__sBaseDir.'/'.$Config->getSection('Settings')->getValue('SourceDir');
        self::$__aIgnorePattern = explode(' ', $Config->getSection('Settings')->getValue('IgnorePattern'));
        self::$__sScheme = strtolower( strlen($aOptions['Scheme']) !== 0 ? $aOptions['Scheme'] : 'default');
    }



    /**
     * Liest die übergebenen Parameter aus
     * @return void
     */
    protected static function __prepareScheme()
    {
        /*
         * Konfiguration
         */
        $sSchemeConfigFile = self::$__sSourceDir.'/'.self::$__sScheme.'/scheme.xml';
        
        if (!file_exists($sSchemeConfigFile)) {
            throw new \RuntimeException( '['.\get_class().'::__prepareStructure()] '.
                    'Scheme configuration file does not exist!' );
        }
        
        self::$__oSchemeConfig = \simplexml_load_file($sSchemeConfigFile);
        
        
        // --- Dateien die durchlesen werden
        
        foreach (self::$__oSchemeConfig->dir as $oDir) {
            $sDirName = (string)$oDir['name'];
            self::$__aDirectoryInfos[$sDirName]['source'] = self::$__sSourceDir.'/'.self::$__sScheme.'/'.$sDirName;
            self::$__aDirectoryInfos[$sDirName]['target'] = self::$__sBaseDir.'/'.\str_replace(self::$__sSchemeAppPlaceholder,self::$__sAppName,(string)$oDir->target);
            self::$__aDirectoryInfos[$sDirName]['aAllFiles'] = array();
            self::$__aDirectoryInfos[$sDirName]['aAllDirs'] = array();
            
            self::__getDirectoryEntries( self::$__aDirectoryInfos[$sDirName]['source'], 
                    self::$__aDirectoryInfos[$sDirName]['source'], 
                    self::$__aDirectoryInfos[$sDirName]['aAllFiles'], 
                    self::$__aDirectoryInfos[$sDirName]['aAllDirs'] );
        }
        
    }
    
    


    /**
     * Start the process
     * @return void
     */
    protected static function __start()
    {
        //
        // Zielverzeichnisse erstellen
        //
        foreach (self::$__aDirectoryInfos as $aDirectoryInfo) {
            self::__mkdir($aDirectoryInfo['target'], self::DIRECTORY_CREATE_RIGHTS);
            $aDirs = $aDirectoryInfo['aAllDirs'];
            foreach ($aDirs as $sDir) {
                // --- Erzeugen
                self::__mkdir($aDirectoryInfo['target'].'/'.$sDir, self::DIRECTORY_CREATE_RIGHTS);
            }
        }
        
        
        //
        // --- Kopieren der jeweiligen Datein in die Unterverzeichnisse
        //
        foreach (self::$__aDirectoryInfos as $aDirectoryInfo) {
            $aFiles = $aDirectoryInfo['aAllFiles'];
            foreach ($aFiles as $sFile) {
                $sContent = \file_get_contents($aDirectoryInfo['source'].'/'.$sFile);
                
                // --- Dateien auf Makro scannen
                if(\preg_match('#'.self::$__sSchemeAppPlaceholder.'#imsU', $sContent)) {
                    $sContent = \preg_replace('#'.self::$__sSchemeAppPlaceholder.'#imsU', self::$__sAppName, $sContent);
                }
                
                // --- Erzeugen
                $fh = \fopen($aDirectoryInfo['target'].'/'.$sFile, 'w');
                if ($fh === false) {
                    throw new \RuntimeException( '['.\get_class().'::__start()] '.
                    'File '.$sFile.' could not be created!' );
                }
                \fwrite($fh, $sContent);
                \fclose($fh);
            }
        }
    }
    
    
    
    /**
     * Creates a directory
     * @param type $sDirectory
     * @param type $sMode 
     */
    protected static function __mkdir( $sDirectory, $sMode )
    {
        // --- Erzeugen
        \mkdir($sDirectory, $sMode);
        if (!\is_dir($sDirectory)) {
            throw new \RuntimeException( '['.\get_class().'::__mkdir()] '.
                    'Directory could not be created!' );
        }
    }



    /**
     * Writing log file
     * @param string $sMessage
     * @param boolean $bDoNotPrint
     * @return void
     */
    protected static function __writeToLog($sMessage, $bDoNotPrint = false)
    {
            if (!$bDoNotPrint) {
                print(BR.$sMessage);
            }
            
            if ((string)self::$__oConfig->getSection('Settings')->getValue('Log')  !== 'true') {
                return;
            }

            $sBaseDir = dirname(__FILE__);
            $fh = \fopen($sBaseDir.self::$__sLogFileName,"a");
            \fwrite($fh, $sMessage."\n" );
            \fclose($fh);
    }



    /**
     * Get the folder entries
     * @param string $sBaseDir
     * @param string $sCurrentDir
     * @param array $aAllFiles
     * @param array $aAllDirs
     * @param array $aIgnoreDirs
     */
    protected static function __getDirectoryEntries($sBaseDir, $sCurrentDir, array &$aAllFiles, array &$aAllDirs, array $aIgnoreDirs = array())
    {
        $handle = \opendir( $sCurrentDir );
        while (($file = \readdir($handle) ) !== false) {
            if ($file === '.' || $file === '..' || $file[0] === '.') { 
                continue;
            }
                
            $dir = $sCurrentDir.'/'.$file;
            if (\is_dir($dir)) {
                if (!\in_array($dir, $aIgnoreDirs)) {
                    $aAllDirs[] = \substr(
                                $dir,
                                \strlen($sBaseDir) + 1,
                                \strlen($dir) - \strlen($sBaseDir) 
                            );
                    self::__getDirectoryEntries(
                                $sBaseDir,
                                $dir,
                                $aAllFiles,
                                $aAllDirs,
                                $aIgnoreDirs 
                            );
                } else {
                    continue;
                }
            } else {
                $aAllFiles[] = \substr(
                            $dir,
                            \strlen($sBaseDir) + 1,
                            \strlen($dir) - \strlen($sBaseDir)
                        );
            }
        }
        \closedir($handle);
    }

}
