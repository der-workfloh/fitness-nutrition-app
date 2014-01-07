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
 * @file wscInt.php
 *
 *
 *
 * @author Florian Horn
 * @namespace WSC\core\types
 *
 *
 *
 * @author Florian Horn
 * @version
 * Version 1.0, 25.08.2010<br/>
 * Version 2.0, 13.11.2013<br/>
 *
 */

namespace WSC\core\types;

use WSC\core\types\AbstractType;

class Integer extends AbstractType
{
    protected function __internalContaining( $Integer )
    {
        if (!\is_int($Integer)) {
            throw new \InvalidArgumentException(
                    '['.\get_class().'::__construct()] Parameter "Integer" is invalid argument type' );
        }

        $this->__default = $Integer;
    }
}
