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
 * @file wsDate.php
 * @namespace WSC\tools\datetime
 *
 *
 *
 * @author Florian Horn
 * @version
 * Version 1.0, 22.03.2009<br/>
 * Version 1.1, 26.02.2011<br/>
 * Version 1.2, 21.03.2011<br/>
 * Version 1.3, 22.04.2011<br/>
 * Version 1.4, 03.08.2011<br/>
 * Version 1.5, 24.10.2011<br/>
 * Version 2.0, 08.11.2013<br/>
 *
 */

namespace WSC\tools\datetime;

use WSC\tools\datetime\wsDateFormat;
use APF\core\pagecontroller\APFObject;

class wsDate extends APFObject
{
    protected $__oFormat      = null;
    protected $__sYear        = '0000';
    protected $__sMonth       = '00';
    protected $__sDay         = '00';
    protected $__sHour        = '00';
    protected $__sMinute      = '00';
    protected $__sSecond      = '00';
    protected $__aMonthNames  = array(
        "01"    =>  "Jan",
        "02"    =>  "Feb",
        "03"    =>  "Mar",
        "04"    =>  "Apr",
        "05"    =>  "May",
        "06"    =>  "Jun",
        "07"    =>  "Jul",
        "08"    =>  "Aug",
        "09"    =>  "Sep",
        "10"    =>  "Oct",
        "11"    =>  "Nov",
        "12"    =>  "Dec",
    );


    /**
     * Konstruktor
     * @param timestamp $dDate
     * @return bool
     */
    public function __construct($dDate = null)
    {
        $this->__oFormat = new wsDateFormat();

        if ($dDate === null) { 
            $this->__setToNow(); 
            return true;             
        }

        $this->__sYear      = substr($dDate, 0, 4);
        $this->__sMonth     = substr($dDate, 5, 2);
        $this->__sDay       = substr($dDate, 8, 2);
        $this->__sHour      = substr($dDate, 11, 2);
        $this->__sMinute    = substr($dDate, 14, 2);
        $this->__sSecond    = substr($dDate, 17, 2);

        return $this;
    }
    
    
    
    /**
     * Loading the date from a url prepared date value
     * @param string $sDate 
     */
    public function loadFromURL($sDate)
    {
        $aDate = explode('.', $sDate);
        $this->__sYear      = $aDate[0];
        $this->__sMonth     = $aDate[1];
        $this->__sDay       = $aDate[2];
        $this->__sHour      = $aDate[3];
        $this->__sMinute    = $aDate[4];
        $this->__sSecond    = $aDate[5];
    }
    
    
    
    /**
     * Cloning
     */
    public function __clone() 
    {
        $oFormat    = $this->__oFormat;
        $sYear      = $this->__sYear;
        $sMonth     = $this->__sMonth;
        $sDay       = $this->__sDay;
        $sHour      = $this->__sHour;
        $sMinute    = $this->__sMinute;
        $sSecond    = $this->__sSecond;
        
        $this->__oFormat    = $oFormat;
        $this->__sYear      = $sYear;
        $this->__sMonth     = $sMonth;
        $this->__sDay       = $sDay;
        $this->__sHour      = $sHour;
        $this->__sMinute    = $sMinute;
        $this->__sSecond    = $sSecond;
    }



    /**
     * Changes the format of an timestamp
     * @param wsDate $wsDate
     * @param string $sFormat
     * @return string
     */
    public static function changeFormat(wsDate $wsDate, $sFormat = wsDateFormat::FORMAT_DATETIME_DE)
    {
         $sDate = date($sFormat, strtotime($wsDate->toTimestamp()));
         return $sDate;
    }


    
    /**
     * Compares two dates if they are equal
     * @param wsDate $Date
     * @return boolean
     */
    public function equalTo(wsDate $Date)
    {
        $dt1    = new DateTime($this->__toTimestamp());
        $dt2    = new DateTime($Date->toTimestamp());
        return ($dt1 == $dt2);
    }

    
    
    /**
     * Compares this date if is older then $Date
     * @param wsDate $Date
     * @return boolean
     */
    public function olderThen(wsDate $Date)
    {
        $dt1    = new DateTime($this->__toTimestamp());
        $dt2    = new DateTime($Date->toTimestamp());
        return ($dt1 < $dt2);
    }

    
    
    /**
     * Compares this date if is newer then $Date
     * @param wsDate $Date
     * @return boolean
     */
    public function newerThen(wsDate $Date)
    {
        $dt1    = new DateTime($this->__toTimestamp());
        $dt2    = new DateTime($Date->toTimestamp());
        return ($dt1 > $dt2);
    }
    
    
    
    /**
     * Get the difference between two dates
     * @param wsDate $Date 
     * @return wsDate
     */
    public function diff(wsDate $Date)
    {
        $date1 = new DateTime($this->toTimestamp());
        $date2 = new DateTime($Date->toTimestamp());
        $sDiffYear = sprintf('%04d', $date1->diff($date2)->format('%Y'));
        $dReturn = new wsDate( $sDiffYear.'-'.$date1->diff($date2)->format('%M-%D %H:%I:%S') );
        return $dReturn;
    }

    

    /**
     * Check if date is valid
     * @return boolean
     */
    public function isValid() 
    { 
        return \checkdate((int)$this->__sMonth, (int)$this->__sDay, (int)$this->__sYear);
    }

    
    
    /**
     * Get year
     * @return string
     */
    public function getYear() 
    {
        return $this->__sYear; 
    }

    
    
    /**
     * Get month
     * @return string
     */
    public function getMonth() 
    { 
        return $this->__sMonth; 
    }

    
    
    /**
     * Get week
     * @return int
     */
    public function getWeek() 
    { 
        return (int)date('W',$this->__toDate()); 
    }

    
    
    /**
     * Get day
     * @return string
     */
    public function getDay() 
    { 
        return $this->__sDay; 
    }

    
    
    /**
     * Get hour
     * @return string
     */
    public function getHour() 
    { 
        return $this->__sHour; 
    }

    
    
    /**
     * Get minute
     * @return string
     */
    public function getMinute() 
    { 
        return $this->__sMinute; 
    }

    
    
    /**
     * Get second
     * @return string
     */
    public function getSecond() 
    { 
        return $this->__sSecond; 
    }

    
    
    /**
     * Get date as timestamp
     * @return string
     */
    public function toTimestamp()
    { 
        return $this->__toTimestamp(); 
    }

    
    
    /**
     * Get date as an url parameter prepared value
     * @return string
     */
    public function toURLParameter()
    { 
        return $this->__toURLParameter(); 
    }

    
    
    /**
     * Set date value to now
     * @return wsDate
     */
    public function setToNow() 
    { 
        return $this->__setToNow(); 
    }

    
    
    /**
     * Get date format valid to RFC 2822
     * @return string
     */
    public function toMailDate() 
    { 
        return $this->__toMailDate(); 
    }

    
    
    /**
     * Get date as micro time value
     * @return int
     */
    public function toMicrotime() 
    { 
        return $this->__toDate(); 
    }



    /**
     * Set year
     * @param string $sValue
     */
    public function setYear($sValue) 
    { 
        $this->__sYear = $sValue; 
    }

    
    
    /**
     * Set month
     * @param string $sValue
     */
    public function setMonth($sValue) 
    { 
        $this->__sMonth = $sValue; 
    }

    
    
    /**
     * Set day
     * @param string $sValue
     */
    public function setDay($sValue) 
    { 
        $this->__sDay = $sValue; 
    }

    
    
    /**
     * Set hour
     * @param string $sValue
     */
    public function setHour($sValue) 
    { 
        $this->__sHour = $sValue; 
    }

    
    
    /**
     * Set minute
     * @param string $sValue
     */
    public function setMinute($sValue) 
    { 
        $this->__sMinute = $sValue; 
    }

    
    
    /**
     * Set second
     * @param string $sValue
     */
    public function setSecond($sValue) 
    { 
        $this->__sSecond = $sValue; 
    }

    
    
    /**
     * Set date with a microtime value
     * @param int $iValue
     * @return wsDate
     */
    public function setMicrotime($iValue)
    {
        $dDate = date('Y-m-d H:i:s', $iValue);

        $this->__sYear      = substr($dDate, 0, 4);
        $this->__sMonth     = substr($dDate, 5, 2);
        $this->__sDay       = substr($dDate, 8, 2);
        $this->__sHour      = substr($dDate, 11, 2);
        $this->__sMinute    = substr($dDate, 14, 2);
        $this->__sSecond    = substr($dDate, 17, 2);

        return $this;
    }



    /**
     * Set date to now
     * @return wsDate
     */
    protected function __setToNow()
    {
        $dDate =  date('Y-m-d H:i:s');
        $this->__sYear      = substr($dDate, 0, 4);
        $this->__sMonth     = substr($dDate, 5, 2);
        $this->__sDay       = substr($dDate, 8, 2);
        $this->__sHour      = substr($dDate, 11, 2);
        $this->__sMinute    = substr($dDate, 14, 2);
        $this->__sSecond    = substr($dDate, 17, 2);
        return $this;
    }


    
    /**
     * Gibt den Timestamp-String des Datums zurueck
     * @return string
     */
    protected function __toTimestamp()
    {
        return $this->__sYear.'-'.$this->__sMonth.'-'.$this->__sDay.' '.$this->__sHour.':'.$this->__sMinute.':'.$this->__sSecond;
    }

    

    /**
     * Gibt den Timestamp-String des Datums fuer die URL-Uebergabe zurueck
     * @return string
     */
    protected function __toURLParameter()
    {
        return $this->__sYear.'.'.$this->__sMonth.'.'.$this->__sDay.'.'.$this->__sHour.'.'.$this->__sMinute.'.'.$this->__sSecond;
    }


    
    /**
     * Wandelt das Datum nach RFC 2822 in ein Mail-valides Format um
     * @return string
     */
    protected function __toMailDate()
    {
        // [Sat, ]26 Jun 2010 12:17:59
        return $this->__sDay.' '.$this->__aMonthNames[$this->__sMonth].' '.$this->__sYear.' '.$this->__sHour.':'.$this->__sMinute.':'.$this->__sSecond;
    }

    

    /**
     * Wandelt das Datum in einen Zahlenwert um
     * @return string
     */
    protected function __toDate()
    {
        return mktime(
                    (int)$this->__sHour, 
                    (int)$this->__sMinute, 
                    (int)$this->__sSecond, 
                    (int)$this->__sMonth, 
                    (int)$this->__sDay, 
                    (int)$this->__sYear
                );
    }
    
}
