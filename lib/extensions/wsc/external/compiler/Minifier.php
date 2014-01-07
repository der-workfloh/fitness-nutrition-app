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
 * @file Minifier.php
 * @namespace WSC\external\compiler
 *
 *
 * @author Florian Horn
 * @version
 * Version 1.0, 21.10.2010<br/>
 * Version 1.1, 26.02.2011<br/>
 * Version 1.2, 23.05.2011<br/>
 * Version 1.3, 25.08.2011<br/>
 * Version 2.0, 03.11.2013<br/>
 *
 */

namespace WSC\external\compiler;

use WSC\external\compiler\AbstractBuilder;
use APF\core\configuration\provider\BaseConfiguration;

class Minifier extends AbstractBuilder
{
    protected $__sOutputDirectory;
    protected $__sOutputFile;
    protected $__sOutputPrefix;
    protected $__sOutputAppendix;
    protected $__aInputFiles;




    /**
     * Constructor
     * @param BaseConfiguration $oConfig 
     */
    public function __construct(BaseConfiguration $oAppConfig, $sAppName)
    {
        parent::__construct($oAppConfig,$sAppName);
        if( empty( $this->__aMinifiers ) ) {
            return;
        }


        // --- Laufe alle angegebenen Minifier-Makros durch
        $aProject = $this->__aMinifiers[$sAppName];
        foreach($aProject as $aSection) {
            foreach($aSection as $aFile) {
                // --- Lade Settings
                $this->setOutputDirectory($this->__sOutputDir.'::lib::apps::'.$sAppName.'::pres::templates::'.$aFile['Dir']);
                $this->setOutputFile($aFile['Name']);

                // --- Files auslesen
                $this->setSection($aFile);

                // --- Erstelle MainFiles
                $this->setOutputPrefix("/*\n * wsCatalyst Optimizer - ".$sAppName." - (Minified)\n */\n");
                $this->setOutputAppendix('');
            }
        }
       
        $this->createOutputFile();
        $this->deleteSourceFilesInOutput();
    }
    


    /**
     * Set the directory the file to be written in exists
     * @param string $sNamespace
     */
    public function setOutputDirectory($sNamespace) { 
        $this->__sOutputDirectory = str_replace( '::' , '/' , $sNamespace);
    }

    
    
    /**
     * Set the name of the file to be written in
     * @param string $sFilename
     */
    public function setOutputFile($sFilename) { 
        $this->__sOutputFile = $sFilename; 
    }

    
    
    /**
     * Set a content prefix
     * @param string $sPrefix
     */
    public function setOutputPrefix($sPrefix) { 
        $this->__sOutputPrefix = $sPrefix; 
    }
    
    
    
    /**
     * Set a content appendix
     * @param string $sAppendix
     */
    public function setOutputAppendix($sAppendix) { 
        $this->__sOutputAppendix = $sAppendix; 
    }

    
    
    /**
     * Set section which results the input files
     * @param array $aInputFiles 
     */
    public function setSection(array $aFile = array())
    {
        for($n = 0; $n < count($aFile); ++$n) {
            if( !array_key_exists( 'Add.'.$n , $aFile ) ) {
                break;
            }
            $this->__aInputFiles[] = $aFile[ 'Add.'.$n ];
        }
    }

    
    
    /**
     * Create the output file
     * @return void
     */
    public function createOutputFile()
    {
        $this->__createOutputFile();
    }
    
    
    
    /**
     * Delete the source files in the output folder
     * @return void
     */
    public function deleteSourceFilesInOutput()
    {
        $this->__deleteSourceFiles();        
    }



    /**
     * Prepare the file and directory
     * @return void
     */
    protected function __createOutputFile()
    {
        $fp = fopen($this->__sOutputDirectory.DS.$this->__sOutputFile, 'w+');

        // --- Prefix
        fwrite($fp, $this->__sOutputPrefix);

        // --- 
        foreach($this->__aInputFiles as $file) {

            $s = trim(file_get_contents($this->__sOutputDirectory.DS.$file));
            // -- Einzeilige Kommentare entfernen
            $s = preg_replace(('#\/\/.*$#imsU'),' ',$s);
            // -- Kommentare entfernen
            $s = preg_replace(('#/\*.*\*/#imsU'),' ',$s);
            // -- Zeilenumbrüche entfernen
            $s = preg_replace(('#[\r\t\n]+#imsU'),' ',$s);
            // -- Leerzeichen reduzieren
            $s = preg_replace(('#[\ ]+#ims')," ",$s);
            // -- Zeilenumbrüche entfernen reduzieren
            $s = preg_replace(('#[^\r]{1}\n#s'),"\r\n",$s);
            fwrite($fp, ' '.$s);
        }

        fwrite($fp , $this->__sOutputAppendix);
        fclose($fp);
    }



    /**
     * Delete the source files in output folder
     */
    protected function __deleteSourceFiles()
    {
        foreach ($this->__aInputFiles as $file) {
            @unlink($this->__sOutputDirectory.DS.$file);
        }
    }
}
