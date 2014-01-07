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
 * @file mainframe.php
 * @namespace WSC\core\mainframe
 * 
 *
 * 
 * @author Florian Horn
 * @version
 * Version 1.0, 01.03.2010<br/>
 * Version 1.1, 26.10.2010<br/>
 * Version 1.2, 26.02.2011<br/>
 * Version 1.3, 15.09.2011<br/>
 * Version 1.4, 30.10.2013<br/>
 * Version 2.0, 07.11.2013<br/>
 * 
 */

namespace WSC\core\mainframe;

use APF\core\singleton\Singleton;
use APF\core\logging\Logger;
use APF\core\logging\LogEntry;
use APF\core\logging\Writer\FileLogWriter;
use APF\core\configuration\ConfigurationManager;
use APF\core\configuration\Provider\Ini\IniConfigurationProvider;
use APF\core\registry\Registry;
use APF\tools\request\RequestHandler;
use WSC\tools\link\RouterHandler;
use WSC\templates\FactoryTemplateManager;
use WSC\core\configuration\provider\ini\ReadonlyIniConfigurationProvider;
use WSC\core\language\LanguageManager;
use WSC\core\factory\AbstractStatic;

class Mainframe extends AbstractStatic
{
    const HTTP_COMPRESSION_OFF              = 'none';
    const HTTP_COMPRESSION_GZIP             = 'gzip';
    const HTTP_COMPRESSION_XGZIP            = 'x-gzip';
    const HTTP_ENCODING_NORMAL              = 'none';
    const HTTP_ENCODING_GZIP                = 'gzip';
    const HTTP_ENCODING_XGZIP               = 'x-gzip';
    const TEMPLATE_ENGINE_DEFAULT           = 'default';

    
    
    /**
     * Output string
     * @var string
     */
    protected static $__sOutput = '';
    
    
    
    /**
     * HTTP compression method
     * @var string
     */
    protected static $__sUsedCompression = self::HTTP_COMPRESSION_OFF;
    
    
    
    /**
     * Encoding method
     * @var string
     */
    protected static $__sEncoding = self::HTTP_ENCODING_NORMAL;
    


    /**
     * Rendert die erhaltenen Informationen und gibt sie aus
     * @return string
     */
    public static function render()
    {
        self::__configuration();
        self::__process();
        self::__parseEncoding();

        // --- Gib den Output zurück
        return self::$__sOutput;
    }



    /**
     * Get the encoding type
     * @return string
     */
    public static function getEncoding()
    {
        return self::$__sEncoding;
    }
    
    
    
    /**
     * Get http compression mode
     * @return string
     */
    public static function getHTTPCompression()
    {
        return self::$__sUsedCompression;
    }
    
    
    
    /**
     * Process configuration
     */
    protected static function __configuration()
    {        
        self::setContext(Registry::retrieve('APF\core','Environment'));
        self::setLanguage(LanguageManager::getShortLocale());
        
        
        /*
         * Konfiguration-Provider registrieren
         */
        $provLang = new IniConfigurationProvider();
        $provLang->setOmitContext(false);
        $provLang->setOmitEnvironment(true);
        ConfigurationManager::registerProvider('lini', $provLang);
        ConfigurationManager::registerProvider('rini', new ReadonlyIniConfigurationProvider());

        $provOrm = new IniConfigurationProvider();
        $provOrm->setOmitContext(false);
        $provOrm->setOmitEnvironment(true);
        ConfigurationManager::registerProvider('orm', $provOrm);
        


        /*
         * Configuration
         */
        $CnfSys = ConfigurationManager::loadConfiguration(
                    'WSC\System',
                    NULL,
                    NULL,
                    Registry::retrieve('APF\core','Environment'),
                    'config.ini'
                );
        $CnfClt = self::getConfiguration('APF\apps', 'config.ini');
        

        
        /*
         * Registry
         */
        $sSysPaths = $CnfSys->getSection('Paths')->getValue('Docroot').$CnfSys->getSection('Paths')->getValue('Sysbind');
        Registry::register('APF\core', 'SystemPath', $sSysPaths);

        // --- Log
        Registry::register('APF\core', 'LogPath', $sSysPaths.'/logs');
        Registry::register('APF\core', 'LogDir' , Registry::retrieve('APF\core', 'LogPath'));

        // --- Session
        Registry::register('APF\core', 'SessionTime', (int)$CnfClt->getSection('Session')->getValue('Duration'));

        // --- Cookies
        Registry::register('APF\core', 'CookieTime', time() + (int)$CnfClt->getSection('Cookies')->getValue('Duration'));

        // --- SSL
        Registry::register('APF\core', 'UseSSL', ( $CnfClt->getSection('SSL')->getValue('Active') === 'true') ? true : false);
        

        /*
         * Logging
         */
        $oLogger = Singleton::getInstance('APF\core\logging\Logger');        
        $oLogger->addLogWriter(
           'stdout',
           new FileLogWriter(Registry::retrieve('APF\core', 'LogPath'))
        );
        $oLogger->addLogWriter(
           'language',
           new FileLogWriter(Registry::retrieve('APF\core', 'LogPath'))
        );
        $oLogger->addLogWriter(
           'template',
           new FileLogWriter(Registry::retrieve('APF\core', 'LogPath'))
        );
        $oLogger->addLogWriter(
           'handler',
           new FileLogWriter(Registry::retrieve('APF\core', 'LogPath'))
        );
        $oLogger->addLogWriter(
           'action',
           new FileLogWriter(Registry::retrieve('APF\core', 'LogPath'))
        );
        $oLogger->addLogWriter(
           'errors',
           new FileLogWriter(Registry::retrieve('APF\core', 'LogPath'))
        );
        $oLogger->addLogWriter(
           'exceptions',
           new FileLogWriter(Registry::retrieve('APF\core', 'LogPath'))
        );
        
        Registry::register( 'APF\core','Logger',$oLogger);
        


        /*
         * LanguageManager
         */
        LanguageManager::setLogger($oLogger);
        LanguageManager::execute( 
                    $CnfClt->getSection('System')->getValue('Multilingual') === 'true' ? true : false,
                    $CnfClt->getSection('System')->getValue('Gettext') === 'true' ? true : false
                );

        
        
        /*
         * URLRewriting
         */
        Registry::register(
                    'APF\core', 
                    'URLRewriting', 
                    $CnfClt->getSection('System')->getValue('URLRewrite') === 'true' ? true : false
                ); 
        
        
        
        /*
         * RouterHandler
         */
        RouterHandler::initialize();
        
        /*
         * Session-Konfiguration
         */
        \session_set_cookie_params(Registry::retrieve('APF\core', 'SessionTime'));
        \session_cache_expire(Registry::retrieve('APF\core', 'SessionTime')); 
        \ini_set('session.gc_maxlifetime', (int)Registry::retrieve('APF\core', 'SessionTime'));
        \session_cache_limiter('nocache');
    }



    /**
     * Process state
     * @return int
     */
    protected static function __process()
    {
        /*
         * TemplateManager
         */
        // --- Enginewahl (Default, Cronjob, Ajax, ... )
        $sTemplateEngine= RequestHandler::getValue('engine' , self::TEMPLATE_ENGINE_DEFAULT);
        
        // --- Manager laden
        $oLogger = Registry::retrieve('APF\core', 'Logger');
        FactoryTemplateManager::setLogger($oLogger);
        $oTmplManager = FactoryTemplateManager::factory(
                    'WSC\templates\\'.\ucfirst($sTemplateEngine).'TemplateManager'
                );

        // --- Fehler beim Erstellen?
        if (FactoryTemplateManager::hasError() === true) {
            $oLogger->logEntry('template', FactoryTemplateManager::getError(), LogEntry::SEVERITY_ERROR);
            return -1;
        }
        
        // --- Logger setzen
        $oTmplManager->setLogger($oLogger);
        
        // --- TemplateManager ausfuehren
        $oTmplManager->execute();

        self::$__sOutput = $oTmplManager->transform();

        return 0;
    }
    
    

    /**
     * Komprimierung (Encodierung)
     * @return bool
     */
    protected static function __parseEncoding()
    {
        // --- Browser-Kompression verfügbar?
        $HAE = array_key_exists('HTTP_ACCEPT_ENCODING', $_SERVER) ? $_SERVER['HTTP_ACCEPT_ENCODING'] : false;

        if (\headers_sent()) {
            self::$__sEncoding = self::HTTP_ENCODING_NORMAL;
        } else if (\strpos($HAE, self::HTTP_ENCODING_XGZIP) !== false) {
            self::$__sEncoding          = self::HTTP_ENCODING_XGZIP;
            self::$__sUsedCompression   = self::HTTP_COMPRESSION_XGZIP;
        } else if (\strpos($HAE, self::HTTP_ENCODING_GZIP) !== false) {
            self::$__sEncoding          = self::HTTP_ENCODING_GZIP;
            self::$__sUsedCompression   = self::HTTP_COMPRESSION_GZIP;
        } else {
            self::$__sEncoding = self::HTTP_ENCODING_NORMAL;
        }
    }
}