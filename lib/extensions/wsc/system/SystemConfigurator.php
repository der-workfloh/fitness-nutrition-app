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
 * @file SystemConfigurator.php
 * @namespace WSC\system
 *
 *
 *
 * @author Florian Horn
 * @version
 * Version 1.0, 26.10.2010<br/>
 * Version 1.1, 24.04.2011<br/>
 * Version 1.2, 15.09.2011<br/>
 * Version 2.0, 06.11.2013<br/>
 */

namespace WSC\system;

use APF\core\registry\Registry;
use APF\core\configuration\ConfigurationManager;
use WSC\core\factory\AbstractStatic;

class SystemConfigurator extends AbstractStatic
{
    const DEBUG_MODE_ON             = true;
    const DEBUG_MODE_OFF            = false;
    
    protected static $__bDebugMode  = false;
    
    /**
     * Run system configuration process
     * @param string $sContext
     */
    public static function run($sContext)
    {
        self::setDefiner();
        self::setInitSettings();
        self::fixRequestURI();
        self::undoMagicQuote();

        // --- Context festhalten
        Registry::register('APF\core','App',$sContext);

        $oCnfEnv = ConfigurationManager::loadConfiguration(
                'WSC\system',
                null,
                null,
                null,
                'environment.ini' 
            );
        $sEnvironment = $oCnfEnv->getSection('Environment')->getValue('Environment');
        
        
        

        // --- Environment festhalten
        Registry::register('APF\core','Environment', $sEnvironment);

        /*
         * System-Config laden
         */
        $CnfSys = ConfigurationManager::loadConfiguration(
                    'WSC\System',
                    NULL,
                    NULL,
                    Registry::retrieve('APF\core','Environment'),
                    'config.ini' 
                );
        $CnfClt = ConfigurationManager::loadConfiguration(
                    'APF\apps',
                    $sContext,
                    NULL,
                    Registry::retrieve('APF\core','Environment'),
                    'config.ini' 
                );


        /*
         * Absoluter Pfad für Cronjobs!
         */
        $_SERVER["DOCUMENT_ROOT"] = $CnfSys->getSection('Paths')->getValue( 'Docroot' );
        $BasePath = $_SERVER["DOCUMENT_ROOT"].$CnfSys->getSection('Paths')->getValue('Sysbind').DS;

        /*
         * Wartungsseite
         */
        if ($CnfClt->getSection('System')->getValue('Maintenance') === 'true') {
            $sFile = $BasePath.'lib'.DS.'apps'.DS.$sContext.DS.'pres'.DS.'maintenance.php';

            $s = '';
            if (file_exists($sFile)) {
                $s = file_get_contents($sFile);
            }

            print( $s );
            exit(0);
        }

        self::$__bDebugMode = $CnfClt->getSection('System')->getValue('Debug') === 'true' ? 
                self::DEBUG_MODE_ON : self::DEBUG_MODE_OFF;
        


        /*
         * LIBRARIES EINBINDEN
         */
        $aIncludes = $CnfSys->getSection('Includes')->getValueNames();
        if (count($aIncludes) !== 0) {
            $sIncPath = '';
            foreach ($aIncludes as $sInclude) {
                $sIncPath .= PATH_SEPARATOR.$BasePath.DS.$CnfSys->getSection('Includes')->getValue( $sInclude );
            }
            set_include_path(get_include_path().PATH_SEPARATOR.$sIncPath);
        }

    }
    
    
    
    
    /**
     * Get debug mode
     * @return boolean
     */
    public static function getDebugMode() 
    { 
        return self::$__bDebugMode; 
    }
    



    /**
     * Set definitions
     * @return void
     */
    public static function setDefiner()
    {
        /*
         * DEFINITIONS
         */
        if (!defined('PHP_EOL')) {
            define('PHP_EOL',"\n");
        }
        
        define( "DS" , '/' );
        define( "__VALIDATION__" , true );
    }



    /**
     * Set php-ini settings
     * @return void
     */
    public static function setInitSettings()
    {
        /*
         * PHP-INI EINSTELLUNGEN
         */
        ini_set('session.use_cookies', true );
        ini_set('session.use_only_cookies', true );
        ini_set('session.use_trans_sid', false );
        ini_set('magic_quotes_gpc', 'Off' );
        ini_set('magic_quotes_runtime', 'Off' );
        ini_set('magic_quotes_sybase', 'Off' );

        date_default_timezone_set( "Europe/Berlin" );
    }



    /**
     * Fix the request URI
     * @return void
     */
    public static function fixRequestURI()
    {
        if (!isset($_SERVER['REQUEST_URI'])) {
            if (isset($_SERVER['SCRIPT_NAME'])) {
                $_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'];
            } else {
                $_SERVER['REQUEST_URI'] = $_SERVER['PHP_SELF'];
            } 
            
            if (isset( $_SERVER['QUERY_STRING'])) {
                $_SERVER['REQUEST_URI'] .=  '?'.$_SERVER['QUERY_STRING'];
            }
        }
    }



    /**
     * Undo magic quoting
     * @return void
     */
    public static function undoMagicQuote()
    {
        if (get_magic_quotes_gpc()) {
            $process = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
            while (list($key, $val) = each($process)) {
                foreach ($val as $k => $v) {
                    unset($process[$key][$k]);
                    if (is_array($v)) {
                        $process[$key][stripslashes($k)] = $v;
                        $process[] = &$process[$key][stripslashes($k)];
                    }
                    else {
                        $process[$key][stripslashes($k)] = stripslashes($v);
                    }
                }
            }
            unset($process);
        }
    }
}
