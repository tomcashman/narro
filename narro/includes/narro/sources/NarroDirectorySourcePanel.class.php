<?php
    /**
     * Narro is an application that allows online software translation and maintenance.
     * Copyright (C) 2008-2011 Alexandru Szasz <alexxed@gmail.com>
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

    class NarroDirectorySourcePanel extends QPanel {
        protected $txtDirectory;
        protected $objProject;
        protected $objLanguage;

        public function __construct(NarroProject $objProject, NarroLanguage $objLanguage, $objParentObject, $strControlId = null) {
            // Call the Parent
            try {
                parent::__construct($objParentObject, $strControlId);
            } catch (QCallerException $objExc) {
                $objExc->IncrementOffset();
                throw $objExc;
            }

            $this->objProject = $objProject;
            $this->objLanguage = $objLanguage;

            $this->blnAutoRenderChildren = true;

            $this->txtDirectory = new QTextBox($this);
            $this->txtDirectory->DisplayStyle = QDisplayStyle::Block;
            $this->txtDirectory->Width = 500;
            $this->txtDirectory->Instructions = t('Please enter the full path to the directory that contains the files');
        }

        public function __get($strName) {
            switch ($strName) {
                case "Directory":
                    return $this->txtDirectory->Text;

                default:
                    try {
                        return parent::__get($strName);
                        break;
                    } catch (QCallerException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
            }
        }

        public function __set($strName, $mixValue) {
            $this->blnModified = true;

            switch ($strName) {
                case "Directory":
                    try {
                        $this->txtDirectory->Text = QType::Cast($mixValue, QType::String);
                        break;
                    } catch (QInvalidCastException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }

                default:
                    try {
                        parent::__set($strName, $mixValue);
                    } catch (QCallerException $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                    break;
            }
        }

    }
