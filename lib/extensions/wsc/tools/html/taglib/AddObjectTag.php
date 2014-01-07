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
 * @file AddObjectTag.php
 * @namespace WSC\tools\html\taglib
 * 
 * TAGLIB ADDOBJECT
 * 
 * 
 * @author Florian Horn
 * @version
 * Version 1.0, 03.03.2010<br/>
 * Version 2.0, 07.11.2013<br/>
 *
 */

namespace WSC\tools\html\taglib;

use APF\tools\request\RequestHandler;
use APF\tools\html\taglib\CreateDocumentFromFileTag;
use APF\core\logging\Logger;
use APF\core\registry\Registry;
use WSC\core\mainframe\Mainframe;
use WSC\core\language\LanguageManager;
use WSC\templates\FactoryTemplateManager;

class AddObjectTag extends CreateDocumentFromFileTag
{
    const TEMPLATE_SUB_DEFAULT  = 'content';
    
    protected $__sErrorPageName = '404';


    /**
     * Konstruktor
     * @return void
     */
    public function __construct()
    {
         parent::__construct();
    }


    
    /**
     * Get the content
     * @param string $pageName
     * @return string
     */
    protected function loadContent($pageName)
    {
        $sTemplateEngine    = RequestHandler::getValue('engine', Mainframe::TEMPLATE_ENGINE_DEFAULT);
        $bError             = false;
        
        /*
         * Manager laden
         */
        $oTmplManager = FactoryTemplateManager::factory('WSC\templates\\'. ucfirst($sTemplateEngine).'TemplateManager');
        $oTmplManager->setLogger(Registry::retrieve('APF\core','Logger'));

        /*
         * Fehlerseite definieren
         */
        if (array_key_exists('error', $this->attributes)) {
            $this->__sErrorPageName = $this->attributes['error'];
        }

        /*
         * Unterverzeichnis
         */

        // --- Bevorzugung von Requestanweisung
        // --- Wenn diese nicht existiert, wird der gewählte Wert genomment (Default oder Attribut Type)
        $Sub = RequestHandler::getValue(
                    'template_type', 
                    array_key_exists('type' , $this->attributes) ? $this->attributes['type'] : self::TEMPLATE_SUB_DEFAULT
                );

        
        /*
         * Seite
         */
        $sLangSection   = LanguageManager::isUsingGettext() ? null : LanguageManager::GetShortLocale().DS ;
        $pageName       = $sLangSection.strtolower($pageName);
        
        /*
         * File laden
         */
        $file = $oTmplManager->getFrontendDir().DS.$Sub.DS.$pageName.'.html';
        if (!file_exists($file) || $bError === true) {
            $file = $oTmplManager->getFrontendDir().DS.$Sub.DS.$sLangSection.$this->__sErrorPageName.'.html';
        }
        
        return file_get_contents($file);
    }
}