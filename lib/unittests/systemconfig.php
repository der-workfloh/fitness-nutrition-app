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
 * @file systemconfig.php
 * @namespace \
 * 
 * SYSTEM-CONFIGURATION
 *
 * Notwendige Konfigurationen für das System
 *
 * @author Florian Horn
 * @version
 * Version 1.0, 26.02.2011<br/>
 * Version 2.0, 06.11.2013<br/>
 *
 */


use WSC\system\SystemConfigurator;

/*
 * PHP-INI EINSTELLUNGEN
 */
SystemConfigurator::setInitSettings();

/*
 * DEFINITIONS
 */
SystemConfigurator::setDefiner();

/*
 * Repariert die Request-URI unter windows
 */
SystemConfigurator::fixRequestURI();

/*
 * VERHINDERN VON AUTOMATISCHER MASKIERUNG
 */
SystemConfigurator::undoMagicQuote();



/*
 * Load libraries
 */
use APF\core\registry\Registry;
use APF\core\singleton\Singleton;
use APF\core\logging\Logger;
use APF\core\logging\writer\StdOutLogWriter;
use APF\core\configuration\ConfigurationManager;
use APF\core\configuration\provider\ini\IniConfigurationProvider;
use WSC\core\language\LanguageManager;
use WSC\core\database\DatabaseFactory as wsDB;
use WSC\core\configuration\provider\ini\ReadonlyIniConfigurationProvider;



/*
 * Konfiguration-Provider registrieren
 */
$provLang = new IniConfigurationProvider();
$provLang->setOmitContext(false);
$provLang->setOmitEnvironment(true);
ConfigurationManager::registerProvider('lini', $provLang );
ConfigurationManager::registerProvider('rini', new ReadonlyIniConfigurationProvider() );

$provOrm = new IniConfigurationProvider();
$provOrm->setOmitContext(true);
$provOrm->setOmitEnvironment(true);
ConfigurationManager::registerProvider('orm', $provOrm );



Registry::register('APF\core','LogDir', UnittestDir.'/logs');
Registry::register('APF\core','Environment','DEV');
Registry::register('APF\core','App',$AppName);

/*
 * Konfigurationen laden
 */
$CnfSys = ConfigurationManager::loadConfiguration(
            'WSC\system', 
            null, 
            null, 
            null, 
            'unittest.ini'
        );
$CnfClt = ConfigurationManager::loadConfiguration( 
            'APF\apps', 
            $AppName, 
            null, 
            Registry::retrieve('APF\core','Environment'), 
            'config.ini'
        );



/*
 * Absoluter Pfad für Cronjobs!
 */
$_SERVER["DOCUMENT_ROOT"] = $CnfSys->getSection('Paths')->getValue('Docroot');





$sSysPaths = $CnfSys->getSection('Paths')->getValue('Docroot').$CnfSys->getSection('Paths')->getValue('Sysbind');
Registry::register('APF\core', 'SystemPath', $sSysPaths );

// --- Log
Registry::register('APF\core', 'LogPath', $sSysPaths.DS.'logs' );

// --- Session
Registry::register('APF\core', 'SessionTime' , (int) $CnfClt->getSection('Session')->getValue('Duration') );

// --- Cookies
Registry::register('APF\core', 'CookieTime', time() + (int) $CnfClt->getSection('Cookies')->getValue('Duration') );

// --- SSL
Registry::register('APF\core', 'UseSSL',( $CnfClt->getSection('SSL')->getValue('Active') === 'true' ) ? true : false );




/*
 * Registry
 */
// Log-Verzeichnis anpassen
Registry::register('APF\core', 'LogDir', Registry::retrieve('APF\core', 'LogPath'));



/*
 * Logging
 */
$oLogger = Singleton::getInstance('APF\core\logging\Logger');
$oLogger->addLogWriter(
   'stdout',
   new StdOutLogWriter()
);
$oLogger->addLogWriter(
    'language',
    new StdOutLogWriter
 );
 $oLogger->addLogWriter(
    'template',
    new StdOutLogWriter
 );
 $oLogger->addLogWriter(
    'handler',
    new StdOutLogWriter
 );
 $oLogger->addLogWriter(
    'action',
    new StdOutLogWriter
 );
 $oLogger->addLogWriter(
    'errors',
    new StdOutLogWriter
 );
 $oLogger->addLogWriter(
    'exceptions',
    new StdOutLogWriter
 );

Registry::register( 'APF\core','Logger',$oLogger);



/*
 * LanguageManager
 */
LanguageManager::setLogger($oLogger);
LanguageManager::execute(
            ( $CnfClt->getSection('System')->getValue('Multilingual') === 'true' ? true : false ),
            ( $CnfClt->getSection('System')->getValue('Gettext') === 'true' ? true : false ) 
        );


/*
 * Database-Settings
 */
wsDB::setLogger($oLogger);
wsDB::initialize();
