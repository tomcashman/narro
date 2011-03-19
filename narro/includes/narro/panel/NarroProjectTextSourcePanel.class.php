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

    class NarroProjectTextSourcePanel extends QPanel {

        public $pnlTextSource;
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

            $this->pnlTextSource = new QTabPanel($this);
            $this->pnlTextSource->UseAjax = QApplication::$UseAjax;
            $objDirectoryPanel = new NarroDirectorySourcePanel($objProject, $objLanguage, $this->pnlTextSource);
            $objDirectoryPanel->Directory = $this->objProject->DefaultTemplatePath;
            $this->pnlTextSource->addTab($objDirectoryPanel, t('On this server'));
            $this->pnlTextSource->addTab(new NarroUploadSourcePanel($objProject, $objLanguage, $this->pnlTextSource), t('On my computer'));
            $this->pnlTextSource->addTab(new NarroWebSourcePanel($objProject, $objLanguage, $this->pnlTextSource), t('On the web'));
            $this->pnlTextSource->addTab(new NarroMercurialSourcePanel($objProject, $objLanguage, $this->pnlTextSource), t('Mercurial'));
            $this->pnlTextSource->addTab(new NarroSvnSourcePanel($objProject, $objLanguage, $this->pnlTextSource), t('SVN'));
        }

        public function GetControlHtml() {
            $this->strText = $this->pnlTextSource->Render(false);
            return parent::GetControlHtml();
        }

        public function __get($strName) {
            switch ($strName) {
                case "Directory":
                    return $this->pnlTextSource->SelectedTab->Directory;

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

    }
