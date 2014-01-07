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
 * @file wsDateFormat.php
 * @namespace WSC\tools\datetime
 *
 * 
 *
 * @author Florian Horn
 * @version
 * Version 1.0, 22.09.2010<br/>
 * Version 2.0, 08.11.2013<br/>
 *
 */

namespace WSC\tools\datetime;

use APF\core\pagecontroller\APFObject;

class wsDateFormat extends APFObject
{
    // --- Date
    const FORMAT_DATE_DE = "d.m.Y";
    const FORMAT_DATE_EN = "m.d.Y";

    // --- DateTime Small
    const FORMAT_DATETIME_SMALL_DE = "d.m.Y H:i";
    const FORMAT_DATETIME_SMALL_EN = "m.d.Y H:i";
    
    // --- DateTime
    const FORMAT_DATETIME_DE = "d.m.Y H:i:s";
    const FORMAT_DATETIME_EN = "m.d.Y H:i:s";



    protected $__sDate          = self::FORMAT_DATE_DE;
    protected $__sDateTimeSmall = self::FORMAT_DATETIME_SMALL_DE;
    protected $__sDateTime      = self::FORMAT_DATETIME_DE;


    /**
     * Konstruktor
     */
    public function __construct() 
    {
        parent::__construct();
    }
    
    

    /**
     * Get date
     * @return string
     */
    public function getFormatDate() 
    { 
        return $this->__sDate; 
    }

    /**
     * Get datetime small
     * @return string
     */
    public function getFormatDateTimeSmall() 
    { 
        return $this->__sDateTimeSmall; 
    }

    /**
     * Get datetime
     * @return string
     */
    public function getFormatDateTime() 
    { 
        return $this->__sDateTime; 
    }


    /**
     * set format for date output<br/>
     * Possibilities:<br/>
     * <ul>
     *  <li>wsDateFormat::FORMAT_DATE_DE</li>
     *  <li>wsDateFormat::FORMAT_DATE_EN</li>
     * </ul>
     * @param string $sFormat
     */
    public function setFormatDate($sFormat = self::FORMAT_DATE_DE)
    {
        switch ($sFormat)
        {
            case self::FORMAT_DATE_DE: 
                $this->__sDate = self::FORMAT_DATE_DE; 
                break;
            case self::FORMAT_DATE_EN: 
                $this->__sDate = self::FORMAT_DATE_EN; 
                break;
            default:
                throw new \InvalidArgumentException( '['.get_class().'::setFormatDate()] Invalid Argument');
        }
    }

    

    /**
     * set format for datetime small output<br/>
     * Possibilities:<br/>
     * <ul>
     *  <li>wsDateFormat::FORMAT_DATETIME_SMALL_DE</li>
     *  <li>wsDateFormat::FORMAT_DATETIME_SMALL_EN</li>
     * </ul>
     * @param string $sFormat
     */
    public function setFormatDateTimeSmall($sFormat = self::FORMAT_DATETIME_SMALL_DE)
    {
        switch ($sFormat)
        {
            case self::FORMAT_DATETIME_SMALL_DE: 
                $this->__sDateTimeSmall = self::FORMAT_DATETIME_SMALL_DE; 
                break;
            case self::FORMAT_DATETIME_SMALL_EN: 
                $this->__sDateTimeSmall = self::FORMAT_DATETIME_SMALL_EN; 
                break;
            default:
                throw new \InvalidArgumentException( '['.get_class().'::setFormatDateTimeSmall()] Invalid Argument');
        }
    }


    
    /**
     * set format for datetime output<br/>
     * Possibilities:<br/>
     * <ul>
     *  <li>wsDateFormat::FORMAT_DATETIME_DE</li>
     *  <li>wsDateFormat::FORMAT_DATETIME_EN</li>
     * </ul>
     * @param string $sFormat
     */
    public function setFormatDateTime($sFormat = self::FORMAT_DATETIME_DE)
    {
        switch ($sFormat)
        {
            case self::FORMAT_DATETIME_DE: 
                $this->__sDateTime = self::FORMAT_DATETIME_DE; 
                break;
            case self::FORMAT_DATETIME_EN: 
                $this->__sDateTime = self::FORMAT_DATETIME_EN; 
                break;
            default:
                throw new \InvalidArgumentException( '['.get_class().'::setFormatDateTime()] Invalid Argument');
        }
    }
}
