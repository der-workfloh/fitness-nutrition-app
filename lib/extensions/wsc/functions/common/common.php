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
 * @file common.php
 * @namespace WSC\functions\common
 *
 * Common functions
 *
 * @author Florian Horn
 * @version
 * Version 1.0, 26.02.2011<br/>
 * Version 2.0, 06.11.2013<br/>
 *
 */

namespace WSC\functions\common;

/**
 * Unique for multidimensional arrays
 * @param array $aRes
 * @return array
 */
function array_multiunique( $Array )
{
    foreach ($Array as &$r) $r=\serialize($r);
    $Array = array_unique( $Array );
    foreach ($Array as &$r) $r=\unserialize($r);
    return $Array;
}


/**
 * Sorts an Array by a key of his subarray
 * @param array $array
 * @param string $subkey
 * @param bool $sort_ascending
 * @return int
 */
function sksort(&$array, $subkey="id", $sort_ascending=false) 
{
    $temp_array = array();

    if (count($array))
    {
        $temp_array[key($array)] = array_shift($array);
    }

    foreach($array as $key => $val)
    {
        $offset = 0;
        $found = false;
        foreach($temp_array as $tmp_key => $tmp_val)
        {
            if(!$found and strtolower($val[$subkey]) > strtolower($tmp_val[$subkey]))
            {
                $temp_array = array_merge( 
                        (array)array_slice($temp_array,0,$offset),
                        array($key => $val),
                        array_slice($temp_array,$offset) );
                $found = true;
            }
            $offset++;
        }
        if(!$found) $temp_array = array_merge($temp_array, array($key => $val));
    }

    $array = $sort_ascending ? array_reverse($temp_array) : $temp_array;
    
    return 0;
}




/**
 * Generates a new random password.
 * @param int $iLength
 * @param bool $bSimple
 * @return string
 */
function generatePassword( $iLength = 8 , $bSimple = false )
{
    mt_srand( round( microtime(true) ) );

    // Alle erlaubten Zeichen in einem Passwort
    $aDigitsSimple = array( 'a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z',
        'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z',
        0,1,2,3,4,5,6,7,8,9);
    $aDigitsFull = array_merge(
        $aDigitsSimple,
        array( 'ä','ö','ü','Ä','Ö','Ü','ß','@','.','(',')','[',']','&','?','!','$','%' ) );

    $aDigits = $bSimple === true ? $aDigitsSimple : $aDigitsFull;

    $iDigits = count( $aDigits );
    $sPassword = '';

    for( $i = 0; $i < $iLength; $i++ )
    {
        $sPassword .= $aDigits[  mt_rand( 0 , mt_getrandmax() ) % $iDigits ];
    }

    return $sPassword;
}




/**
 * Encrypts a plain-text password
 * @param string $sPassword
 * @return string
 */
function encryptPassword( $sPassword )
{
    return sha1( md5( $sPassword ) );
}




/**
 * Converts HTML-Break-Tag to nl
 * @param string $text
 * @return string
 */
function br2nl( $sText )
{
    $sText = preg_replace('/<br\\\\s*?\\/?/i', "\\n", $sText);
    return str_replace("<br/>","",$sText);
}



/**
 * Compares two strings through their lengths
 * @param string $a
 * @param string $b
 * @return bool
 */
function strlenCmp( $a , $b )
{
    if( $a !== (string)$a || $b !== (string)$b ) return NULL;

    if ( strlen($a) == strlen($b) ) return 0;
    else return strlen($a) < strlen($b) ? -1 : 1;
}

?>