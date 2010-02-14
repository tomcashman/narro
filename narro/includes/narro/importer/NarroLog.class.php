<?php
    /**
     * Narro is an application that allows online software translation and maintenance.
     * Copyright (C) 2008-2010 Alexandru Szasz <alexxed@gmail.com>
     * http://code.google.com/p/narro/
     *
     * This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public
     * License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any
     * later version.
     *
     * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the
     * implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for
     * more details.
     *
     * You should have received a copy of the GNU General Public License along with this program; if not, write to the
     * Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
     */
    class NarroLog {
        /**
         * whether to log output
         */
        public static $blnLogOutput = true;
        /*
         * whether to echo the output
         */
        public static $blnEchoOutput = false;

        /**
         * log file name with path
         */
        private static $strLogFile;
        /*
         * severity level
         */
        public static $intMinLogLevel;

        public static function LogMessage($intMessageType, $strText) {
            if ($intMessageType < self::$intMinLogLevel)
                return false;

            if (self::$blnEchoOutput)
                echo $strText . "\n";

            if (self::$blnLogOutput) {
                self::OutputLog($intMessageType, $strText);
            }
        }

        private static function OutputLog($intMessageType, $strText) {
            if (!self::$strLogFile) {
                self::$strLogFile = __TMP_PATH__ . '/narro-' . QApplication::$Language->LanguageCode . '.log';
                self::OutputLog($intMessageType, date('l, j F Y g:i a'));
            }

            $hndLogFile = fopen(self::$strLogFile, 'a+');

            if (isset($hndLogFile) && $hndLogFile) {
                if ($strText != '')
                    fputs($hndLogFile, $strText . "\n");
            }
            else {
                if ($strText != '')
                    error_log($strText);
            }

            if ($hndLogFile) {
                fclose($hndLogFile);
                @chmod(self::$strLogFile, 0666);
            }

        }

        public static function SetLogFile($strLogFile, $blnClear = false) {
            self::$strLogFile = __TMP_PATH__ . '/' . $strLogFile;

            if ($blnClear)
                self::ClearLog();
        }

        public static function ClearLog() {

            if (file_exists(self::$strLogFile)) {
                unlink(self::$strLogFile);
            }
        }

        public static function GetLogContents() {

            if (file_exists(self::$strLogFile))
                return file_get_contents(self::$strLogFile);
            elseif (file_exists(__TMP_PATH__ . '/narro-' . QApplication::$Language->LanguageCode . '.log')) {
                self::OutputLog(3, 'No log found, showing the full application log');
                return file_get_contents(__TMP_PATH__ . '/narro-' . QApplication::$Language->LanguageCode . '.log');
            }
            else
                return 'No log found, check the server log.';
        }
    }
?>