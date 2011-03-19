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

    class NarroProjectExportPanel extends QPanel {
        protected $objNarroProject;
        public $objExportProgress;

        public $pnlLogViewer;
        public $lblExport;
        public $btnKillProcess;

        public $chkCopyUnhandledFiles;
        public $chkCleanDirectory;
        public $lstExportSuggestionType;

        public $btnExport;

        public function __construct($objNarroProject, $objParentObject, $strControlId = null) {
            // Call the Parent
            try {
                parent::__construct($objParentObject, $strControlId);
            } catch (QCallerException $objExc) {
                $objExc->IncrementOffset();
                throw $objExc;
            }

            $this->strTemplate = __NARRO_INCLUDES__ . '/narro/panel/NarroProjectExportPanel.tpl.php';

            $this->objNarroProject = $objNarroProject;

            $this->pnlLogViewer = new NarroLogViewerPanel($this);
            $this->pnlLogViewer->Visible = false;

            $this->lblExport = new QLabel($this);
            $this->lblExport->HtmlEntities = false;
            $strArchiveName = $this->objNarroProject->ProjectName . '-' . QApplication::$TargetLanguage->LanguageCode . '.zip';
            $strExportFile = __IMPORT_PATH__ . '/' . $this->objNarroProject->ProjectId . '/' . $strArchiveName;
            if (file_exists($strExportFile)) {
                $objDateSpan = new QDateTimeSpan(time() - filemtime($strExportFile));
                $this->lblExport->Text = sprintf(t('Link to last export: <a href="%s">%s</a>, exported %s ago'), str_replace(__DOCROOT__, __HTTP_URL__, $strExportFile) , $strArchiveName, $objDateSpan->SimpleDisplay());
            }


            $this->chkCopyUnhandledFiles = new QCheckBox($this);
            $this->chkCopyUnhandledFiles->Name = t('Copy unhandled files');

            $this->chkCleanDirectory = new QCheckBox($this);
            $this->chkCleanDirectory->Name = t('Clean export directory before exporting');

            $this->lstExportSuggestionType = new QListBox($this);
            $this->lstExportSuggestionType->Name = t('Export translations using') . ':';
            $this->lstExportSuggestionType->AddItem(t('Approved suggestion'), 1);
            $this->lstExportSuggestionType->AddItem(t('Approved, then most voted suggestion'), 2);
            $this->lstExportSuggestionType->AddItem(t('Approved, then most recent suggestion'), 3);
            $this->lstExportSuggestionType->AddItem(t('Approved, then most voted and then most recent suggestion'), 4);
            $this->lstExportSuggestionType->AddItem(t('Approved, then my suggestion'), 5);

            $this->objExportProgress = new NarroTranslationProgressBar($this);
            $this->objExportProgress->Total = 100;
            $this->objExportProgress->Visible = false;

            $this->btnKillProcess = new QButton($this);
            $this->btnKillProcess->Text = 'Kill process';
            if (QApplication::$UseAjax)
                $this->btnKillProcess->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'btnKillProcess_Click'));
            else
                $this->btnKillProcess->AddAction(new QClickEvent(), new QServerControlAction($this, 'btnKillProcess_Click'));

            $this->btnExport = new QButton($this);
            $this->btnExport->Text = t('Export');
            if (QApplication::$UseAjax)
                $this->btnExport->AddAction(new QClickEvent(), new QAjaxControlAction($this, 'btnExport_Click'));
            else
                $this->btnExport->AddAction(new QClickEvent(), new QServerControlAction($this, 'btnExport_Click'));

            if (NarroUtils::IsProcessRunning('export', $this->objNarroProject->ProjectId)) {
                $this->btnExport->Visible = false;
                $this->objExportProgress->Visible = true;
                $this->objExportProgress->Translated = NarroProgress::GetProgress($this->objNarroProject->ProjectId, 'export');
                QApplication::ExecuteJavaScript(sprintf('lastExportId = setInterval("qcodo.postAjax(\'%s\', \'%s\', \'QClickEvent\', \'1\');", %d);', $this->Form->FormId, $this->btnExport->ControlId, 2000));
            }

            $this->btnKillProcess->Visible = QApplication::HasPermission('Administrator', $this->objNarroProject->ProjectId, QApplication::$TargetLanguage->LanguageCode) && !$this->btnExport->Visible;
        }

        public function btnExport_Click($strFormId, $strControlId, $strParameter) {
            if (!QApplication::HasPermissionForThisLang('Can export project', $this->objNarroProject->ProjectId))
                return false;

            $strProcLogFile = __TMP_PATH__ . '/' . $this->objNarroProject->ProjectId . '-' . QApplication::$TargetLanguage->LanguageCode . '-export-process.log';

            $this->pnlLogViewer->LogFile = QApplication::$LogFile;

            if ($strParameter == 1) {
                if (NarroUtils::IsProcessRunning('export', $this->objNarroProject->ProjectId)) {
                    $this->objExportProgress->Translated = NarroProgress::GetProgress($this->objNarroProject->ProjectId, 'export');
                    $this->objExportProgress->MarkAsModified();
                }
                else {

                    $this->lblExport->Text = t('Export finished.');

                    if (QApplication::$UseAjax)
                        QApplication::ExecuteJavaScript('if (typeof lastExportId != \'undefined\') clearInterval(lastExportId)');

                    if (file_exists($strProcLogFile) && filesize($strProcLogFile))
                        QApplication::LogInfo(sprintf('There are messages from the background process: %s', file_get_contents($strProcLogFile)));

                    $this->lblExport->Visible = true;
                    $this->btnExport->Visible = true;
                    $this->btnKillProcess->Visible = false;
                    $this->objExportProgress->Translated = 0;
                    $this->objExportProgress->Visible = false;

                    QApplication::$PluginHandler->DisplayExportMessage($this->objNarroProject);

                    if (is_array(QApplication::$PluginHandler->PluginReturnValues))
                        foreach(QApplication::$PluginHandler->PluginReturnValues as $strPluginName=>$mixReturnValue) {
                            if (count($mixReturnValue) == 2 && $mixReturnValue[0] instanceof NarroProject && is_string($mixReturnValue[1]) && $mixReturnValue[1] != '') {
                                $this->lblExport->Text .= sprintf('<br /><span class="info"><b>%s</b>: %s</span>', $strPluginName, nl2br($mixReturnValue[1]));
                            }
                        }

                    $this->pnlLogViewer->MarkAsModified();
                }
            }
            elseif ($strParameter == 2 || !function_exists('proc_open')) {
                set_time_limit(0);

                if ($this->chkCleanDirectory->Checked)
                    NarroUtils::RecursiveDelete($this->objNarroProject->DefaultTranslationPath .'/*');

                $objNarroImporter = new NarroProjectImporter();

                /**
                 * Get boolean options
                 */
                $objNarroImporter->CopyUnhandledFiles = $this->chkCopyUnhandledFiles->Checked;
                $objNarroImporter->ExportedSuggestion = $this->lstExportSuggestionType->SelectedValue;
                $objNarroImporter->Project = $this->objNarroProject;
                $objNarroImporter->User = NarroUser::LoadAnonymousUser();
                $objNarroImporter->TargetLanguage = QApplication::$TargetLanguage;
                $objNarroImporter->SourceLanguage = NarroLanguage::LoadByLanguageCode(NarroLanguage::SOURCE_LANGUAGE_CODE);
                try {
                    $objNarroImporter->TranslationPath = $this->objNarroProject->DefaultTranslationPath;
                    $objNarroImporter->TemplatePath = $this->objNarroProject->DefaultTemplatePath;
                }
                catch (Exception $objEx) {
                    QApplication::LogError(sprintf('An error occurred during export: %s', $objEx->getMessage()));
                    $this->lblExport->Text = t('Export failed.');
                }

                try {
                    $objNarroImporter->ExportProject();
                }
                catch (Exception $objEx) {
                    QApplication::LogError(sprintf('An error occurred during export: %s', $objEx->getMessage()));
                    $this->lblExport->Text = t('Export failed.');
                }

                $this->lblExport->Visible = true;
                $this->btnExport->Visible = true;
                $this->btnKillProcess->Visible = false;
                $this->objExportProgress->Visible = false;

                $this->pnlLogViewer->MarkAsModified();

            }
            else {
                QApplication::ClearLog();
                if ($this->chkCleanDirectory->Checked)
                    NarroUtils::RecursiveDelete($this->objNarroProject->DefaultTranslationPath .'/*');

                $this->btnExport->Visible = false;
                $this->btnKillProcess->Visible = $this->btnKillProcess->Visible = QApplication::HasPermission('Administrator',$this->objNarroProject,QApplication::$TargetLanguage->LanguageCode);
                $this->objExportProgress->Visible = true;
                $this->objExportProgress->Translated = 0;
                $this->lblExport->Text = '';
                try {
                    $strCommand = sprintf(
                        '%s %s --export --project %d --user %d --template-lang %s --translation-lang %s --template-directory "%s" --translation-directory "%s" --exported-suggestion %d %s',
                        __PHP_CLI_PATH__,
                        escapeshellarg('includes/narro/importer/narro-cli.php'),
                        $this->objNarroProject->ProjectId,
                        QApplication::$User->UserId,
                        NarroLanguage::SOURCE_LANGUAGE_CODE,
                        QApplication::$TargetLanguage->LanguageCode,
                        $this->objNarroProject->DefaultTemplatePath,
                        $this->objNarroProject->DefaultTranslationPath,
                        $this->lstExportSuggestionType->SelectedValue,
                        (($this->chkCopyUnhandledFiles->Checked)?'--copy-unhandled-files ':'')
                    );
                }
                catch (Exception $objEx) {
                    QApplication::LogError(sprintf('An error occurred during export: %s', $objEx->getMessage()));
                    $this->lblExport->Text = t('Export failed.');

                    $this->lblExport->Visible = true;
                    $this->btnExport->Visible = true;
                    $this->btnKillProcess->Visible = false;
                    $this->objExportProgress->Translated = 0;
                    $this->objExportProgress->Visible = false;

                    $this->pnlLogViewer->MarkAsModified();
                    return false;
                }


                if (file_exists($strProcLogFile) && is_writable($strProcLogFile))
                    unlink($strProcLogFile);

                $mixProcess = proc_open("$strCommand &", array(2 => array("file", $strProcLogFile, 'a')), $foo);

                if ($mixProcess) {
                    if (QApplication::$UseAjax)
                        QApplication::ExecuteJavaScript(sprintf('lastExportId = setInterval("qc.pA(\'%s\', \'%s\', \'QClickEvent\', \'1\')", %d);', $strFormId, $strControlId, 2000));
                    else
                        $this->btnExport_Click($strFormId, $strControlId, 1);
                }
                else {
                    $this->objExportProgress->Visible = false;
                    QApplication::LogError('Failed to launch a background process, there will be no progress displayed, and it might take a while, please wait for more messages');
                    $this->pnlLogViewer->MarkAsModified();
                    /**
                     * try exporting without launching a background process
                     */
                    if (QApplication::$UseAjax)
                        QApplication::ExecuteJavaScript(sprintf('lastExportId = setTimeout("qc.pA(\'%s\', \'%s\', \'QClickEvent\', \'2\')", %d);', $strFormId, $strControlId, 2000));
                    else
                        $this->btnExport_Click($strFormId, $strControlId, 2);
                }
            }
        }

        public function btnKillProcess_Click($strFormId, $strControlId, $strParameter) {
            $strProcLogFile = __TMP_PATH__ . '/' . $this->objNarroProject->ProjectId . '-' . QApplication::$TargetLanguage->LanguageCode . '-export-process.log';
            $strProcPidFile = __TMP_PATH__ . '/' . $this->objNarroProject->ProjectId . '-' . QApplication::$TargetLanguage->LanguageCode . '-export-process.pid';

            if (!file_exists($strProcPidFile)) {
                QApplication::LogError('Could not find a pid file for the background process.');
                $this->pnlLogViewer->MarkAsModified();
                return false;
            }

            $intPid = file_get_contents($strProcPidFile);

            if (is_numeric(trim($intPid))) {

                $mixProcess = proc_open(sprintf('kill -9 %d', $intPid), array(2 => array("file", $strProcLogFile, 'a')), $foo);

                if ($mixProcess) {
                    proc_close($mixProcess);
                    QApplication::LogInfo('Process killed');
                }
                else {
                    QApplication::LogInfo('Failed to kill process');
                }

                if (file_exists($strProcLogFile) && filesize($strProcLogFile))
                    QApplication::LogInfo(sprintf('There are messages from the background process: %s', file_get_contents($strProcLogFile)));

                $this->pnlLogViewer->MarkAsModified();
            }

        }

    }