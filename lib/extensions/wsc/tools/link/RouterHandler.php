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
 * @file RouterHandler.php
 * @namespace WSC\Tools\Link
 * 
 *
 *
 * @author Florian Horn
 * @version
 * Version 1.0, 14.12.2009<br/>
 * Version 1.1, 26.02.2011<br/>
 * Version 2.0, 07.11.2013<br/>
 *
 */

namespace WSC\Tools\Link;

use APF\Tools\Request\RequestHandler;
use WSC\Core\Factory\AbstractStatic;

class RouterHandler extends AbstractStatic
{
    const TEMPLATE_DEFAULT              = 'default';
    const LAYOUT_DEFAULT                = 'default';
    const SERVICE_DEFAULT               = 'template';
    
    private static $__sScheme           = '';
    private static $__sHost             = '';
    private static $__sPath             = '';
    private static $__aQuery            = null;
    private static $__aQueryOriginal    = null;
    private static $__sTemplate         = self::TEMPLATE_DEFAULT;
    private static $__sLayout           = self::LAYOUT_DEFAULT;
    private static $__sService          = self::SERVICE_DEFAULT;
    private static $__sURL;
    private static $__sDomain;
    private static $__bSecure;



    public static function initialize()
    {
        self::$__sURL = self::__parseURI();

        // -- Template
        self::$__sTemplate = RequestHandler::getValue('template', self::TEMPLATE_DEFAULT);
        // -- Layout
        self::$__sLayout = RequestHandler::getValue('layout', self::LAYOUT_DEFAULT);
        // -- Service
        self::$__sService = RequestHandler::getValue('service', self::SERVICE_DEFAULT);
    }



    /**
     * Adds a new value pair into the query part of the url
     * @param string $sKey
     * @param string $sValue
     */
    public static function addQueryValue($sKey, $sValue)
    {
        self::$__aQuery[$sKey] = $sValue;
    }

    
    
    /**
     * Removes a existing value pair from the query array
     * @param string $sKey
     * @return boolean
     */
    public static function removeQueryValue($sKey)
    {
        if (array_key_exists($sKey, self::$__aQuery)) {
            unset(self::$__aQuery[$sKey]);
            return true;
        }
        return false;
    }

    
    
    /**
     * Get service
     * @return string
     */
    public static function getService() 
    { 
        return self::$__sService; 
    }

    
    
    /**
     * Get layout
     * @return string
     */
    public static function getLayout() 
    { 
        return self::$__sLayout; 
    }

    
    
    /**
     * Get template
     * @return string
     */
    public static function getTemplate() 
    { 
        return self::$__sTemplate; 
    }

    
    
    /**
     * Get URL
     * @return string
     */
    public static function getUrl()
    {
        $sUrl = self::$__sDomain.self::$__sPath;
        if (self::$__aQueryOriginal !== null) {
            $sUrl .= '?';
            $sUrlTmp = '';
            foreach (self::$__aQuery as $k => $v) {
                $sUrlTmp .= '&'.$k.'='.$v;
            }
            $sUrl .= substr($sUrlTmp, 1, strlen($sUrlTmp)-1);
        }
        return $sUrl;
    }

    
    
    /**
     * Get manipulated URL
     * @return string
     */
    public static function getManipulatedUrl()
    {
        $sUrl = self::$__sDomain.self::$__sPath;
        if (self::$__aQuery !== null) {
            $sUrl .= '?';
            $sUrlTmp = '';
            foreach (self::$__aQuery as $k => $v) {
                $sUrlTmp .= '&'.$k.'='.$v;
            }
            $sUrl .= substr($sUrlTmp, 1, strlen($sUrlTmp)-1);
        }
        return $sUrl;
    }

    
    
    /**
     * Get base URL
     * @return string
     */
    public static function getBaseUrl() 
    { 
        return self::$__sDomain.self::$__sPath; 
    }

    
    
    /**
     * Get domain
     * @return string
     */
    public static function getDomain() 
    { 
        return self::$__sDomain; 
    }

    
    
    /**
     * Get scheme
     * @return string
     */
    public static function getScheme()  
    { 
        return self::$__sScheme;        
    } 

    
    
    /**
     * Get host
     * @return string
     */
    public static function getHost() 
    { 
        return self::$__sHost;         
    }

    
    
    /**
     * Get path
     * @return string
     */
    public static function getPath() 
    { 
        return self::$__sPath; 
    }

    
    
    /**
     * Get query
     * @return string
     */
    public static function getQuery() 
    { 
        return self::$__aQuery; 
    }
    
    
    
    /**
     * Gib an ob der Request über SSL läuft
     * @return boolean
     */
    public static function isSecure() 
    { 
        return self::$__bSecure;
    }


    
    /**
     * Zerlegt Domain und Anhang
     * @return string
     */
    private static function __parseURI()
    {        
        $isHTTPS = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on");
        self::$__bSecure = ( $isHTTPS ) ? true : false;
        $port = (isset($_SERVER["SERVER_PORT"]) && ((!$isHTTPS && $_SERVER["SERVER_PORT"] != "80") || ($isHTTPS && $_SERVER["SERVER_PORT"] != "443")));
        $port = ($port) ? ':'.$_SERVER["SERVER_PORT"] : '';
        $url = ($isHTTPS ? 'https://' : 'http://').$_SERVER["SERVER_NAME"].$port.$_SERVER["REQUEST_URI"];
        
        self::$__sDomain = ($isHTTPS ? 'https://' : 'http://').$_SERVER["SERVER_NAME"].$port;

        $aURL = parse_url($url);
        self::$__sPath = $aURL['path'];

        $aQry = (array_key_exists('query',$aURL)) ? explode('&',$aURL['query']) : array();
        foreach ($aQry as $value) {
            $a = explode('=',$value);
            if( count( $a ) != 2 ) {
                continue;
            }
            self::$__aQuery[$a[0]] = $a[1];
        }
        self::$__aQueryOriginal = self::$__aQuery;

        self::$__sHost = $aURL['host'];
        self::$__sScheme = $aURL['scheme'];
        
        return $url;
    }    
}
