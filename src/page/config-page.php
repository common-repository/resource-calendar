<?php

	require_once(RCAL_PLUGIN_SRC_DIR . 'page/resource-calendar-page.php');


class Config_Page extends ResourceCalendar_Page {


	private $set_items = null;


	function __construct() {
		parent::__construct();
		$this->set_items =
			array(
				'before_day','after_day','config_show_detail_msg','reserve_deadline'
				,'open_time','close_time','time_step','closed_day_check','sp_date'
				,'confirm_style','enable_reservation','config_name','address','tel'
				,'mail','resource_name','cal_size','config_use_session'
				,'config_use_submenu','config_require');
	}


	public function show_page() {
?>

<script type="text/javascript">
		var $j = jQuery
		var is_show_detail = true;
<?php
		//曜日;from;to,曜日;from;to,曜日;from;to
		$holidays_data = explode(',',$this->config_datas["RCAL_CONFIG_CLOSED"]);
		$holidays_arr = array();
		$holidays_detail_arr = array();

		foreach($holidays_data  as $k1 => $d1 ) {
			$splitdata = explode(';',$d1);
			if (count($splitdata) == 3 ) {
				$holidays_arr[] = $splitdata[0];
				$holidays_detail_arr[] = $splitdata[1].";".$splitdata[2];
			}
		}
		$holidays =  implode(',',$holidays_arr);
		$holidays_datail = implode(',',$holidays_detail_arr);

?>
		var save_closed = "<?php echo $holidays; ?>";
		var save_closed_detail = "<?php echo $holidays_datail; ?>";


		var target;

		<?php parent::echoClientItem($this->set_items); ?>
		<?php parent::set_datepicker_date(); ?>

		$j(document).ready(function() {

			$j("#rcal_button_div input[type=button]").addClass("rcal_button");
			<?php parent::echoSetItemLabel(); ?>
			<?php parent::echoClosedDetail($holidays,"rcal_closed_day"); ?>

			fnDetailInit();

			<?php  parent::set_datepickerDefault(); ?>
			<?php  parent::set_datepicker("rcal_sp_date",true,$this->config_datas["RCAL_CONFIG_CLOSED"]); ?>
			$j("#rcal_button_sp_date_insert").click(function(){
				res = fnClickAddRow('inserted')
				if (res !== false) {
					$j(target.fnSettings().aoData).each(function (){
						$j(this.nTr).removeClass("row_selected");
					});
				}

			});

			$j("#rcal_button_update").click(function(){
				fnClickUpdate();


			});

			$j("#rcal_closed_day_check input[type=checkbox]").click(function(){
				var tmp = new Array();
				$j("#rcal_closed_day_check input[type=checkbox]").each(function (){
					if ( $j(this).is(":checked") ) {
						tmp.push( $j(this).val() );
					}
				});
				save_closed = tmp.join(",");
			});


			target = $j("#rcal_lists").dataTable({
				"sAjaxSource": "<?php echo get_bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php?action=rcalconfig",
				<?php parent::echoDataTableLang(); ?>
				<?php parent::echoTableItem("",true); ?>
					 { "mData":"target_date","sTitle": "<?php _e('Date',RCAL_DOMAIN); ?>","bSearchable": true,"bSortable": true, "sWidth":"<?php echo ResourceCalendar_Page::MIDDLE_WIDTH; ?>" }
					,{ "mData":"status_title","sTitle": "<?php _e('Irregular business days and holidays',RCAL_DOMAIN); ?>","bSearchable": true,"bSortable": true }
				],
				"fnServerParams": function ( aoData ) {
				  aoData.push( { "name": "menu_func","value":"Config_Init" } );
				  aoData.push( { "name": "target_year","value":"<?php echo date_i18n('Y'); ?>" } );
				},
				"fnDrawCallback": function () {
					$j("#rcal_lists  tbody .rcal_select").click(function(event) {
						fnSelectRow(this);
					});
				},
<?php	//iDisplayIndexFullがデータ上のindexでidisplayIndexがページ上のindexとなる　?>
		//aDataが実際のデータで、nRowがTrオブジェクト
				fnRowCallback: function( nRow, aData, iDisplayIndex, iDataIndex ) {
					<?php  parent::echoDataTableSelecter("target_date",false); ?>
					element.append(del_box);
				}
			});

			<?php if ( $this->config_datas['RCAL_CONFIG_USE_SESSION_ID'] == ResourceCalendar_Config::USE_SESSION ) $set_boolean = 'true';
					else $set_boolean = 'false'; ?>
			$j("#rcal_config_is_use_session").attr("checked",<?php echo $set_boolean; ?>);

			<?php if ( $this->config_datas['RCAL_CONFIG_USE_SUBMENU'] == ResourceCalendar_Config::USE_SUBMENU ) $set_boolean = 'true';
					else $set_boolean = 'false'; ?>
			$j("#rcal_config_is_use_submenu").attr("checked",<?php echo $set_boolean; ?>);


		});

		function fnClickDeleteRow(target_col) {
			var position = target.fnGetPosition( target_col );
			var setData = target.fnSettings();
			var target_date = setData['aoData'][position[0]]['_aData']['target_date'];
			 $j.ajax({
				 	type: "post",
					url:  "<?php echo get_bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php?action=rcalconfig",
					dataType : "json",
					data: 	{
						"type":"deleted",
						"rcal_sp_date":target_date,
						"nonce":"<?php echo $this->nonce; ?>",
						"menu_func":"Config_Edit"

					},
					success: function(data) {
						if (data && data.status == "Error" ) {
							alert(data.message);
						}
						else {
							var rest = target.fnDeleteRow( position[0] );
						}
					},
					error:  function(XMLHttpRequest, textStatus){
						alert (textStatus);
					}
			 });
		}


		function fnClickAddRow() {
			var sts = checkItem("rcal_multi_item_wrap");
<?php //ここはクローム対応 ?>
			$j("#rcal_sp_date").attr("style","width:100px;margin-right:0px;" );

			if ( ! sts  ) return false;
			var in_date =  $j("#rcal_sp_date").val() ;
			<?php //in_dateはYYYYMMDD形式で戻る ?>
			if ( in_date === false ) return;

			$j.ajax({
				 	type: "post",
					url:  "<?php echo get_bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php?action=rcalconfig",
					dataType : "json",
					data: {
						"type":"inserted",
						"rcal_sp_date":in_date,
						"rcal_status":$j("input[name=\"sp_date_radio\"]:checked").val(),
						"nonce":"<?php echo $this->nonce; ?>",
						"menu_func":"Config_Edit"

					},

					success: function(data) {
						if (data.status == "Error" ) {
							alert(data.message);
							return false;
						}
						else {
							$j("#rcal_sp_date").val("");
							target.fnAddData( data.set_data );

						}
					},
					error:  function(XMLHttpRequest, textStatus){
						alert (textStatus);
						return false;
					}
			 });
		}

		<?php parent::echoTime25Check(); ?>

		function fnClickUpdate() {
<?php			//sp_dateはここではチェックしない。他の画面とはちとちがう ?>
			if ( ! checkItem("rcal_data_detail","rcal_sp_date,rcal_config_deadline_time_unit") ) return false;

			var op = $j("#rcal_open_time").val();
			if (!_fnCheckTimeStep(+$j("#rcal_time_step").val(),op.slice(-2) ) ) return false;
			var cl = $j("#rcal_close_time").val();
			if (!_fnCheckTimeStep(+$j("#rcal_time_step").val(),cl.slice(-2) ) ) return false;

			var set_deadline = $j("#rcal_reserve_deadline").val();

			if ( $j("#rcal_config_deadline_time_unit").val() == <?php echo ResourceCalendar_Config::DEFALUT_RESERVE_DEADLINE_UNIT_DAY; ?> ) {
				set_deadline = set_deadline * 24 * 60;
			}
			else if ($j("#rcal_config_deadline_time_unit").val() == <?php echo ResourceCalendar_Config::DEFALUT_RESERVE_DEADLINE_UNIT_HOUR; ?> ) {
				set_deadline = set_deadline * 60;
			}

			var required_array = new Array();
			if ($j("#rcal_required_name").prop('checked') ) {
				required_array.push("rcal_name");
			}
			if ($j("#rcal_required_tel").prop('checked') ) {
				required_array.push("rcal_tel");
			}
			if ($j("#rcal_required_mail").prop('checked') ) {
				required_array.push("rcal_mail");
			}
			set_required = required_array.join(",");

			$j.ajax({
				 	type: "post",
					url:  "<?php echo get_bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php?action=rcalconfig",
					dataType : "json",
					data: {
						"type":"updated"
						,"rcal_config_show_detail_msg":$j("#rcal_config_is_show_detail_msg").attr("checked")
						,"rcal_config_before_day":$j("#rcal_before_day").val()
						,"rcal_config_after_day":$j("#rcal_after_day").val()
						,"rcal_cal_size":$j("#rcal_cal_size").val()
						,"rcal_config_reserve_deadline":set_deadline
						,"rcal_open_time":$j.trim($j("#rcal_open_time").val())
						,"rcal_close_time":$j.trim($j("#rcal_close_time").val())
						,"rcal_closed_day_check":save_closed
						,"rcal_closed_day_detail":save_closed_detail
						,"rcal_time_step":$j("#rcal_time_step").val()
						,"rcal_confirm_style":$j("#rcal_confirm_style").val()
						,"rcal_enable_reservation":$j("#rcal_enable_reservation").val()
						,"rcal_name":$j("#rcal_name").val()
						,"rcal_resource_name":$j("#rcal_resource_name").val()
						,"rcal_address":$j("#rcal_address").val()
						,"rcal_tel":$j("#rcal_tel").val()
						,"rcal_mail":$j("#rcal_mail").val()
						,"rcal_config_use_session":$j("#rcal_config_is_use_session").attr("checked")
						,"rcal_config_use_submenu":$j("#rcal_config_is_use_submenu").attr("checked")
						,"rcal_config_required":set_required
						,"nonce":"<?php echo $this->nonce; ?>"
						,"menu_func":"Config_Edit"
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




		function fnDetailInit() {
			$j("#rcal_data_detail input[type=\"text\"]").val("");
			$j("#rcal_open_time").val("<?php echo ResourceCalendar_Component::formatTime($this->config_datas['RCAL_CONFIG_OPEN_TIME']); ?>");
			$j("#rcal_close_time").val("<?php echo ResourceCalendar_Component::formatTime($this->config_datas['RCAL_CONFIG_CLOSE_TIME']); ?>");
			$j("#rcal_time_step").val("<?php echo $this->config_datas['RCAL_CONFIG_TIME_STEP']; ?>");
			<?php if ( $this->config_datas['RCAL_CONFIG_SHOW_DETAIL_MSG'] == ResourceCalendar_Config::DETAIL_MSG_OK ) $set_boolean = 'true';
					else $set_boolean = 'false'; ?>
			$j("#rcal_config_is_show_detail_msg").attr("checked",<?php echo $set_boolean; ?>);

			$j("#rcal_before_day").val("<?php echo $this->config_datas['RCAL_CONFIG_BEFORE_DAY']; ?>");
			$j("#rcal_after_day").val("<?php echo $this->config_datas['RCAL_CONFIG_AFTER_DAY']; ?>");
			$j("#rcal_cal_size").val("<?php echo $this->config_datas['RCAL_CONFIG_CAL_SIZE']; ?>");
			<?php
				//
				$setMinutes = $this->config_datas['RCAL_CONFIG_RESERVE_DEADLINE'];
				$setIndex = ResourceCalendar_Config::DEFALUT_RESERVE_DEADLINE_UNIT_MIN;
				if ($this->config_datas['RCAL_CONFIG_RESERVE_DEADLINE'] % (60 * 24 ) == 0 ) {
					$setMinutes = $this->config_datas['RCAL_CONFIG_RESERVE_DEADLINE'] / (60 * 24);
					$setIndex = ResourceCalendar_Config::DEFALUT_RESERVE_DEADLINE_UNIT_DAY;
				}
				elseif  ($this->config_datas['RCAL_CONFIG_RESERVE_DEADLINE'] % 60  == 0 ) {
					$setMinutes = $this->config_datas['RCAL_CONFIG_RESERVE_DEADLINE'] / 60;
					$setIndex = ResourceCalendar_Config::DEFALUT_RESERVE_DEADLINE_UNIT_HOUR;
				}
			?>
			$j("#rcal_reserve_deadline").val(<?php echo $setMinutes; ?>);
			$j("#rcal_config_deadline_time_unit").val(<?php echo $setIndex; ?>);
			$j("#rcal_confirm_style").val(<?php echo$this->config_datas['RCAL_CONFIG_CONFIRM_STYLE']; ?>);
			$j("#rcal_enable_reservation").val(<?php echo$this->config_datas['RCAL_CONFIG_ENABLE_RESERVATION']; ?>);


			$j(".rcal_holiday_detail_wrap").hide();
			$j("#rcal_closed_day_check input").attr("checked",false);

			var tmp = save_closed.split(",");
			var tmp_detail = save_closed_detail.split(",");
			for (var i=0; i < tmp.length; i++) {
				$j("#rcal_closed_day_"+tmp[i]).attr("checked",true);
				var tmp_time_array = Array();
				if (tmp_detail[i]) {
					tmp_time_array = tmp_detail[i].split(";");
				}
				else {
					tmp_time_array[0] = "<?php echo $this->config_datas['RCAL_CONFIG_OPEN_TIME']; ?>";
					tmp_time_array[1] = "<?php echo $this->config_datas['RCAL_CONFIG_CLOSE_TIME']; ?>";
				}
				$j("#rcal_closed_day_"+tmp[i]+"_fr").val(tmp_time_array[0].slice(0,2)+":"+tmp_time_array[0].slice(-2));
				$j("#rcal_closed_day_"+tmp[i]+"_to").val(tmp_time_array[1].slice(0,2)+":"+tmp_time_array[1].slice(-2));
				$j("#rcal_holiday_detail_wrap_"+tmp[i]).show();

			}


			$j("#rcal_time_step").val("<?php echo $this->config_datas['RCAL_CONFIG_TIME_STEP']; ?>");

			$j("#rcal_name").val("<?php echo $this->config_datas['RCAL_CONFIG_NAME']; ?>");
			$j("#rcal_resource_name").val("<?php echo $this->config_datas['RCAL_CONFIG_RESOURCE_NAME']; ?>");
			$j("#rcal_address").val("<?php echo $this->config_datas['RCAL_CONFIG_ADDRESS']; ?>");
			$j("#rcal_tel").val("<?php echo $this->config_datas['RCAL_CONFIG_TEL']; ?>");
			$j("#rcal_mail").val("<?php echo $this->config_datas['RCAL_CONFIG_MAIL']; ?>");


			$j("#rcal_button_update").attr("disabled", false);
			$j("#rcal_sp_date_radio_close").attr("checked","checked");
			$j("#rcal_sp_date").val("");

			<?php
				$name_required = "true";
				$tel_required = "true";
				$mail_required = "true";
				$require_array = explode(',',$this->config_datas['RCAL_CONFIG_REQUIRED']);
				if ( in_array('rcal_name',$require_array)  == false)  {
					$name_required = "false";
				}
				if ( in_array('rcal_tel',$require_array) == false ) {
					$tel_required = "false";
				}
				if (( in_array('rcal_mail',$require_array) == false  ) &&
		 		    ($this->config_datas['RCAL_CONFIG_ENABLE_RESERVATION'] != ResourceCalendar_Config::CONFIRM_BY_MAIL )) {
						$mail_required = "false";
		 		}

			?>
			$j("#rcal_required_name").prop("checked",<?php echo $name_required; ?>);
			$j("#rcal_required_tel").prop("checked",<?php echo $tel_required; ?>);
			$j("#rcal_required_mail").prop("checked",<?php echo $mail_required; ?>);

			<?php parent::echo_clear_error(); ?>


		}

		<?php parent::echoCheckClinet(array('chk_required','chkTime','chkTime2','lenmax','range','chkDate','num')); ?>
		<?php //parent::echoClosedDetailCheck(); ?>
		<?php parent::echoTextDateReplace(); ?>
		<?php parent::echoDayFormat(); ?>

	</script>

	<h2 id="rcal_admin_title"><?php echo __('Environment settings',RCAL_DOMAIN); ?></h2>
	<?php echo parent::echoShortcode(); ?>
	<div id="rcal_button_div" >
		<input id="rcal_button_update" type="button" value="<?php _e('Update',RCAL_DOMAIN); ?>" />
	</div>
	<div id="rcal_data_detail" >
    	<input id="rcal_resource_name" type="text" />
    	<input id="rcal_name" type="text" />
    	<input id="rcal_address" type="text" />
    	<input id="rcal_tel" type="text" />
    	<input id="rcal_mail" type="text" />
		<input id="rcal_open_time" type="text"   />
		<input id="rcal_close_time" type="text"   />
		<div id="rcal_setting_data_wrap" >
		<?php parent::echoClosedCheck($holidays,"rcal_closed_day"); ?>
		</div>
		<?php parent::echoTimeStepSelect('rcal_time_step'); ?>
		<input id="rcal_config_is_show_detail_msg" type="checkbox"  class="rcal_radio" value="<?php echo ResourceCalendar_Config::DETAIL_MSG_OK; ?>" />
		<input type="text" id="rcal_before_day" />
		<input type="text" id="rcal_after_day" />
		<input type="text" id="rcal_cal_size" />


		<div id="rcal_config_enble_wrap" class="config_item_wrap" >
			<select id="rcal_enable_reservation" >
				<option value="<?php echo ResourceCalendar_Config::USER_ANYONE; ?>" ><?php _e('Anyone',RCAL_DOMAIN); ?></option>
				<option value="<?php echo ResourceCalendar_Config::USER_REGISTERED; ?>"  ><?php _e('Registered user',RCAL_DOMAIN); ?></option>
			</select>
		</div>

		<div id="rcal_config_confirm_wrap" class="config_item_wrap" >
			<select id="rcal_confirm_style"  >
				<option value="<?php echo ResourceCalendar_Config::CONFIRM_BY_ADMIN; ?>" ><?php _e('Confirmation by an administrator',RCAL_DOMAIN); ?></option>
				<option value="<?php echo ResourceCalendar_Config::CONFIRM_NO; ?>"  ><?php _e('No confirm',RCAL_DOMAIN); ?></option>
				<option value="<?php echo ResourceCalendar_Config::CONFIRM_BY_MAIL; ?>" ><?php _e('Confirmation via user mail',RCAL_DOMAIN); ?></option>
			</select>
		</div>


		<div id="rcal_config_deadline_wrap" class="config_item_wrap" >
			<input type="text" id="rcal_reserve_deadline"  />
			<select id="rcal_config_deadline_time_unit" class="rcal_middle_width_no_margin" >
				<option value="<?php echo ResourceCalendar_Config::DEFALUT_RESERVE_DEADLINE_UNIT_MIN; ?>"  ><?php _e('Minute',RCAL_DOMAIN); ?></option>
				<option value="<?php echo ResourceCalendar_Config::DEFALUT_RESERVE_DEADLINE_UNIT_HOUR; ?>" ><?php _e('Hour',RCAL_DOMAIN); ?></option>
				<option value="<?php echo ResourceCalendar_Config::DEFALUT_RESERVE_DEADLINE_UNIT_DAY; ?>" ><?php _e('Day',RCAL_DOMAIN); ?></option>
			</select>

		</div>



		<div id="rcal_multi_item_wrap" >

			<input type="text" id="rcal_sp_date" class="rcal_middle_width_no_margin"  />
			<label class="rcal_inner_label" ><INPUT type="radio"  id="rcal_sp_date_radio_open"  name="sp_date_radio" class="rcal_radio"  value="<?php echo ResourceCalendar_Status::OPEN; ?>"><?php _e('Business day',RCAL_DOMAIN); ?></label>
			<label class="rcal_inner_label"><INPUT type="radio" id="rcal_sp_date_radio_close"  name="sp_date_radio"  class="rcal_radio" value="<?php echo ResourceCalendar_Status::CLOSE; ?>"><?php _e('Special holiday',RCAL_DOMAIN); ?></label>
			<input id="rcal_button_sp_date_insert" type="button" class="rcal_button" value="<?php _e('Add',RCAL_DOMAIN); ?>" style="width:50px;margin-right:0px;"/>
		</div>
		<div id="rcal_config_is_use_session_wrap" class="config_item_wrap" >
			<input id="rcal_config_is_use_session" type="checkbox" class="rcal_radio" value="<?php echo ResourceCalendar_Config::USE_SESSION; ?>" />
		</div>
		<div id="rcal_config_is_use_submenu_wrap" class="config_item_wrap" >
			<input id="rcal_config_is_use_submenu" type="checkbox"  class="rcal_radio" value="<?php echo ResourceCalendar_Config::USE_SUBMENU; ?>" />
		</div>
		<div id="rcal_config_set_require_wrap" class="config_item_wrap" >
			<span id="rcal_config_require_dummy" ></span>
			<label class="rcal_inner_label" ><INPUT type="checkbox"  id="rcal_required_name"  class="rcal_radio"  value="<?php echo ResourceCalendar_Status::CHECKED; ?>"><?php _e('Name',RCAL_DOMAIN); ?></label>
			<label class="rcal_inner_label"><INPUT type="checkbox" id="rcal_required_tel"  class="rcal_radio" value="<?php echo ResourceCalendar_Status::CHECKED; ?>"><?php _e('Tel',RCAL_DOMAIN); ?></label>
			<label class="rcal_inner_label"><INPUT type="checkbox" id="rcal_required_mail"  class="rcal_radio" value="<?php echo ResourceCalendar_Status::CHECKED; ?>"><?php _e('Mail',RCAL_DOMAIN); ?></label>
		</div>
		<div class="spacer"></div>
	</div>
	<table class="flexme" id='rcal_lists'>
	<thead>
	</thead>
	</table>
<?php
	}	//show_page
}		//class

