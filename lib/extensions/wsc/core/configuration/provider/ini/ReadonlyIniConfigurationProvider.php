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
 * @file ReadonlyIniConfigurationProvider.php
 * @namespace WSC\core\configuration\provider\ini
 *
 *
 *
 * @author Florian Horn
 * @version
 * Version 1.0, 10.10.2010<br/>
 * Version 2.0, 07.11.2013<br/>
 *
 */

namespace WSC\core\configuration\provider\ini;

use APF\core\configuration\Configuration;
use APF\core\configuration\ConfigurationProvider;
use APF\core\configuration\provider\ini\IniConfigurationProvider;

class ReadonlyIniConfigurationProvider extends IniConfigurationProvider implements ConfigurationProvider
{
    public function saveConfiguration($namespace, $context, $language, $environment, $name, Configuration $config)
    {
        throw new ConfigurationException('['.get_class().'::saveConfiguration()] The saveConfiguration()-method is not allow. The configuration is readonly!');
    }
}
