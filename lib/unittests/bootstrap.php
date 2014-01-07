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
 * @file bootstrap.php
 * @namespace \
 * 
 * Unittest bootstrap
 * 
 * Start der Applikation für PHPUnit
 * 
 * @author Florian Horn
 * @version
 * Version 1.0, 09.11.2013<br/>
 * 
 */

$_SERVER['SERVER_PORT'] = '80';
$_SERVER['HTTP_HOST']   = 'localhost';
$_SERVER['REQUEST_URI'] = '';
define( "SYSTEM_START" , true );
define( 'UnittestDir' , dirname( __FILE__ ) );

$AppName = "unittest";

/*
 * Erste Ressourcen laden
 */

require_once('../lib/core/bootstrap.php');

use APF\core\loader\RootClassLoader;
use APF\core\loader\StandardClassLoader;
use APF\core\errorhandler\GlobalErrorHandler;
use APF\core\exceptionhandler\GlobalExceptionHandler;

RootClassLoader::addLoader(
        new StandardClassLoader(
                    'WSC', 
                    $apfClassLoaderRootPath.'/extensions/wsc', 
                    $apfClassLoaderRootPath.'/extensions/wsc'
                )
        );

RootClassLoader::addLoader(
        new StandardClassLoader(
                    'UNITTESTS', 
                    $apfClassLoaderRootPath.'/unittests', 
                    $apfClassLoaderRootPath.'/unittests'
                )
        );

GlobalErrorHandler::disable();
GlobalExceptionHandler::disable();

require_once(UnittestDir.'/systemconfig.php');
