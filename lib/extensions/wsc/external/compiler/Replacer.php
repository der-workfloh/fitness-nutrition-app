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
 * @file Replacer.php
 * @namespace WSC\external\compiler
 *
 *
 * @author Florian Horn
 * @version
 * Version 1.0, 21.10.2010<br/>
 * Version 1.1, 26.02.2011<br/>
 * Version 1.2, 25.08.2011<br/>
 * Version 2.0, 03.11.2013<br/>
 *
 */

namespace WSC\external\compiler;

use WSC\external\compiler\AbstractBuilder;

class Replacer extends AbstractBuilder
{
    const PATTERN_REPLACEMENT_MATCH     = "/\*\*\*[\ ]*RPLC[\ ]*\(([A-Za-z0-9\_]+)\)[\ ]*\*/";
    const PATTERN_REPLACEMENT_REPLACE   = "/\*\*\*[\ ]*RPLC[\ ]*\(%s)\)[\ ]*\*/";

    protected $__aReplacementStatements = array();



    /**
     * Set statements
     * @param array $aReplacementStatements 
     */
    public function setStatements(array $aReplacementStatements = array()) 
    { 
        $this->__aReplacementStatements = $aReplacementStatements; 
    }



    /**
     * Start replacing provess
     * @param string $sContent
     * @return string
     */
    public function replace($sContent)
    {
        $aMatches = array();
        if (preg_match_all("?".self::PATTERN_REPLACEMENT_MATCH."?imsU", $sContent, $aMatches, PREG_SET_ORDER)) {
            foreach ($aMatches as $a) {
                if (!array_key_exists($a[1], $this->__aReplacementStatements)) {
                    continue;
                }
                
                $sRplc = $this->__aReplacementStatements[$a[1]];
                $sRplcPattern = sprintf(
                            "?".self::PATTERN_REPLACEMENT_REPLACE."?imsU",
                            $a[1]
                        );
                $sContent = preg_replace($sRplcPattern, $sRplc, $sContent);
            }
        }
        return $sContent;
    }
}
