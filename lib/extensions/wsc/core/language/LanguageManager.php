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
 * @file LanguageManager.php
 * @namespace WSC\core\language
 * 
 * LANGUAGE MANAGER
 *
 * Manager für die gewählte Sprache
 *
 * @author Florian Horn
 * @version
 * Version 1.0, 04.03.2010<br/>
 * Version 1.1, 28.09.2010<br/>
 * Version 1.2, 09.01.2012<br/>
 * Version 1.3, 30.10.2013<br/>
 * Version 2.0, 07.11.2013<br/>
 *
 */

namespace WSC\core\language;

use APF\tools\cookie\Cookie;
use APF\tools\request\RequestHandler;
use APF\tools\validator\Validator;
use APF\tools\link\Url;
use APF\core\configuration\ConfigurationManager;
use APF\core\registry\Registry;
use WSC\core\factory\AbstractStatic;

class LanguageManager extends AbstractStatic
{
    const LOCALE_DEFINED    = 0;
    const LOCALE_UNDEFINED  = -1;
    
    protected static $__iLocale             = 0;
    protected static $__aLocalesAvaiable    = array();
    protected static $__bUseGettext         = false;
    protected static $__bIsInitialized      = false;


    /**
     * Setzt die Language
     * @return int
     */
    public static function execute($bMultiLang = false, $bGettext = false)
    {
        /*
         * Language Config laden
         */
        $CnfHd = ConfigurationManager::loadConfiguration(
                    'APF\apps', 
                    Registry::retrieve('APF\core','App'), 
                    NULL, 
                    Registry::retrieve('APF\core','Environment'), 
                    'config.ini'
                );
        $aLang = $CnfHd->getSection('Languages')->getValueNames();




        /*
         * Mehrere Sprachen nutzen?
         */
        if ($bMultiLang === false) {
            self::$__aLocalesAvaiable[0] = $aLang[0];
        } else {
            foreach ($aLang as $lg) {
                $id = $CnfHd->getSection('Languages')->getValue($lg);
                self::$__aLocalesAvaiable[(int)$id ] = $lg;
            }
        }


        /*
         * Cookie für Sprachwahl setzen
         */
        $cM = new Cookie(
                    'iLocale',
                    Registry::retrieve('APF\core', 'CookieTime'),
                    Url::fromCurrent()->getHost(),
                    $CnfHd->getSection('Client')->getValue('Systemname')
                );

        // --- Pruefen ob ein Request auf Sprachwechsel vorliegt
        if (RequestHandler::getValue('language') !== null &&
            self::__locale_exists(RequestHandler::getValue('language'))) {
            $cM->setValue(self::getLocaleId( RequestHandler::getValue('language')));
        }

        // --- Pruefen ob Cookies gesetzt wurden, oder erst erstellt werden muessen
        // --- Falls existiert, den Wert uebernehmen
        if ($cM->getValue('iLocale') === null ||
            !array_key_exists( $cM->getValue('iLocale'), self::$__aLocalesAvaiable)) {
            $cM->setValue(0);
            self::$__iLocale = 0;
        } else {
            self::$__iLocale = $cM->getValue('iLocale');
        }



        /*
         * Gettext initialisieren
         */
        if ($bGettext) {
            self::__initializeGettext();
            self::$__bUseGettext = true;
        }

        self::$__bIsInitialized = true;

        return 0;
    }

    

    /**
     * Check if is initialized
     * @return bool
     */
    public static function isInitialized() 
    { 
        return self::$__bIsInitialized; 
    }

    
    
    /**
     * Get full locale
     * @return string
     */
    public static function getLocale()
    { 
        return self::$__aLocalesAvaiable[self::$__iLocale]; 
    }

    
    
    /**
     * Get first to locale letters
     * @return string
     */
    public static function getShortLocale()
    {
        if (empty(self::$__aLocalesAvaiable)) {
            return '';
        }
        return substr(self::$__aLocalesAvaiable[self::$__iLocale], 0, 2);
    }

    
    
    /**
     * Get locale id if exists
     * @param string $sLocale
     * @return int
     */
    public static function getLocaleId($sLocale)
    {
        foreach(self::$__aLocalesAvaiable as $id => $l) {
            if($l === $sLocale) {
                return (int)$id;
            }
        }
        return (int)self::LOCALE_UNDEFINED;
    }

    
    
    /**
     * Aktuelle Sprache aendern
     * @param string $sLocale
     * @return int
     */
    public static function setLocale( $sLocale )
    {
        $CnfHd = ConfigurationManager::loadConfiguration(
                    'APF\apps', 
                    Registry::retrieve('APF\core','App'), 
                    NULL, 
                    Registry::retrieve('APF\core','Environment'), 
                    'config.ini'
                );
        
       $cM = new Cookie(
                    'iLocale',
                    Registry::retrieve('APF\core', 'CookieTime'),
                    Url::fromCurrent()->getHost(),
                    $CnfHd->getSection('Client')->getValue('Systemname')
                );
       
        if ((self::$__iLocale = self::getLocaleId($sLocale)) === self::NO_LOCALE_DEFINED ) {
            $cM->setValue(0);
            self::$__iLocale = 0;
            return self::LOCALE_UNDEFINED;
        }

        $cM->setValue(self::$__iLocale);
        return self::LOCALE_DEFINED;
    }


    
    /**
     * Checks if gettext is used
     * @return boolean
     */
    public static function isUsingGettext()
    { 
        return self::$__bUseGettext; 
    }



    /**
     * Prüft ob der erste Parameter ein gültiger Locale ist.
     * @param string $sLocale
     * @return bool
     */
    public static function isValid($sLocale)
    {
        if (!Validator::validateText($sLocale)) {
            throw new InvalidArgumentException('['.get_class().'::isValid()] Invalid Argument');
        }
        return self::__locale_exists($sLocale);
    }



    /**
     * Prueft ob der Locale zur Auswahl steht
     * @param string $sTag
     * @return boolean
     */
    private static function __locale_exists($sTag)
    {
        foreach (self::$__aLocalesAvaiable as $id => $l) {
            if ($l === $sTag) {
                return true;
            }
        }
        return false;
    }


    
    /**
     * Initialize gettext
     */
    private static function __initializeGettext()
    {
        /*
         * Locale-Ini
         */
        $CnfHd  = ConfigurationManager::loadConfiguration('language', Registry::retrieve('APF\core', 'App'), null , null, 'locale.lini');
        $locale = $CnfHd->getSection('Locales')->getValue(self::$__aLocalesAvaiable[self::$__iLocale]);

        // --- Workaround Windows for LC_MESSAGES
        if (!defined('LC_MESSAGES')) {
            define('LC_MESSAGES', 6); 
        }

        $domain         = "messages";   // setzt die Domäne
        $encoding       = 'UTF-8';      // setzt die Zeichenkodierung
        $localeDir      = Registry::retrieve('APF\core', 'SystemPath').DS."locale";
        $lcMessagesDir  = $localeDir.DS.self::$__aLocalesAvaiable[self::$__iLocale].DS.'LC_MESSAGES';

        /*
         * Validierung
         */
        if (!is_dir($lcMessagesDir)) {
            self::$__oLogger->logEntry(
                        sprintf('Locale-Directory %s does noet exist!', $lcMessagesDir),
                        'language' 
                    );
        }

        /*
         * Enviroment
         */
        if (putenv("LANGUAGE=$locale") === false) {
            self::$__oLogger->logEntry(
                        sprintf('Could not set the ENV variable LANGUAGE = ', $locale),
                        'language' 
                    );
        }

        if (putenv("LANG=$locale") === false) {
            self::$__oLogger->logEntry(
                        sprintf( 'Could not set the ENV variable LANG = ' , $locale ),
                        'language' 
                    );
        }


        /*
         * SetLocale
         */
        if (!defined('LANG')) {
            define('LANG', $locale);
        }
        $locale_set = setlocale(LC_ALL, $locale);
        
        if( $locale_set != $locale ) {
            self::$__oLogger->logEntry(
                        sprintf("Tried: setlocale, tried to set '%s', but instead '%s' returns",
                                $locale, $locale_set),
                        'language'
                    );
        }

        /*
         * Domain
         */
        $bindtextdomain_set = bindtextdomain($domain , $localeDir.DS);
        if (empty($bindtextdomain_set))  {
            self::$__oLogger->logEntry(
                        sprintf("Tried: bindtextdomain, '%s', to directory, '%s', but received '%s'",
                                $domain, $localeDir.DS, $bindtextdomain_set),
                        'language' 
                    );
        }

        bind_textdomain_codeset($domain, $encoding);
        $textdomain_set = textdomain($domain);
        if (empty($textdomain_set)) {
            self::$__oLogger->logEntry(
                        sprintf("Tried: set textdomain to '%s', but got '%s'",
                                $domain, $textdomain_set),
                        'language' 
                    );
        }
    }

}
