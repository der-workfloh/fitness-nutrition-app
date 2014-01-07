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
 * @file AbstractTemplateManager.php
 * @namespace WSC\templates
 *
 * Regelt den Art des Aufrufes (Direkt, AJAX, ....) und die Weiterverarbeitung
 * ( MVC-Zelle, Statische HTML-Seite, ... )
 *
 * @author Florian Horn
 * @version
 * Version 1.0, 04.03.2010<br/>
 * Version 1.1, 26.02.2011<br/>
 * Version 1.2, 15.07.2011<br/>
 * Version 1.3, 09.01.2012<br/>
 * Version 2.0, 07.11.2013<br/>
 *
 */

namespace WSC\templates;

use APF\core\pagecontroller\APFObject;
use APF\core\registry\Registry;
use APF\core\logging\Logger;
use APF\tools\cookie\Cookie;
use APF\tools\link\Url;
use WSC\core\language\LanguageManager;

abstract class AbstractTemplateManager extends APFObject
{
    protected $__sOutput                    = '';
    protected $__sPresentationFrontendDir   = '';
    protected $__sPresentationModuleDir     = '';
    protected $__sTemplateRootAbs           = '';
    protected $__sTemplateRootRel           = '';
    protected $__sDesignNamespace           = 'default';
    protected $__sDesignName                = 'default';
    protected $__oLogger                    = null;
    
    /**
     * Initialisiert den Template Manager
     * @return void
     */
    public function __construct()
    {
        $this->setContext(Registry::retrieve('APF\core', 'App'));
        $this->setLanguage(LanguageManager::getShortLocale());
        
        

        /*
         * Configuration auslesen
         */
        $CnfHd          = $this->getConfiguration('APF\apps', 'config.ini');
        $iCookieTime    = Registry::retrieve('APF\core', 'CookieTime');
        $sClient        = $CnfHd->getSection('Client')->getValue('Systemname');

        /*
         * Cookies checken
         */
        $cMTemplate = new Cookie(
                    'template',
                    Registry::retrieve('APF\core', 'CookieTime'),
                    Url::fromCurrent()->getHost(),
                    $sClient
                );
        $cMLayout = new Cookie(
                    'template',
                    Registry::retrieve('APF\core', 'CookieTime'),
                    Url::fromCurrent()->getHost(),
                    $sClient
                );
        
        // --- Template
        if (Registry::retrieve('APF\core','Template',null) !== null) {
            $sTmpl = Registry::retrieve('APF\core','Template');
        } else {
            if($cMTemplate->getValue() !== null) {
                $sTmpl = $cMTemplate->getValue();
            } else {
                $sTmpl = $CnfHd->getSection('Defaults')->getValue('Template');
            }
        }
        
        // --- Layout
        if (Registry::retrieve('APF\core','Layout',null) !== null) {
            $sLayout = Registry::retrieve('APF\core','Layout');
        } else {
            if($cMLayout->getValue() !== null) {
                $sLayout = $cMLayout->getValue();
            } else {
                $sLayout = $CnfHd->getSection('Defaults')->getValue('Layout');
            }
        }
        
        $cMTemplate->setValue($sTmpl);
        $cMLayout->setValue($sLayout);
        
        /*
         * Direktiven
         */
        $this->__sTemplateRootAbs =
                Registry::retrieve('APF\core', 'SystemPath').DS.'lib'.DS.
                'apps'.DS.$sClient.DS.'pres';
        $this->__sTemplateRootRel           = 'templates'.DS.strtolower($sTmpl);
        $this->__sPresentationFrontendDir   = $this->__sTemplateRootAbs.DS.$this->__sTemplateRootRel.DS.'frontend';
        $this->__sDesignNamespace           = 'APF\apps\\'.strtolower($sClient).'\pres\templates\\'.strtolower($sTmpl);
        $this->__sDesignName                = strtolower($sLayout);
    }
    private function __clone(){}

    
    
    /**
     * 
     * @param Logger $oLogger
     */
    public function setLogger( $oLogger ) 
    { 
        $this->__oLogger = $oLogger; 
    }
    
    
    
    /**
     * Führt den gewählten Template Service aus
     * @return int
     */
    abstract public function execute();

    
    
    /**
     * Transform content
     * @return string
     */
    public function transform() 
    { 
        return $this->__sOutput; 
    }

    
    
    /**
     * Get the absolute template path
     * @return string
     */
    public function getAbsoluteTemplateRoot() 
    { 
        return $this->__sTemplateRootAbs; 
    }

    
    
    /**
     * Get the relative template path
     * @return string
     */
    public function getRelativeTemplateRoot() 
    { 
        return $this->__sTemplateRootRel; 
    }

    
    
    /**
     * Get the frontend path
     * @return string
     */
    public function getFrontendDir() 
    { 
        return $this->__sPresentationFrontendDir;
    }
}