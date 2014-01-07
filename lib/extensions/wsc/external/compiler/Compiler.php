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
 * @file Compiler.php
 * @namespace WSC\external\compiler
 *
 *
 * @author Florian Horn
 * @version
 * Version 1.0, 21.10.2010<br/>
 * Version 1.1, 26.02.2011<br/>
 * Version 1.2, 24.04.2011<br/>
 * Version 1.3, 06.05.2011<br/>
 * Version 1.4, 25.08.2011<br/>
 * Version 2.0, 03.11.2013<br/>
 *
 */

namespace WSC\external\compiler;

use WSC\external\compiler\PreAbstractBuilder;
use WSC\external\compiler\Replacer;
use APF\core\configuration\provider\BaseConfiguration;

class Compiler extends PreAbstractBuilder
{
    const PATTERN_MAKRO_MATCH       = "/\*\*\*[\ ]*BEGIN[\ ]*\(([A-Z0-9\,\_]+)\)[\ ]*\*/";
    const PATTERN_MAKRO_REPLACE     = "/\*\*\*[\ ]*BEGIN[\ ]*\(%s\)[\ ]*\*/(.*)/\*\*\*[\ ]*END[\ ]*\(%s\)[\ ]*\*/";
    const SYSTEM_CONFIG_DIR         = "lib/config/system/";
    const FOLDER_STRUCTURE_XML_SCHEME_FILE = "logs/compiler_folder_files_scheme.xml";
    const FOLDER_STRUCTURE_XML_BASE = "<?xml version='1.0' standalone='yes'?><compiler><folders></folders><files></files></compiler>";

    
    protected $__oReplacer;
    protected $__iFilesParsed       = 0;
    protected $__iFilesKept         = 0;



    /**
     * Constructor
     * @param BaseConfiguration $oSysConfig
     * @param array $aApps 
     */
    public function __construct(BaseConfiguration $oSysConfig, array $aApps = array())
    {
        parent::__construct($oSysConfig, $aApps);
        $this->__oReplacer  = new Replacer($oSysConfig);
        
        $aReplacer = array();
        if ($oSysConfig->getSection("Replacement") &&
            \count( $oSysConfig->getSection("Replacement")->getValueNames() ) !== 0) {
            foreach ($oSysConfig->getSection("Replacement")->getValueNames() as $Statement) {
                $aReplacer[$Statement] = $oSysConfig->getSection("Replacement")->getValue($Statement);
            }
        }
        
        $this->__oReplacer->setStatements($aReplacer);
        
        $aProtect = array();
        foreach ($this->__aProtect as $v) {
            $aProtect[] = \str_replace($this->__sBaseDir,$this->__sOutputDir,$v);
        }
        $this->__clearFolder($this->__sOutputDir, $aProtect);
        $this->createFolderStructureXMLScheme($this->__sBaseDir.DS.self::FOLDER_STRUCTURE_XML_SCHEME_FILE);
        $this->createFolderStructure($this->__sBaseDir.DS.self::FOLDER_STRUCTURE_XML_SCHEME_FILE);
        $this->parseFilesAndCopy($this->__sBaseDir.DS.self::FOLDER_STRUCTURE_XML_SCHEME_FILE);
    }
    
    
    
    /**
     * Creates a XML File with all folder and file informations
     * @param string $sFolderStructureXMLFile
     * @return \SimpleXMLElement
     */
    public function createFolderStructureXMLScheme($sFolderStructureXMLFile)
    {
        $sXML = new \SimpleXMLElement(self::FOLDER_STRUCTURE_XML_BASE);
             
        // --- Folders
        foreach($this->__aAllDirs as $sDir) {
            $sXML->folders->addChild('folder', $sDir);
        }  
        
        // --- Files
        foreach($this->__aAllFiles as $sFile) {
            $sXML->files->addChild('file', $sFile);
        }
        
        // --- Try writing file
        try {
            $sXML->asXML($sFolderStructureXMLFile);
        } catch (\Exception $e) {
            throw $e;
        }
        
        return $sXML;
    }

    
    
    /**
     * Create output folder structure
     * @param string $sFolderStructureXMLFile
     */
    public function createFolderStructure($sFolderStructureXMLFile)
    {
        /*
         * Erzeuge Output-Ordner, falls noch nicht vorhanden
         */
        if (!is_dir($this->__sOutputDir) && !mkdir($this->__sOutputDir, 0755)) {
            throw new \RuntimeException(sprintf("Could not create %s!", $this->__sOutputDir));
        }
        
        
        
        /*
         * Lese XML-File aus
         */
        if (\file_exists($sFolderStructureXMLFile)) {
            $oXML = \simplexml_load_file($sFolderStructureXMLFile);
        } else {
            throw new \InvalidArgumentException('['.  \get_class().'::createFolderStructure] '.
                    'Folder structure XML File "'.$sFolderStructureXMLFile.'" does not exist.');
        }
        
        
        /*
         * Erzeuge alle Verzeichnisse
         */
        foreach ($oXML->folders->children() as $sFolder) {
            $this->__createDir(\str_replace($this->__sBaseDir, $this->__sOutputDir, $sFolder));
        }
    }
    
    

    /**
     * Parse the files and copy them to the output folder
     * @param string $sFolderStructureXMLFile
     */
    public function parseFilesAndCopy($sFolderStructureXMLFile)
    {
        $this->__iFilesParsed   = 0;
        $this->__iFilesKept     = 0;
        
        /*
         * Lese XML-File aus
         */
        if (\file_exists($sFolderStructureXMLFile)) {
            $oXML = \simplexml_load_file($sFolderStructureXMLFile);
        } else {
            throw new \InvalidArgumentException('['.  \get_class().'::createFolderStructure] '.
                    'Folder structure XML File "'.$sFolderStructureXMLFile.'" does not exist.');
        }
        
        
        
        /*
         * Stepping through all files to parse them
         */
        foreach ($oXML->files->children() as $sFile) {
            // --- Nach Makros suchen
            $sOutput = $this->__parseFileForMakro(\file_get_contents($sFile));
            ++$this->__iFilesParsed;

            // --- File nur übernehmen, wenn es Inhalt gibt
            $this->__parseContentAndSave($sOutput, \str_replace($this->__sBaseDir, $this->__sOutputDir, $sFile));
        }
    }

    
    
    /**
     * Delete empty folders
     * @param string $sDir
     * @param array $aIgnoreDirs
     */
    public function deleteEmptyDirectories($sDir , array $aIgnoreDirs = array()) 
    { 
        $this->__deleteEmptyDirectories($sDir, $aIgnoreDirs);
    }
    
    

    /**
     * Parse the content for markos
     * @param string $sFileContent
     * @return string
     */
    protected function __parseFileForMakro($sFileContent)
    {
        $aMatches = array();
        while (\preg_match("?".self::PATTERN_MAKRO_MATCH."?imsU", $sFileContent, $aMatches)) {
            $aKeys = \explode(',', $aMatches[1]);
            // --- Options / Keys-Schnittmenge
            // --- Vergleiche gefundene Options mit Keys
            // --- Alle Keys müssen in Options enthalten sein
            $aIntersect = \array_intersect($this->__aMakros, $aKeys);
            $aDiff = \array_diff($aKeys, $aIntersect);

            $sCompPattern = \sprintf(
                        '?'.self::PATTERN_MAKRO_REPLACE.'?imsU',
                        $aMatches[1],
                        $aMatches[1]
                    );

            if (empty($aDiff)) {
                $sFileContent = \preg_replace($sCompPattern, '${1}', $sFileContent, 1);
            } else {
                $sFileContent = \preg_replace($sCompPattern, '', $sFileContent, 1);
            }

            $aMatches = array();
        }
        return $sFileContent;
    }



    /**
     * Parse a files' content and save the result in output folder
     * @param string $sContent
     * @param string $sFilename
     */
    protected function __parseContentAndSave($sContent, $sFilename) {
        $sContent = trim($sContent);
        if (empty($sContent)) {
            return;
        }

        $sContent = $this->__oReplacer->replace($sContent);

        /*
         * Save file
         */
        $fp = \fopen($sFilename, "w+");
        \fwrite($fp, $sContent);
        \fclose($fp);
        ++$this->__iFilesKept;
    }
}
