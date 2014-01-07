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
 * @file UploadManager.php
 * @namespace WSC\tools\filehandler
 *
 * 
 *
 * @author Florian Horn
 * @version
 * Version 1.0, 13.04.2010<br/>
 * Version 2.0, 15.11.2013<br/>
 *
 */

namespace WSC\tools\filehandler;

use APF\core\pagecontroller\APFObject;
use APF\tools\filesystem\FilesystemManager;
use WSC\tools\datetime\wsDate;
use WSC\functions\common\common;

class UploadManager extends APFObject
{
    /**
     * Upload
     * @return bool
     */
    public function upload($sFile, $sTargetPath)
    {
        /*
         * Eventuell Pfade erstellen via FilesystemManager
         */
        FilesystemManager::createFolder($sTargetPath , "777");
        
        /*
         * Hochladen (Kopiervorgang)
         * DB-Relation ablegen zwischen codiertem Namen und File-ID
         */
        $Date = new wsDate();
        $sFilename = \sha1(\md5( $sFile.$Date->setToNow()->toTimestamp().generatePassword(10, true)));
        if (\move_uploaded_file($sFile, $sTargetPath.DS.$sFilename)) {
            return $sFilename;
        } else {
            return false;
        }
                
    }
}
