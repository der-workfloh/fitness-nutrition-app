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
 * @file SubversionHandler.php
 * @namespace WSC\external\compiler
 *
 *
 * @author Florian Horn
 * @version
 * Version 1.0, 11.11.2013<br/>
 *
 */

namespace WSC\external\compiler;

use WSC\external\compiler\PostAbstractBuilder;
use APF\core\registry\Registry;
use APF\core\configuration\ConfigurationManager;
use APF\core\configuration\provider\BaseConfiguration;

class SubversionHandler extends PostAbstractBuilder 
{
    /**
     * Constructor
     * @param \APF\core\configuration\provider\BaseConfiguration $oSysConfig
     * @param array $aApps
     */
    public function __construct(BaseConfiguration $oSysConfig, array $aApps = array())
    {
        parent::__construct($oSysConfig, $aApps);
        
        $oSVNCnf = ConfigurationManager::loadConfiguration(
                    'WSC\external\compiler', 
                    null, 
                    null, 
                    Registry::retrieve('APF\core', 'Environment'), 
                    'subversion.ini'
                );

        $sTargetXMLFile = $oSVNCnf->getSection('Settings')->getValue('TargetXMLFile');
        $sTargetLogFile = $oSVNCnf->getSection('Settings')->getValue('TargetLogFile');
        
        try {
            $this->getSubversionLogFileAsXML(
                        $oSVNCnf->getSection('Settings')->getValue('RepositoryURL'), 
                        $oSVNCnf->getSection('Settings')->getValue('SVNUsername'), 
                        $oSVNCnf->getSection('Settings')->getValue('SVNPassword'), 
                        $oSVNCnf->getSection('Settings')->getValue('RevisionStart'), 
                        $oSVNCnf->getSection('Settings')->getValue('RevisionEnd'), 
                        $sTargetXMLFile
                    );
        }
        catch (\RuntimeException $ex) {
            throw new \RuntimeException(
                        '['.\get_class().'::__construct()] Get Subversion Log File as XML failed.'
                    );
        }
        
        // Write subversion log to log text file
        $sLogMsg = $this->getXMLLogFileAsString($sTargetXMLFile);
        $fh = \fopen($sTargetLogFile, 'w+');
        \fwrite($fh, $sLogMsg, strlen($sLogMsg));
        \fclose($fh);
    }
    
    
    
    /**
     * Get an XML Attribut as string
     * @param SimpleXMLElement $aObject
     * @param string $sAttribute
     * @return string
     */
    protected function getXMLAttributeAsString(\SimpleXMLElement $oObject, $sAttribute)
    {
        if(isset($oObject[$sAttribute])) {
            return (string) $oObject[$sAttribute];
        }
        return '';
    }
    
    
    
    /**
     * Get the current Subversion Log file as XML
     * @param string $sRepositoryURL
     * @param string $sUsername
     * @param string $sPassword
     * @param string $sRevisionStart
     * @param string $sRevisionEnd
     * @param string $sTargetXMLFile
     * @return void
     * @throws \WSC\external\compiler\RuntimeException
     */
    protected function getSubversionLogFileAsXML($sRepositoryURL, $sUsername, $sPassword, $sRevisionStart, $sRevisionEnd, $sTargetXMLFile) {
        try { 
            \shell_exec("svn log --xml --verbose ".$sRepositoryURL." --username ".$sUsername." --password ".$sPassword." -r ".$sRevisionStart.":".$sRevisionEnd." > ".$sTargetXMLFile." ") ;
        } catch (\RuntimeException $ex) {
            throw $ex;
        }
    }
    
    
    
    /**
     * Get the subversion XML Log file as string
     * @param string $sTargetXMLFile
     * @return string
     */
    protected function getXMLLogFileAsString($sTargetXMLFile) {
        $sXMLFile = \simplexml_load_file($sTargetXMLFile);
        
        $sLogFileString = '';
        
        foreach ($sXMLFile->logentry as $logentry) {
            foreach ($logentry->paths as $paths) {
                
                // Get all files which have been changed in commit
                /*
                $aActions = array();
                foreach ($paths->path as $path) {
                    $aActions[] = '* ['.$path['action'].'] '.$path;
                }
                 * 
                 */
                
                $sHighestRevision = $this->getXMLAttributeAsString($logentry, 'revision');
                $sLatestCommitDate = \date('d.m.Y', strtotime((string) $logentry->date));
                // --- add message; ignore empty messages
                if (strlen(trim($logentry->msg)) > 0) {
                    $sLogFileString = \sprintf("%s\r\n", $logentry->msg).$sLogFileString;
                }
                
                /*
                $sLogFileString .= sprintf(
                            "[LOG] Revision: %s\r\nDate: %s\r\nMessage: %s\r\nCommited By: %s\r\n%s\r\n\r\n",
                            $this->getXMLAttributeAsString($logentry, 'revision'),
                            $date,
                            $logentry->msg,
                            $logentry->author,
                            \implode("\r\n", $aActions)
                        );
                 * 
                 */
            }
        }
        
        $sLogFileStringHeadline = \sprintf("r%s (%s)\r\n\r\n", $sHighestRevision, $sLatestCommitDate);
        return $sLogFileStringHeadline.$sLogFileString;
    }
}
