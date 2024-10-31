<?php

	require_once(RCAL_PLUGIN_SRC_DIR . 'page/resource-calendar-page.php');


class Confirm_Page extends ResourceCalendar_Page {

	private $reservation_cd = '';
	private $activation_key = '';

	private $datas = null;

	private $error_msg = '';



	public function __construct() {
		parent::__construct(false);
		if ( isset($_GET['P1']) )  {
			$this->reservation_cd = intval($_GET['P1']);
		}
		if (!empty($_GET['P2'])) {
			$this->activation_key = $_GET['P2'];
		}
	}


	public function set_reservation_datas ( $datas ) {
		$this->datas = $datas;
	}
	public function get_reservation_cd (  ) {
		return $this->reservation_cd;
	}

	public function check_request() {
		if ('' == strval($this->reservation_cd) || empty($this->activation_key)  ) {
			$this->error_msg = ResourceCalendar_Component::getMsg('E901',__LINE__);
			return;
		}
		if ( count($this->datas) == 0 )  {
			$this->error_msg = ResourceCalendar_Component::getMsg('E901',__LINE__);
			return;
		}

		if ($this->datas['activate_key'] !== $this->activation_key) {
			$this->error_msg = ResourceCalendar_Component::getMsg('E909',__LINE__);
		}

		$now =  date_i18n("YmdHi");
		if ($this->datas['check_day'] < $now )  {
			$this->error_msg = ResourceCalendar_Component::getMsg('E011',$this->datas['target_day'].' '.$this->datas['time_from']);
		}

	}


	public function show_page() {
		if (!empty($this->error_msg) ) {
			echo '<h1>'.$this->error_msg.'</h1>';
			return;
		}

		wp_enqueue_style('rcal', RCAL_PLUGIN_URL.'css/resource-calendar.css');
?>
<div id="rcal_main" >
		<div id="rcal_confirm_detail">
				<table>
				<tbody>
					<tr><th><?php _e('name',RCAL_DOMAIN); ?></th><td><?php echo $this->datas['name']; ?></td></tr>
					<tr><th><?php _e('reserved day',RCAL_DOMAIN); ?></th><td><?php echo $this->datas['target_day']; ?>&nbsp;<?php echo $this->datas['time_from']; ?>-<?php echo $this->datas['time_to']; ?></td></tr>
					<tr><th><?php _e('reserved menu',RCAL_DOMAIN); ?></th><td><?php echo htmlspecialchars($this->datas['resource_name'],ENT_QUOTES); ?></td></tr>
					<tr><th><?php _e('Remark',RCAL_DOMAIN); ?></th><td><?php echo htmlspecialchars($this->datas['remark'],ENT_QUOTES); ?></td></tr>
					<tr><th><?php _e('status',RCAL_DOMAIN); ?></th><td id="status_name"><?php echo $this->datas['status_name']; ?></td></tr>
				</tbody>
				</table>
		</div>
<?php
		$echo_data_exec = '';
		$echo_data_cancel = '';
		$echo_data_exec_event = '';
		if ($this->datas['status'] == ResourceCalendar_Reservation_Status::TEMPORARY) {
			$echo_data_exec = '<input id="rcal_button_exec" type="button" value="'.__('Create Reservation',RCAL_DOMAIN).'" class="rcal_button"/>';
			$echo_data_cancel = '<input id="rcal_button_cancel" type="button" value="'.__('Cancel Reservation',RCAL_DOMAIN).'" class="rcal_button"/>';
			$echo_data_exec_event = '$j("#rcal_button_exec").click(function(){if (!_checkNow() ) return;if (confirm("'.__('Reservation Completed ?',RCAL_DOMAIN).'") == false) return;fnFixReservation("exec");});';
		}
		elseif ($this->datas['status'] == ResourceCalendar_Reservation_Status::COMPLETE) {
			$echo_data_exec = '';
			$echo_data_cancel = '<input id="rcal_button_cancel" type="button" value="'.__('Cancel Reservation',RCAL_DOMAIN).'" class="rcal_button"/>';
			$echo_data_exec_event = '';
		}
?>
		<div id="rcal_button_div" >
			<?php echo $echo_data_exec; ?>
			<?php echo $echo_data_cancel; ?>
		</div>
</div>
		<script type="text/javascript">
			var $j = jQuery
			$j("#rcal_button_cancel").click(function(){

				if (!_checkNow() ) return;
				if (confirm("<?php _e('Are you sure you want to cancel the reservation?',RCAL_DOMAIN); ?>") == false) return;
				fnFixReservation("cancel");
			});
			<?php echo $echo_data_exec_event; ?>
			function fnFixReservation(action) {
				$j.ajax({
						type: "post"
						,url:  "<?php echo get_bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php?action=rcalconfirm"
						,dataType : "json"
						,data: {
							"type":action
							,"target":"<?php echo $this->reservation_cd; ?>"
							,"P2":"<?php echo $this->activation_key; ?>"
							,"nonce":"<?php echo $this->nonce; ?>"
							,"menu_func":"Confirm_Edit"
						}

						,success: function(data) {
							if (data.status == "Error" ) {
								alert(data.message);
								return false;
							}
							$j("#status_name").text(data.set_data["status_name"]);
							$j("#rcal_button_cancel").val("<?php _e('Cancel Reservation',RCAL_DOMAIN); ?>");
							$j("#rcal_button_exec").remove();
							if (action == "cancel" )  {
								$j("#rcal_button_cancel").remove();
							}
						}
						,error:  function(XMLHttpRequest, textStatus){
							alert (textStatus);
							return false;
						}
				});
			}
			function _checkNow() {
				var target = new Date(<?php echo sprintf("%d,%d,%d,%d,%d,%d",substr($this->datas['check_day'],0,4),substr($this->datas['check_day'],4,2)-1,substr($this->datas['check_day'],6,2), substr($this->datas['check_day'],8,2),substr($this->datas['check_day'],10,2), 59); ?>);
				var now = new Date();
				if(target.getTime() < now.getTime()) {
					alert("<?php echo ResourceCalendar_Component::getMsg('E011',$this->datas['target_day'].' '.$this->datas['time_from']); ?>");
					return false;
				}
				return true;
			}
		</script>
<?php
	}	//show_page
}		//class

