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
 * @file CompilerManager.php
 * @namespace WSC\external\compiler
 *
 * Handles the process and progress of the project compiling 
 *
 * @author Florian Horn
 * @version
 * Version 1.0, 14.08.2009<br/>
 * Version 2.0, 21.10.2010<br/>
 * Version 2.1, 26.02.2011<br/>
 * Version 2.2, 24.04.2011<br/>
 * Version 2.3, 25.08.2011<br/>
 * Version 3.0, 03.11.2013<br/>
 *
 */

namespace WSC\external\compiler;

use WSC\core\factory\AbstractStatic;
use APF\core\configuration\ConfigurationManager;
use APF\core\configuration\provider\BaseConfiguration;
use APF\core\registry\Registry;

class CompilerManager extends AbstractStatic
{
    protected static $__sCompilerLogFile        = "compiler.log";
    protected static $__aProjects               = array();
    protected static $__sDir                    = '';
    protected static $__sOutputDir              = '';
    protected static $__Environment             = 'DEV';
    protected static $__aApps                   = array();
    protected static $__tStartTime              = 0;
    protected static $__tStopTime               = 0;
    protected static $__aIncludes               = array();
    protected static $__Config                  = NULL;



    /**
     * Bereitet die Compilierung vor
     * @param BaseConfiguration $Config
     * @param array $aOptions
     */
    public static function prepare(BaseConfiguration $Config , array $aOptions = array())
    {
        /*
         * Konfiguration abspeichern
         */
        self::$__Config = $Config;


        /*
         * Logfile anlegen
         */
        if ($Config->getSection('Log')->getValue('Active') === 'true') {
            self::$__sCompilerLogFile = $Config->getSection('Log')->getValue('LogDir').DS.$Config->getSection('Log')->getValue('LogName');
            $fh = fopen(self::$__sCompilerLogFile,"w");
            fwrite($fh, "COMPILERLOG\r\n\r\n\r\n");
            fclose($fh);
        }



        /*
         * Lade Einstellungen
         */
        self::__writeToLog("Lade Einstellungen");
        self::__setBasicSettings($Config , $aOptions);
    }
    
    

    /**
     * Startet die Compilierung
     * @return void
     */
    public static function compile()
    {
        self::__writeToLog("Starte den Compilierungsvorgang" , true);

        self::$__tStartTime = explode(" ", microtime());
        /*
         * Compiler ausführen
         */
        self::__run();


        self::$__tStopTime = explode(" ", microtime());
        self::__writeToLog("\n\nCompilierungsvorgang erfolgreich beendet!" , true);
    }



    /**
     * Gibt Ergebnisse und statistische Werte aus
     * @return string
     */
    public static function printResults()
    {
        $sTimeStart = self::$__tStartTime;
        $sTimeStop  = self::$__tStopTime;
        return round( ( ($sTimeStop[0]+$sTimeStop[1])-($sTimeStart[1]+$sTimeStart[0]) ) * 1000 ) / 1000;
    }



    /**
     * Lade Einstellungen
     *
     *
     * @param BaseConfiguration $Config
     * @param array $aOptions
     */
    protected static function __setBasicSettings(BaseConfiguration $Config , array $aOptions = array())
    {
        /*
         * Basis-Einstellungen
         */
        self::$__aProjects  = array_map("strtolower" , $aOptions["Projects"]);
        self::$__sDir       = $Config->getSection("Settings")->getValue("BaseDir");
        self::$__sOutputDir = $Config->getSection("Settings")->getValue("OutputDir");


        
        /*
         * Includes
         */
        self::$__aIncludes = $Config->getSection("Includes")->getValueNames();
        foreach (self::$__aIncludes as $sName) {
            $value = $Config->getSection( "Includes" )->getValue($sName);
            self::$__aIncludes[$sName] = self::$__sDir.DS.$value;
        }


        /*
         * Include-Pfad setzen
         */
        set_include_path(get_include_path().PATH_SEPARATOR.implode(PATH_SEPARATOR, self::$__aIncludes));
        
        self::__writeToLog("Compilierte Projects: ".implode( ", ", self::$__aProjects));
        self::__writeToLog("Basisverzeichnis: ".self::$__sDir);
        self::__writeToLog("Output-Verzeichnis: ".self::$__sOutputDir);


        /*
         * Environment, Debug, Publish
         */
        $bEnvironmentFound = false;
        foreach ($Config->getSection('Environments')->getSection('Env')->getSectionNames() as $aEnvSectionNumber) {
            $oEnvSection = $Config->getSection('Environments')->getSection('Env')->getSection($aEnvSectionNumber);
            if ($oEnvSection->getValue('Short') === $aOptions['Env']) {
                 $sEnv = $oEnvSection->getValue('Macro');
                 $bEnvironmentFound = true;
                 break;
            }
        }
        
        if (!$bEnvironmentFound) {
            throw new \RuntimeException('['.get_class().'::__setBasicSettings] '.
                    'Could not find environment!');
        }
        
        self::__writeToLog("Environment: ".$sEnv, true);


        /*
         * Environment in Registry festhalten
         */
        Registry::register('APF\core',"Environment", $sEnv);
        self::$__Environment = $sEnv;
        
        
        /*
         * Environment in Konfigurationsdatei festhalten
         */
        $oCnfEnv = ConfigurationManager::loadConfiguration(
                'WSC\system',
                null,
                null,
                null,
                'environment.ini' 
            );
        $oCnfEnv->getSection('Environment')->setValue('Environment',$sEnv);
        ConfigurationManager::saveConfiguration(
                'WSC\system',
                null,
                null,
                null,
                'environment.ini',
                $oCnfEnv
            );


        /*
         * Lese die Projekt-Konfigurationen aus
         */
        foreach (self::$__aProjects as $Project) {
            // --- Configurations
            $ProjectCnf = ConfigurationManager::loadConfiguration(
                        'APF\apps',
                        $Project,
                        null,
                        null,
                        "compiler.ini"
                    );
            

            // --- System-Name
            if( $ProjectCnf->getSection("System") &&
                $ProjectCnf->getSection("System")->getValue('Project') !== null) {
                self::$__aApps[] = $ProjectCnf->getSection("System")->getValue('Project');
            } else {
                self::$__aApps[] = $Project;
            }
        }
    }



    /**
     * Start the compiling
     * @return void
     */
    protected static function __run()
    {
        /*
         * Lade Compiler-Klassen
         */
        $aSubSections   = self::$__Config->getSection('Order')->getSectionNames();
        $aPre           = array();
        $aApp           = array();
        $aPost          = array();
        
        
        foreach ($aSubSections as $sSection) {
            $sTiming = self::$__Config->getSection('Order')->getSection($sSection)->getValue('Timing');
            switch (strtolower($sTiming)) {
                case 'pre':
                    $n = count( $aPre );
                    $aPre[$n]['Class'] = self::$__Config->getSection('Order')->getSection($sSection)->getValue('Class');
                    break;
                case 'post':
                    $n = count( $aPost );
                    $aPost[$n]['Class'] = self::$__Config->getSection('Order')->getSection($sSection)->getValue('Class');
                    break;
                default:
                    $n = count( $aApp );
                    $aApp[$n]['Class'] = self::$__Config->getSection('Order')->getSection($sSection)->getValue('Class');
                    break;
            }
        }
        
        
        
        /*
         * Pre-Statements
         */
        foreach ($aPre as $aTag) {
            $sNamespaceClass = $aTag['Class'];
            
            self::__writeToLog("[Execute] ".$sNamespaceClass);
            
            try {
                $oClass = new $sNamespaceClass(self::$__Config, self::$__aApps);
            } catch (\InvalidArgumentException $e) {
                throw $e;
            }
            
        }
        
        
        /*
         * App-Statements
         */
        foreach ($aApp as $aTag) {
            foreach (self::$__aApps as $sApp) {
                $sNamespaceClass = $aTag['Class'];

                $oAppCnf = ConfigurationManager::loadConfiguration(
                            'APF\apps',
                            $sApp,
                            null,
                            null,
                            "compiler.ini"
                        );

                self::__writeToLog("[Execute] ".$sNamespaceClass);
                
                try {
                    $oClass = new $sNamespaceClass(self::$__Config, $sApp);
                } catch (\InvalidArgumentException $e) {
                    throw $e;
                }   
            }
        }
        
        
        /*
         * Post-Statements
         */
        foreach ($aPost as $aTag) {
            $sNamespaceClass = $aTag['Class'];
            
            self::__writeToLog("[Execute] ".$sNamespaceClass);
            
            try {
                $oClass = new $sNamespaceClass(self::$__Config, self::$__aApps);
            } catch (\InvalidArgumentException $e) {
                throw $e;
            } 
        }
    }



    /**
     * Writing log file
     * @param string $sMessage
     * @param boolean $bDoNotPrint
     * @return void
     */
    protected static function __writeToLog($sMessage , $bDoNotPrint = false)
    {
            if (!$bDoNotPrint) { 
                print( BR.$sMessage );
            }
            
            if ((string)self::$__Config->getSection('Log')->getValue('Active') !== 'true') { 
                return;
            }

            $fh = fopen(self::$__sCompilerLogFile,"a");
            fwrite($fh, $sMessage."\r\n" );
            fclose($fh);
    }
}
