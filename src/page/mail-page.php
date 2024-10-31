<?php

	require_once(RCAL_PLUGIN_SRC_DIR . 'page/resource-calendar-page.php');


class Mail_Page extends ResourceCalendar_Page {

	private $set_items = null;




	public function __construct() {
		parent::__construct();
		$this->set_items = array('send_mail_text_on_mail','mail_from_on_mail','mail_returnPath_on_mail',
		'target_mail_patern','send_mail_subject','information_mail_text_on_mail','information_mail_subject','mail_bcc'
		,'send_mail_text_admin_on_mail','send_mail_subject_admin'
		,'send_mail_text_completed_on_mail','send_mail_subject_completed'
		,'send_mail_text_accepted_on_mail','send_mail_subject_accepted'
		,'send_mail_text_canceled_on_mail','send_mail_subject_canceled'
		);
	}


	public function show_page() {
?>

	<script type="text/javascript" charset="utf-8">

		var $j = jQuery;
		<?php parent::echoClientItem($this->set_items); ?>
		$j(document).ready(function() {
			$j("#rcal_button_div input[type=button]").addClass("rcal_button");
			<?php parent::echoSetItemLabel(false); ?>
			for(index in check_items) {
				if (check_items[index]) {
					var diff = 0;
					var id = check_items[index]["id"];
					$j("#"+id+"_lbl").children(".small").text(check_items[index]["tips"]);
					if ($j("#"+id)[0].tagName.toUpperCase() == "TEXTAREA" ) diff = 5;
					else {
						if ( $j("#"+id).parent().hasClass("config_item_wrap") ) {
							diff = $j("#"+id+"_lbl").outerHeight(true) - $j("#"+id).parent().outerHeight(true);
						}
						else {
							diff = $j("#"+id+"_lbl").outerHeight(true) - $j("#"+id).outerHeight(true);
						}
					}
					if (diff > 0 ) {
						diff += <?php echo parent::INPUT_BOTTOM_MARGIN; ?>+5;
						$j("#"+id).attr("style","margin-bottom: "+diff+"px;");
						$j("#"+id+"_lbl").children(".small").attr("style","text-align:left;");
					}
				}
			}

$j("#rcal_button_update").click(function()	{
				fnClickUpdate();
			});
$j("#rcal_target_mail_patern").change(function()	{

				$j(".rcal_mail_wrap").hide();
				$j("#rcal_mail_warp_"+$j(this).val()).show();

				$j("#rcal_mail_wrap_bcc").hide();
				if ($j(this).val() == "information" ) {
					$j("#rcal_mail_wrap_bcc").show();
				}

			});

			$j("#rcal_send_mail_text").val("<?php echo str_replace(array("\r\n","\r","\n"), '\n', $this->config_datas['RCAL_CONFIG_SEND_MAIL_TEXT']); ?>");
			$j("#rcal_send_mail_text_admin").val("<?php echo str_replace(array("\r\n","\r","\n"), '\n', $this->config_datas['RCAL_CONFIG_SEND_MAIL_TEXT_ADMIN']); ?>");
			$j("#rcal_send_mail_text_completed").val("<?php echo str_replace(array("\r\n","\r","\n"), '\n', $this->config_datas['RCAL_CONFIG_SEND_MAIL_TEXT_COMPLETED']); ?>");
			$j("#rcal_send_mail_text_accepted").val("<?php echo str_replace(array("\r\n","\r","\n"), '\n', $this->config_datas['RCAL_CONFIG_SEND_MAIL_TEXT_ACCEPTED']); ?>");
			$j("#rcal_information_mail_text").val("<?php echo str_replace(array("\r\n","\r","\n"), '\n', $this->config_datas['RCAL_CONFIG_SEND_MAIL_TEXT_INFORMATION']); ?>");
			$j("#rcal_send_mail_text_canceled").val("<?php echo str_replace(array("\r\n","\r","\n"), '\n', $this->config_datas['RCAL_CONFIG_SEND_MAIL_TEXT_CANCELED']); ?>");

			$j("#rcal_send_mail_subject").val("<?php echo $this->config_datas['RCAL_CONFIG_SEND_MAIL_SUBJECT']; ?>");
			$j("#rcal_send_mail_subject_admin").val("<?php echo $this->config_datas['RCAL_CONFIG_SEND_MAIL_SUBJECT_ADMIN']; ?>");
			$j("#rcal_send_mail_subject_completed").val("<?php echo $this->config_datas['RCAL_CONFIG_SEND_MAIL_SUBJECT_COMPLETED']; ?>");
			$j("#rcal_send_mail_subject_accepted").val("<?php echo $this->config_datas['RCAL_CONFIG_SEND_MAIL_SUBJECT_ACCEPTED']; ?>");
			$j("#rcal_information_mail_subject").val("<?php echo $this->config_datas['RCAL_CONFIG_SEND_MAIL_SUBJECT_INFORMATION']; ?>");
			$j("#rcal_send_mail_subject_canceled").val("<?php echo $this->config_datas['RCAL_CONFIG_SEND_MAIL_SUBJECT_CANCELED']; ?>");

			$j("#rcal_mail_from").val("<?php echo $this->config_datas['RCAL_CONFIG_SEND_MAIL_FROM']; ?>");
			$j("#rcal_mail_returnPath").val("<?php echo $this->config_datas['RCAL_CONFIG_SEND_MAIL_RETURN_PATH']; ?>");

			$j("#rcal_mail_bcc").val("<?php echo $this->config_datas['RCAL_CONFIG_SEND_MAIL_BCC']; ?>");

			$j("#rcal_target_mail_patern").val("confirm").change();



		});


		function fnClickUpdate() {
			if ( ! checkItem("rcal_data_detail") ) return false;

			$j.ajax({
				 	type: "post",
					url:  "<?php echo get_bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php?action=rcalmail",
					dataType : "json",
					data: {
						"type":"updated"
						,"rcal_mail_text":$j("#rcal_send_mail_text").val()
						,"rcal_mail_text_admin":$j("#rcal_send_mail_text_admin").val()
						,"rcal_mail_text_completed":$j("#rcal_send_mail_text_completed").val()
						,"rcal_mail_text_accepted":$j("#rcal_send_mail_text_accepted").val()
						,"rcal_mail_text_information":$j("#rcal_information_mail_text").val()
						,"rcal_mail_text_canceled":$j("#rcal_send_mail_text_canceled").val()
						,"rcal_send_mail_subject":$j("#rcal_send_mail_subject").val()
						,"rcal_send_mail_subject_admin":$j("#rcal_send_mail_subject_admin").val()
						,"rcal_send_mail_subject_completed":$j("#rcal_send_mail_subject_completed").val()
						,"rcal_send_mail_subject_accepted":$j("#rcal_send_mail_subject_accepted").val()
						,"rcal_information_mail_subject":$j("#rcal_information_mail_subject").val()
						,"rcal_send_mail_subject_canceled":$j("#rcal_send_mail_subject_canceled").val()
						,"rcal_mail_from":$j("#rcal_mail_from").val()
						,"rcal_mail_returnPath":$j("#rcal_mail_returnPath").val()
						,"rcal_mail_bcc":$j("#rcal_mail_bcc").val()
						,"nonce":"<?php echo $this->nonce; ?>"
						,"menu_func":"Mail_Edit"

					},
					success: function(data) {
						if (data.status == "Error" ) {
							alert(data.message);
							return false;
						}
						else {
							alert(data.message);
							location.reload();
						}
			},
					error:  function(XMLHttpRequest, textStatus){
						alert (textStatus);
						return false;
					}
			 });
		}

		<?php parent::echoCheckClinet(array('chk_required','num','lenmax','chkMail')); ?>

	</script>

	<h2 id="rcal_admin_title"><?php _e('Mail Settings',RCAL_DOMAIN); ?></h2>
	<?php echo parent::echoShortcode(); ?>
<div id="rcal_button_div" >
	<input id="rcal_button_update" type="button" value="<?php _e('update',RCAL_DOMAIN); ?>"/>
	</div>
	<div id="rcal_data_detail" >


		<input type="text" id="rcal_mail_from" />
		<input type="text" id="rcal_mail_returnPath" />
		<select id="rcal_target_mail_patern" >
			<option value="confirm" ><?php _e('Confirmation mail',RCAL_DOMAIN); ?></option>
			<option value="admin" ><?php _e('Confirmation mail (admin)',RCAL_DOMAIN); ?></option>
			<option value="completed" ><?php _e('Reservation complete mail',RCAL_DOMAIN); ?></option>
			<option value="canceled" ><?php _e('Reservation canceled mail',RCAL_DOMAIN); ?></option>
			<option value="accepted" ><?php _e('Reservation receipt mail',RCAL_DOMAIN); ?></option>
			<option value="information" ><?php _e('Notification mail to staff',RCAL_DOMAIN); ?></option>
		</select>
<div id="rcal_mail_explain"  >
<dl>
<dt>(1)<?php _e('Confirmation mail ',RCAL_DOMAIN); ?>
	<dd><?php _e('Mail valid when "Confirmation via user mail" is selected as the "Reservation confirmation method". Content is for the mail sent to the user who made the reservation.',RCAL_DOMAIN); ?>
<dt>(2)<?php _e('Confirmation mail (admin) ',RCAL_DOMAIN); ?>
	<dd><?php _e('Mail valid when "Confirmation by an administrator " is selected as the "Reservation confirmation method". Content is for the mail sent to the person with WordPress administrator privileges.',RCAL_DOMAIN); ?>
<dt>(3)<?php _e('Reservation complete mail ',RCAL_DOMAIN); ?>
	<dd><?php _e('Content is for the mail sent to the user. ',RCAL_DOMAIN); ?>
<dt>(4)<?php _e('Reservation canceled mail ',RCAL_DOMAIN); ?>
	<dd><?php _e('Content is for the mail sent to the user. ',RCAL_DOMAIN); ?>
<dt>(5)<?php _e('Reservation receipt mail ',RCAL_DOMAIN); ?>
	<dd><?php _e('Mail valid when "Confirmation by an administrator " is selected as the "Reservation confirmation method". Content is for the mail sent to the user. ',RCAL_DOMAIN); ?>
<dt>(6)<?php _e('Notification mail to staff ',RCAL_DOMAIN); ?>
	<dd><?php _e('Mail content sent to the address set for "Notification mail recipient". Sent when reservation is registered, updated, or canceled.',RCAL_DOMAIN); ?>

</dl>
</div>
<div id="rcal_mail_wrap_bcc" >
<textarea id="rcal_mail_bcc" ></textarea>
</div>
		<div id="rcal_mail_warp_confirm" class="rcal_mail_wrap" >
			<input id="rcal_send_mail_subject"  />
			<textarea id="rcal_send_mail_text" class="rcal_mail_area" ></textarea>
		</div>
		<div id="rcal_mail_warp_admin" class="rcal_mail_wrap" >
			<input id="rcal_send_mail_subject_admin"  />
			<textarea id="rcal_send_mail_text_admin" class="rcal_mail_area" ></textarea>
		</div>
		<div id="rcal_mail_warp_completed" class="rcal_mail_wrap" >
			<input id="rcal_send_mail_subject_completed"  />
			<textarea id="rcal_send_mail_text_completed" class="rcal_mail_area" ></textarea>
		</div>
		<div id="rcal_mail_warp_canceled" class="rcal_mail_wrap" >
			<input id="rcal_send_mail_subject_canceled"  />
			<textarea id="rcal_send_mail_text_canceled" class="rcal_mail_area" ></textarea>
		</div>
		<div id="rcal_mail_warp_accepted" class="rcal_mail_wrap" >
			<input id="rcal_send_mail_subject_accepted"  />
			<textarea id="rcal_send_mail_text_accepted" class="rcal_mail_area" ></textarea>
		</div>
		<div id="rcal_mail_warp_information" class="rcal_mail_wrap">
			<input id="rcal_information_mail_subject"  />
			<textarea id="rcal_information_mail_text"  class="rcal_mail_area"></textarea>
		</div>




		<div class="spacer"></div>
	</div>


<?php
	}	//show_page
}		//class

