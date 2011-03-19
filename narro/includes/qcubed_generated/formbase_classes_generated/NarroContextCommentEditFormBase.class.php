<?php
	/**
	 * This is a quick-and-dirty draft QForm object to do Create, Edit, and Delete functionality
	 * of the NarroContextComment class.  It uses the code-generated
	 * NarroContextCommentMetaControl class, which has meta-methods to help with
	 * easily creating/defining controls to modify the fields of a NarroContextComment columns.
	 *
	 * Any display customizations and presentation-tier logic can be implemented
	 * here by overriding existing or implementing new methods, properties and variables.
	 * 
	 * NOTE: This file is overwritten on any code regenerations.  If you want to make
	 * permanent changes, it is STRONGLY RECOMMENDED to move both narro_context_comment_edit.php AND
	 * narro_context_comment_edit.tpl.php out of this Form Drafts directory.
	 *
	 * @package Narro
	 * @subpackage FormBaseObjects
	 */
	abstract class NarroContextCommentEditFormBase extends QForm {
		// Local instance of the NarroContextCommentMetaControl
		protected $mctNarroContextComment;

		// Controls for NarroContextComment's Data Fields
		protected $lblCommentId;
		protected $lstContext;
		protected $lstUser;
		protected $lstLanguage;
		protected $calCreated;
		protected $calModified;
		protected $txtCommentText;
		protected $txtCommentTextMd5;

		// Other ListBoxes (if applicable) via Unique ReverseReferences and ManyToMany References

		// Other Controls
		protected $btnSave;
		protected $btnDelete;
		protected $btnCancel;

		// Create QForm Event Handlers as Needed

//		protected function Form_Exit() {}
//		protected function Form_Load() {}
//		protected function Form_PreRender() {}

		protected function Form_Run() {
			// Security check for ALLOW_REMOTE_ADMIN
			// To allow access REGARDLESS of ALLOW_REMOTE_ADMIN, simply remove the line below
			QApplication::CheckRemoteAdmin();
		}

		protected function Form_Create() {
			// Use the CreateFromPathInfo shortcut (this can also be done manually using the NarroContextCommentMetaControl constructor)
			// MAKE SURE we specify "$this" as the MetaControl's (and thus all subsequent controls') parent
			$this->mctNarroContextComment = NarroContextCommentMetaControl::CreateFromPathInfo($this);

			// Call MetaControl's methods to create qcontrols based on NarroContextComment's data fields
			$this->lblCommentId = $this->mctNarroContextComment->lblCommentId_Create();
			$this->lstContext = $this->mctNarroContextComment->lstContext_Create();
			$this->lstUser = $this->mctNarroContextComment->lstUser_Create();
			$this->lstLanguage = $this->mctNarroContextComment->lstLanguage_Create();
			$this->calCreated = $this->mctNarroContextComment->calCreated_Create();
			$this->calModified = $this->mctNarroContextComment->calModified_Create();
			$this->txtCommentText = $this->mctNarroContextComment->txtCommentText_Create();
			$this->txtCommentTextMd5 = $this->mctNarroContextComment->txtCommentTextMd5_Create();

			// Create Buttons and Actions on this Form
			$this->btnSave = new QButton($this);
			$this->btnSave->Text = QApplication::Translate('Save');
			$this->btnSave->AddAction(new QClickEvent(), new QAjaxAction('btnSave_Click'));
			$this->btnSave->CausesValidation = true;

			$this->btnCancel = new QButton($this);
			$this->btnCancel->Text = QApplication::Translate('Cancel');
			$this->btnCancel->AddAction(new QClickEvent(), new QAjaxAction('btnCancel_Click'));

			$this->btnDelete = new QButton($this);
			$this->btnDelete->Text = QApplication::Translate('Delete');
			$this->btnDelete->AddAction(new QClickEvent(), new QConfirmAction(QApplication::Translate('Are you SURE you want to DELETE this') . ' ' . QApplication::Translate('NarroContextComment') . '?'));
			$this->btnDelete->AddAction(new QClickEvent(), new QAjaxAction('btnDelete_Click'));
			$this->btnDelete->Visible = $this->mctNarroContextComment->EditMode;
		}

		/**
		 * This Form_Validate event handler allows you to specify any custom Form Validation rules.
		 * It will also Blink() on all invalid controls, as well as Focus() on the top-most invalid control.
		 */
		protected function Form_Validate() {
			// By default, we report that Custom Validations passed
			$blnToReturn = true;

			// Custom Validation Rules
			// TODO: Be sure to set $blnToReturn to false if any custom validation fails!
			

			$blnFocused = false;
			foreach ($this->GetErrorControls() as $objControl) {
				// Set Focus to the top-most invalid control
				if (!$blnFocused) {
					$objControl->Focus();
					$blnFocused = true;
				}

				// Blink on ALL invalid controls
				$objControl->Blink();
			}

			return $blnToReturn;
		}

		// Button Event Handlers

		protected function btnSave_Click($strFormId, $strControlId, $strParameter) {
			// Delegate "Save" processing to the NarroContextCommentMetaControl
			$this->mctNarroContextComment->SaveNarroContextComment();
			$this->RedirectToListPage();
		}

		protected function btnDelete_Click($strFormId, $strControlId, $strParameter) {
			// Delegate "Delete" processing to the NarroContextCommentMetaControl
			$this->mctNarroContextComment->DeleteNarroContextComment();
			$this->RedirectToListPage();
		}

		protected function btnCancel_Click($strFormId, $strControlId, $strParameter) {
			$this->RedirectToListPage();
		}

		// Other Methods

		protected function RedirectToListPage() {
			QApplication::Redirect(__VIRTUAL_DIRECTORY__ . __FORM_DRAFTS__ . '/narro_context_comment_list.php');
		}
	}
?>