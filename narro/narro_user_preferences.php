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

    class NarroUserPreferencesPanel extends QPanel {
        protected $lblMessage;
        protected $btnSave;
        protected $btnCancel;
        protected $txtPreviousUrl;

        protected $arrControls;

        public function __construct($objParentObject, $strControlId = null) {
            // Call the Parent
            try {
                parent::__construct($objParentObject, $strControlId);
            } catch (QCallerException $objExc) {
                $objExc->IncrementOffset();
                throw $objExc;
            }

            if (isset($_SERVER['HTTP_REFERER']) && !strstr($_SERVER['HTTP_REFERER'], basename(__FILE__)) && $_SERVER['HTTP_REFERER'] !='')
                $this->txtPreviousUrl = $_SERVER['HTTP_REFERER'];

            $this->lblMessage = new QLabel($this);

            $this->btnSave = new QButton($this);
            $this->btnSave->Text = QApplication::Translate('Save');
            $this->btnSave->PrimaryButton = true;
            $this->btnSave->AddAction(new QClickEvent(), new QServerControlAction($this, 'btnSave_Click'));

            $this->btnCancel = new QButton($this);
            $this->btnCancel->Text = QApplication::Translate('Cancel');
            $this->btnCancel->AddAction(new QClickEvent(), new QServerControlAction($this, 'btnCancel_Click'));

        }

        protected function GetControlHtml() {
            $strOutput = $this->lblMessage->Render(false) . '<br /><table style="border: 1px solid #DDDDDD" cellspacing="0">';

            foreach(QApplication::$arrPreferences as $strName=>$arrPref) {
                switch($arrPref['type']) {
                    case 'number':
                            $txtNumber = new QIntegerTextBox($this);
                            $txtNumber->Name = $strName;
                            $txtNumber->Minimum = 5;
                            $txtNumber->Maximum = 100;
                            $txtNumber->MaxLength = 3;
                            $txtNumber->Width = 50;
                            $txtNumber->Text = QApplication::$objUser->getPreferenceValueByName($strName);
                            $strOutput .= sprintf('<tr class="datagrid_row datagrid_even" style="height:40px"><td>%s:</td><td>%s</td><td style="font-size:-1">%s</td></tr>', QApplication::Translate($strName), $txtNumber->RenderWithError(false), QApplication::Translate($arrPref['description']));
                            $this->arrControls[$strName] = $txtNumber;
                            break;
                    case 'text':
                            $txtTextPref = new QTextBox($this);
                            $txtTextPref->Name = $strName;
                            $txtTextPref->Text = QApplication::$objUser->getPreferenceValueByName($strName);
                            $strOutput .= sprintf('<tr class="datagrid_row datagrid_even" style="height:40px"><td>%s:</td><td>%s</td><td style="font-size:-1">%s</td></tr>', QApplication::Translate($strName), $txtTextPref->RenderWithError(false), QApplication::Translate($arrPref['description']));
                            $this->arrControls[$strName] = $txtTextPref;
                            break;
                    case 'option':
                            $lstOption = new QListBox($this);
                            $lstOption->Name = $strName;
                            if ($strName == 'Language') {
                                $arrLanguages = NarroLanguage::LoadAll(QQ::Clause(QQ::OrderBy(QQN::NarroLanguage()->LanguageName)));
                                foreach($arrLanguages as $objLanguage) {
                                    $lstOption->AddItem(QApplication::Translate($objLanguage->LanguageName), $objLanguage->LanguageCode, ($objLanguage->LanguageCode == QApplication::$objUser->getPreferenceValueByName($strName)));
                                }
                            }
                            else
                                foreach($arrPref['values'] as $strValue) {
                                    $lstOption->AddItem(QApplication::Translate($strValue), $strValue, ($strValue == QApplication::$objUser->getPreferenceValueByName($strName)));
                                }
                            $strOutput .= sprintf('<tr class="datagrid_row datagrid_even" style="height:40px"><td>%s:</td><td>%s</td><td style="font-size:-1">%s</td></tr>', QApplication::Translate($strName), $lstOption->RenderWithError(false), QApplication::Translate($arrPref['description']));
                            $this->arrControls[$strName] = $lstOption;
                            break;
                }
            }

            $strOutput .= '<tr><td colspan="3" style="text-align:right">' . $this->btnCancel->Render(false) . ' ' . $this->btnSave->Render(false) . '</td></tr></table>';
            if ($this->txtPreviousUrl)
                $strOutput .= '<p>' . sprintf(QApplication::Translate('Click <a href="%s">here</a> to return to the page you were.'), $this->txtPreviousUrl) . '</p>';
            return $strOutput;
        }

        public function btnSave_Click($strFormId, $strControlId, $strParameter) {
            foreach($this->arrControls as $strName=>$objControl) {
                switch(QApplication::$arrPreferences[$strName]['type']) {
                    case 'number':
                            QApplication::$objUser->setPreferenceValueByName($strName, $objControl->Text);
                            break;
                    case 'text':
                            QApplication::$objUser->setPreferenceValueByName($strName,  $objControl->Text);
                            break;
                    case 'option':
                            QApplication::$objUser->setPreferenceValueByName($strName, $objControl->SelectedValue);
                            break;
                }
            }

            QApplication::$objUser->Data = serialize(QApplication::$objUser->Preferences);

            $_SESSION['objUser'] = QApplication::$objUser;

            /**
             * Don't save the preferences for the anonymous user in the database
             */
            if (QApplication::$objUser->UserId == 0)
                return true;

            try {
                QApplication::$objUser->Save();
                $this->lblMessage->Text = QApplication::Translate('Your preferences were saved successfuly.');
                $this->lblMessage->ForeColor = 'green';
            } catch( Exception $objEx) {
                $this->lblMessage->Text = QApplication::Translate('An error occured while trying to save your preferences.');
                $this->lblMessage->ForeColor = 'red';
            }
        }

        public function btnCancel_Click($strFormId, $strControlId, $strParameter) {
            QApplication::Redirect('narro_project_list.php');
        }

    }

    class NarroUserPreferencesForm extends QForm {
        protected $pnlPreferences;

        protected function Form_Create() {
            $this->pnlPreferences = new NarroUserPreferencesPanel($this);
        }


    }

    NarroUserPreferencesForm::Run('NarroUserPreferencesForm', 'templates/narro_user_preferences.tpl.php');
?>
