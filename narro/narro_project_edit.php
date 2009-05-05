<?php
    /**
     * Narro is an application that allows online software translation and maintenance.
     * Copyright (C) 2008 Alexandru Szasz <alexxed@gmail.com>
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

    require_once('includes/prepend.inc.php');

    class NarroProjectEditForm extends QForm {
        protected $pnlTab;
        protected $pnlProjectEdit;
        protected $objNarroProject;

        protected function SetupNarroProject() {
            // Lookup Object PK information from Query String (if applicable)
            // Set mode to Edit or New depending on what's found
            $intProjectId = NarroApp::QueryString('p');
            $this->objNarroProject = NarroProject::Load(($intProjectId));
        }
        protected function Form_Create() {
            parent::Form_Create();

            $this->SetupNarroProject();

            if (!NarroApp::HasPermissionForThisLang('Can edit project', $this->objNarroProject->ProjectId))
                NarroApp::Redirect(NarroLink::ProjectList());

            if ($this->objNarroProject->ProjectId)
                $this->pnlBreadcrumb->setElements(NarroLink::ProjectList(t('Projects')), NarroLink::ProjectTextList($this->objNarroProject->ProjectId, null, null, null, $this->objNarroProject->ProjectName), t('Edit'));
            else
                $this->pnlBreadcrumb->setElements(NarroLink::ProjectList(t('Projects')), t('Add'));

            $this->pnlTab = new QTabPanel($this);
            $this->pnlTab->UseAjax = false;

            $this->pnlProjectEdit = new NarroProjectEditPanel($this->objNarroProject, $this->pnlTab);


            $this->pnlTab->addTab(new QPanel($this->pnlTab), t('Import'), NarroLink::ProjectImport($this->objNarroProject->ProjectId));
            $this->pnlTab->addTab(new QPanel($this->pnlTab), t('Export'), NarroLink::ProjectExport($this->objNarroProject->ProjectId));
            if ($this->objNarroProject->ProjectId) {
                $this->pnlTab->addTab($this->pnlProjectEdit, t('Edit'));
                $this->pnlTab->SelectedTab = t('Edit');
            }
            else {
                $this->pnlTab->addTab($this->pnlProjectEdit, t('Add'));
                $this->pnlTab->SelectedTab = t('Add');
            }
        }
    }


    NarroProjectEditForm::Run('NarroProjectEditForm', 'templates/narro_project_edit.tpl.php');
?>
