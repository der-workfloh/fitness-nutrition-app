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
 * @file DownloadManager.php
 * @namespace WSC\tools\filehandler
 *
 * Erlaubt das runterladen von Dateien durch die RANGE Angabe
 * sowohl Resume als auch den parallelen Download einer einzelnen
 * Datei mit mehreren Threads.
 *
 * @author Florian Horn
 * @version
 * Version 1.0, 13.04.2010<br/>
 * Version 2.0, 15.11.2013<br/>
 *
 */

namespace WSC\tools\filehandler;

use APF\core\pagecontroller\APFObject;

class DownloadManager extends APFObject
{
    /**
     * Send a file as download to the browser (maybe limited in speed)
     * @param string $filePath
     * @param int $rate speedlimit in KB/s
     * @return void
     */
    public function send($filePath, $rate = 0)
    {
        // Check if file exists
        if (!\is_file($filePath)) {
            throw new \Exception('File not found.');
        }

        // get more information about the file
        $filename = \basename($filePath);
        $size = \filesize($filePath);
        $finfo = \finfo_open(\FILEINFO_MIME);
        $mimetype = \finfo_file($finfo, \realpath($filePath));
        \finfo_close($finfo);

        // Create file handle
        $fp = \fopen($filePath, 'rb');

        $seekStart = 0;
        $seekEnd = $size;

        // Check if only a specific part should be sent
        if (isset($_SERVER['HTTP_RANGE'])) {
            // If so, calculate the range to use
            $range = \explode('-', \substr($_SERVER['HTTP_RANGE'], 6));

            $seekStart = \intval($range[0]);
            if ($range[1] > 0) {
                $seekEnd = \intval($range[1]);
            }

            // Seek to the start
            \fseek($fp, $seekStart);

            // Set headers incl range info
            \header('HTTP/1.1 206 Partial Content');
            \header(\sprintf('Content-Range: bytes %d-%d/%d', $seekStart, $seekEnd, $size));
        } else { 
            // Set headers for full file
            \header('HTTP/1.1 200 OK');
        }

        // Output some headers
        \header('Cache-Control: private');
        \header('Content-Type: ' . $mimetype);
        \header('Content-Disposition: attachment; filename="' . $filename . '"');
        \header('Content-Transfer-Encoding: binary');
        \header("Content-Description: File Transfer");
        \header('Content-Length: ' . ($seekEnd - $seekStart));
        \header('Accept-Ranges: bytes');
        \header('Last-Modified: ' . \gmdate('D, d M Y H:i:s', \filemtime($filePath)) . ' GMT');

        $block = 1024;
        // limit download speed
        if($rate > 0) {
            $block *= $rate;
        }

        // disable timeout before download starts
        \set_time_limit(0);

        // Send file until end is reached
        while (!\feof($fp)) {
            $timeStart = \microtime(true);
            echo \fread($fp, $block);
            \flush();
            $wait = (\microtime(true) - $timeStart) * 1000000;

            // if speedlimit is defined, make sure to only send specified bytes per second
            if($rate > 0) {
                \usleep(1000000 - $wait);
            }
        }

        // Close handle
        \fclose($fp);
    }
}
