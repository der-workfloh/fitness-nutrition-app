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
 * @file AbstractCronjob.php
 * @namespace WSC\templates\cronjob
 *
 * 
 * @author Florian Horn
 * @version
 * Version 1.0, 25.03.2010<br/>
 * Version 1.1, 26.10.2010<br/>
 * Version 1.2, 30.04.2011<br/>
 *
 */

namespace WSC\templates\cronjob;

use APF\core\logging\Logger;
use APF\core\logging\LogEntry;
use WSC\tools\datetime\wsDate;
use WSC\core\database\DatabaseFactory as wsDB;
use WSC\core\handler\AbstractHandler;

abstract class AbstractCronjob extends AbstractHandler
{
    const MAXIMUM_RANGE_LIMITER = 9999;
    
    protected $__sOutput        = '';
    protected $__sCronjobName   = '';
    protected $__dLastRun       = '0000-00-00 00:00:00';
    protected $__sDBDriver      = NULL;



    /**
     * Set the name of the cronjob
     * @param string $sName
     * @return void
     */
    public function setCronjobName( $sName ) 
    { 
        $this->__sCronjobName = $sName; 
    }
    
    

    /**
     * Checks if a cronjob needs to be run
     * @return void
     */
    public function initialize()
    {
        $DB     = wsDB::getPDO($this->__sDBDriver);
        $sQry   = 'SELECT
                    dLast
                   FROM
                    ent_cronjob
                   WHERE
                    sCronjob = :cronjob
                    AND sComponent = :namespace';
        $sth = $DB->prepare($sQry);
        $sth->bindValue(':cronjob', $this->__sCronjobName, PDO::PARAM_STR );
        $sth->bindValue(':namespace', $this->__sCronjobName, PDO::PARAM_STR );

        // --- Statement ausfuehren
        if (!$sth->execute()) {
            $oLogger = wsDB::getLogger();
            $oLogger->logEntry(
                        'database',
                        sprintf(
                                '['.get_class().'::initialize()] Could not execute "%s"'.
                                "\nError-Information: %s",
                                $sQry,
                                implode(',',$sth->errorInfo())
                                ),
                        LogEntry::SEVERITY_ERROR 
                    );
        }

        $oCJ = $sth->fetch(PDO::FETCH_ASSOC);

        // --- Wenn nicht 1, dann bisher noch nie ausgeführt
        if ($sth->rowCount() !== 1) {
            $this->__dLastRun = $oCJ['dLast'];
        }
    }



    /**
     * Runs the cronjob and updates the cronjob last run date in the database<br/>
     * with the current date
     * @return void
     */
    public function run()
    {
        $this->execute();


        $DB     = wsDB::getPDO($this->__sDBDriver);
        $sQry   = 'REPLACE INTO
                    ent_cronjob
                   SET
                    sCronjob=:cronjob,
                    sComponent=:namespace,
                    dLast=:date';
        $sth = $DB->prepare( $sQry );
        $sth->bindValue( ':cronjob' , $this->__sCronjobName  , PDO::PARAM_STR );
        $sth->bindValue( ':namespace' , $this->__sCronjobName  , PDO::PARAM_STR );
        $sth->bindValue( ':date' , date('Y-m-d H:i:s')  , PDO::PARAM_STR );

        // --- Statement ausfuehren
        if (!$sth->execute()) {
            $oLogger = wsDB::getLogger();
            $oLogger->logEntry(
                        'database',
                        sprintf(
                                '['.get_class().'::initialize()] Could not execute "%s"'.
                                "\nError-Information: %s",
                                $sQry,
                                implode(',',$sth->errorInfo())
                                ),
                        LogEntry::SEVERITY_ERROR 
                    );
        }
    }



    /**
     * Prüft ob der Cronjob ausgeführt werden soll
     * @param string $sUpdateRoutine
     * @return bool
     */
    public function isRunable($sUpdateRoutine = '*,*,*,*,*')
    {
        // --- min,hr,day,month,weekday
        $aTiming = explode(',', $sUpdateRoutine);
        if (count($aTiming) !== 5) {
            return false;
        }

        $dNow = new wsDate();
        if ($this->__switchOp( $aTiming[0] , $dNow->getMinute() ) === true &&
            $this->__switchOp( $aTiming[1] , $dNow->getHour() ) === true &&
            $this->__switchOp( $aTiming[2] , $dNow->getDay() ) === true &&
            $this->__switchOp( $aTiming[3] , $dNow->getMonth() ) === true &&
            $this->__switchOp( $aTiming[4] , $dNow->getWeek() ) === true) {
                return true;
        }
            
        return false;
    }



    /**
     * Returns the generated output
     * @return string
     */
    public function getContent() 
    { 
        return $this->__sOutput;        
    }



    /**
     * Checkt den Operator für die Zeitroutinen-Berechnung
     * @param string $sArg
     * @param int $dNow
     * @return bool
     */
    protected function __switchOp($sArg, $dNow)
    {
        // --- From-Till-Range
        $aArg = explode("-", $sArg);
        $n = count($aArg);
        if ($n !== 1 && $n !== 3) {
            return false;
        }
        if ($n === 3) {
            return $this->__opRange($aArg[0], $aArg[2], $dNow);
        }

        // --- Chain
        $aArg = explode("|", $sArg);
        $n = count($aArg);
        if ($n !== 1) {
            return $this->__opChain($sArg, $dNow);
        }

        // --- Frequence
        $aArg = explode("/", $sArg);
        $n = count($aArg);
        if ($n !== 1 && $n !== 3) {
            return false;
        }
        if ($n === 3) {
            return $this->__opFrequence($aArg[0], $aArg[2], $dNow);
        }

        // --- Always
        if ($aArg[0] === '*') {
            return true;
        }
        
        // --- Simple Value
        return (int)$aArg[0] === (int)$dNow ? true : false;
    }



    /**
     * Time-Range-Operator
     * @param int $iArg1
     * @param int $iArg2
     * @param wsDate $dNow
     * @return bool
     */
    protected function __opRange($iArg1, $iArg2, $dNow)
    {
        $iMax = (int)$iArg2;
        if ($iMax > self::MAXIMUM_RANGE_LIMITER) {
            $iMax = self::MAXIMUM_RANGE_LIMITER;
        }
        
        for ($n = (int)$iArg1; $n < $iMax; ++$n) {
            if ($n === (int)$dNow) {
                return true;
            }
        }
        return false;
    }



    /**
     * Time-Chain-Operator
     * @param string $sArg
     * @param int $dNow
     * @return bool
     */
    protected function __opChain($sArg, $dNow)
    {
        $aArg = explode("|", $sArg);
        $n = count($aArg);
        for ($i = 0; $i < $n; ++$i) {
            if ((int)$aArg[$i] === (int)$dNow) {
                return true;
            }
        }
        return false;
    }



    /**
     * Time-Frequence-Operator
     * @param int $iArg1
     * @param int $iArg2
     * @param int $dNow
     * @return bool
     */
    protected function __opFrequence($iArg1, $iArg2, $dNow)
    {
        if ($iArg1 === '*') {
            return ( (int)$dNow % (int)$iArg2 === 0  ) ? true : false;
        }

        if (!\is_numeric($iArg2)) {
            return false;
        }
        
        return (int)($iArg1/$iArg2) === (int)$dNow ? true : false;
    }

}
