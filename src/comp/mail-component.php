<?php

class Mail_Component extends ResourceCalendar_Component {


	public function __construct(&$datas) {
		$this->datas = $datas;
	}




	public function editTableData () {
		$set_data['RCAL_CONFIG_SEND_MAIL_TEXT'] = stripslashes($_POST['rcal_mail_text']);
		$set_data['RCAL_CONFIG_SEND_MAIL_TEXT_ADMIN'] = stripslashes($_POST['rcal_mail_text_admin']);
		$set_data['RCAL_CONFIG_SEND_MAIL_TEXT_COMPLETED'] = stripslashes($_POST['rcal_mail_text_completed']);
		$set_data['RCAL_CONFIG_SEND_MAIL_TEXT_ACCEPTED'] = stripslashes($_POST['rcal_mail_text_accepted']);
		$set_data['RCAL_CONFIG_SEND_MAIL_TEXT_CANCELED'] = stripslashes($_POST['rcal_mail_text_canceled']);
		$set_data['RCAL_CONFIG_SEND_MAIL_SUBJECT'] = stripslashes($_POST['rcal_send_mail_subject']);
		$set_data['RCAL_CONFIG_SEND_MAIL_SUBJECT_ADMIN'] = stripslashes($_POST['rcal_send_mail_subject_admin']);
		$set_data['RCAL_CONFIG_SEND_MAIL_SUBJECT_COMPLETED'] = stripslashes($_POST['rcal_send_mail_subject_completed']);
		$set_data['RCAL_CONFIG_SEND_MAIL_SUBJECT_ACCEPTED'] = stripslashes($_POST['rcal_send_mail_subject_accepted']);
		$set_data['RCAL_CONFIG_SEND_MAIL_SUBJECT_CANCELED'] = stripslashes($_POST['rcal_send_mail_subject_canceled']);
		$set_data['RCAL_CONFIG_SEND_MAIL_FROM'] = stripslashes($_POST['rcal_mail_from']);
		$set_data['RCAL_CONFIG_SEND_MAIL_RETURN_PATH'] = stripslashes($_POST['rcal_mail_returnPath']);
		$set_data['RCAL_CONFIG_SEND_MAIL_TEXT_INFORMATION'] = stripslashes($_POST['rcal_mail_text_information']);
		$set_data['RCAL_CONFIG_SEND_MAIL_SUBJECT_INFORMATION'] = stripslashes($_POST['rcal_information_mail_subject']);
		$set_data['RCAL_CONFIG_SEND_MAIL_BCC'] = stripslashes($_POST['rcal_mail_bcc']);


		return $set_data;

	}


}