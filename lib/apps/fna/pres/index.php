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
 * @file index.php
 * @namespace \
 * 
 * Bootstrap
 *
 * @author Florian Horn
 * @version
 * Version 1.0, 01.03.2010<br/>
 * Version 1.1, 26.10.2010<br/>
 * Version 2.0, 07.11.2013<br/>
 */


\ob_start();
\define("SYSTEM_START", true);

/*
 * Load essential ressources
 */
require_once( '../../../extensions/wsc/core/bootstrap.php');
require_once( '../../../../vendor/autoload.php');
use WSC\system\SystemConfigurator;
use WSC\core\mainframe\Mainframe;
use APF\core\loader\RootClassLoader;
use APF\core\loader\StandardClassLoader;

/*
 * Run the system configuration
 */
SystemConfigurator::run("fna");

/*
 * Add further libraries
 */
/*
// --- Propel
RootClassLoader::addLoader(
        new StandardClassLoader(
                    'Propel', 
                    '../../../../vendor/propel/propel/src/Propel'
                )
        );
// --- Symfony
RootClassLoader::addLoader(
        new StandardClassLoader(
                    'Symfony', 
                    '../../../../vendor/symfony'
                )
        );
*/

/*
 * Start der Applikation
 */
$sOutput = Mainframe::render();

print($sOutput);

\ob_end_flush();
