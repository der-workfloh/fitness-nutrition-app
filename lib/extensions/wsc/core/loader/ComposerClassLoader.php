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
 * @file ComposerClassLoader.php
 * @namespace WSC\core\loader
 * 
 *
 * 
 * @author Florian Horn
 * @deprecated since version 1.0
 * @version
 * Version 1.0, 23.11.2013<br/>
 * 
 */

namespace WSC\core\loader;

require_once '../../../../../vendor/composer/ClassLoader.php';

class ComposerClassLoader extends Composer\Autoload\ClassLoader implements APF\core\loader\ClassLoader
{
    /**
     * Vendor name
     * @var string
     */
    protected $sVendorName = '';
    
    
    
    /**
     * Configuration root path
     * @var string
     */
    protected $sConfigurationRootPath = '';
    
    
    
    /**
     * Root path
     * @var string
     */
    protected $sRootPath = '';
    
    
    
    /**
     * Load class method
     * @param string $class
     */
    public function load($class) {
        return $this->loadClass($class);
    }
    
    
    
    /**
     * Get vendor name
     * @return string
     */
    public function getVendorName() {
        return $this->sVendorName;
    }
    
    
    
    /**
     * Get configuration root path
     * @return string
     */
    public function getConfigurationRootPath() {
        return $this->sConfigurationRootPath;
    }
    
    
    
    /**
     * Get root path
     * @return string
     */
    public function getRootPath() {
        return $this->sRootPath;
    }
}