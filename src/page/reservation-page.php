<?php

	require_once(RCAL_PLUGIN_SRC_DIR . 'page/resource-calendar-page.php');


class Reservation_Page extends ResourceCalendar_Page {

	private $set_items = null;
	private $setting_patern_datas = null;

	private $resource_datas = null;
	private $category_datas = null;

	const SEQ_COLUMN = 5;

	function __construct() {
		parent::__construct();
		$this->set_items = array('search_day','no_edit_customer_name','booking_tel','time_from','time_to','booking_mail','no_edit_remark','booking_starts','target_day_admin',);

	}

	public function set_config_datas($config_datas) {
		parent::set_config_datas($config_datas);
		$now = date_i18n('Ymd');
		$this->valid_from = ResourceCalendar_Component::computeDate(-1*$config_datas['RCAL_CONFIG_BEFORE_DAY'],substr($now,0,4),substr($now,4,2),substr($now,6,2));
		$this->valid_to = ResourceCalendar_Component::computeDate($this->config_datas['RCAL_CONFIG_AFTER_DAY'],substr($now,0,4),(substr($now,4,2)),substr($now,6,2));
	}

	public function set_resource_datas ($datas) {
		$this->resource_datas = $datas;
		if (count($this->resource_datas) === 0 ) {
			throw new ResourceCalendarException(ResourceCalendar_Component::getMsg('E010',__function__.':'.__LINE__ ) );
		}
	}

	public function set_category_datas ($set_data ) {
		$this->category_datas = $set_data;
	}


	public function show_page() {
?>

<script type="text/javascript">
		var $j = jQuery


		var target_day_from = new Date();
		var target_day_to = new Date();
		var operate = "";
		var save_k1 = "";
		var save_position = "";

		var save_result_select_id = "";

		var today = new Date();

		var resource_items = new Array();
		var selected_day;

		var after_day = new Date(<?php echo substr($this->valid_to,0,4).",".(+substr($this->valid_to,5,2)-1).",".substr($this->valid_to,8,2); ?>);
		var save_repeat = <?php echo ResourceCalendar_Repeat::NO_REPEAT; ?>;


		rcalSchedule.config={
				days: []
				,days_detail:[]
				,full_half : []
				,day_full:[<?php _e('"Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"',RCAL_DOMAIN); ?>]
				,day_short:[<?php _e('"Sun","Mon","Tue","Wed","Thu","Fri","Sat"',RCAL_DOMAIN); ?>]
				,resource_holidays:[]
				,open_time:"<?php echo($this->config_datas['RCAL_CONFIG_OPEN_TIME']); ?>"
				,close_time:"<?php echo($this->config_datas['RCAL_CONFIG_CLOSE_TIME']); ?>"
				,step:<?php echo ($this->config_datas['RCAL_CONFIG_TIME_STEP']); ?>
		}
<?php
		if ($this->config_datas['RCAL_CONFIG_USE_SUBMENU'] == ResourceCalendar_Config::USE_SUBMENU) {
			//カテゴリーのパターンを設定する
			echo 'var category_patern = new Object();';
			foreach($this->category_datas as $k1 => $d1 ) {
				echo 'category_patern["i'.$d1['category_cd'].'"]='.$d1['category_patern'].';';
			}
		}
?>
		<?php parent::echoClientItem($this->set_items);  ?>
		<?php parent::set_datepicker_date(); ?>

		$j(document).ready(function() {

			$j(".rcal_patern_original_sel").change(function(){
				var time_fromto = $j(this).val();
				if (time_fromto) {
					var time_fromto_array = time_fromto.split("-");
					setTargetDate(target_day_from,+time_fromto_array[0].substr(0,2),+time_fromto_array[0].substr(3,2));
					setTargetDate(target_day_to,+time_fromto_array[1].substr(0,2),+time_fromto_array[1].substr(3,2));
				}
				else {
					alert("<?php _e('select please',RCAL_DOMAIN); ?>");
				}
			});

			$j("#rcal_target_day").change(function(){
				$j("#rcal_resource_cd").prop("disabled",true);
				$j("#rcal_repeat_onOff").prop("disabled",true);
				if ($j(this).val()  ) {
					var val = $j(this).val(); <?php //チェックの中で使う ?>
					var item_errors = Array();
					<?php //日付のチェック
						$check_contents = parent::setCheckContents();
						echo $check_contents['chkDate'];
					?>
					if (item_errors.length == 0 ) {
						selected_day = new Date($j(this).val());
						setTargetDateYMD(target_day_from,selected_day);
						setTargetDateYMD(target_day_to,selected_day);
						_fnMakeTimeItem();
						$j("#rcal_resource_cd").prop("disabled",false);
						$j("#rcal_resource_cd").prop("selectedIndex", 0).change();
						$j("#rcal_repeat_onOff").prop("disabled",false);
						$j("#rcal_setting_patern_time_wrap").hide();
	}				}
			});

			$j("#rcal_resource_cd").change(function(){
				$j("#rcal_setting_patern_time_wrap").hide();
				$j(".rcal_patern_original").hide();
				var sel = $j(this).val();
				if (sel ) {
					if (resource_items[sel]) {
						$j("#rcal_setting_patern_"+sel+"_wrap").show();
						$j("#rcal_setting_patern_"+sel).prop("selectedIndex", 0).change();

					}
					else {
						$j("#rcal_setting_patern_time_wrap").show();
						$j("#rcal_time_from").prop("selectedIndex", 0).change();
						$j("#rcal_time_to").prop("selectedIndex", 0).change();
					}
				}

			});

			target = $j("#rcal_lists").dataTable({
				"sAjaxSource": "<?php echo get_bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php?action=rcalreservation",
				<?php parent::echoDataTableLang(); ?>
				<?php
					parent::echoTableItem(array('resource_name_table','show_time','no_edit_customer_name','no_edit_remark'),false,"150px");
				?>
				"bSort":false,
				"fnServerParams": function ( aoData ) {
				  aoData.push( { "name": "menu_func","value":"Reservation_Init" } )
				},
				"fnDrawCallback": function () {
					<?php		parent::echoEditableCommon("resource"); 					?>
				},
				fnRowCallback: function( nRow, aData, iDisplayIndex, iDataIndex ) {
					var element = $j("td:eq(1)", nRow);
					element.text("");

					var sel_box = $j("<input>")
							.attr("type","button")
							.attr("id","rcal_select_btn_"+iDataIndex)
							.attr("name","rcal_update_"+iDataIndex)
							.attr("value","<?php _e('Select',RCAL_DOMAIN); ?>")
							.attr("class","rcal_button rcal_button_short")
							.click(function(event) {
								fnSelectRow(this.parentNode);
							});
					var del_box = $j("<input>")
							.attr("type","button")
							.attr("id","rcal_delete_btn_"+iDataIndex)
							.attr("name","rcal_delete_"+iDataIndex)
							.attr("class","rcal_button rcal_button_short")
							.attr("value","<?php _e('Delete ',RCAL_DOMAIN); ?>")
							.click(function(event) {
								fnSelectRow(this.parentNode);
								fnClickDeleteRow(this.parentNode);
							});
					element.append(sel_box);
					element.append(del_box);
<?php
		if ($this->config_datas['RCAL_CONFIG_USE_SUBMENU'] == ResourceCalendar_Config::USE_SUBMENU) {
		//ここで追加のロジックをいれる
			$echoRowCallbackData = "";
			echo( apply_filters('rcal_rowCallback',$echoRowCallbackData, $this->category_datas));
		}
?>

				}

			});

			<?php //繰り返し ?>
				$j("#rcal_repeat_onOff").change(function(){
					if($j(this).prop('checked')) {
						$j("#rcal_repeat_valid_from").val(fnDayFormat(selected_day,"<?php echo __('%m/%d/%Y',RCAL_DOMAIN); ?>"));
						$j("#rcal_repeat_patern").val("<?php echo ResourceCalendar_Repeat::WEEKLY; ?>" ).change();
						$j("#rcal_repeat_area").show();

					}
					else {
						$j("#rcal_repeat_area").hide();
						$j("#rcal_repeat_on input").prop("checked",false);
					}
				});

				$j("#rcal_repeat_patern").change(function(){

					$j("#rcal_repeat_days").hide();
					$j("#rcal_repeat_every_label").text("");
					$j("#rcal_repeat_end").val("");
					$j("#rcal_ends_patern_until").prop("checked",true).change();


					var sel = $j(this).val();

					if ( sel == <?php echo ResourceCalendar_Repeat::WEEKLY; ?> ) {
						for ( var i = 0 ; i < 7 ; i++ ) {
							if (rcalSchedule.chkFullHolidayInWeek(i)) {
								$j("#rcal_repeat_on_"+i).prop("disabled",true);
								$j("#rcal_repeat_on_"+i).prop("checked",false);
							}
						}

						$j("#rcal_repeat_days").show();
						$j("#rcal_repeat_every_label").text("<?php _e('weeks',RCAL_DOMAIN); ?>");
					} else if ( sel == <?php echo ResourceCalendar_Repeat::DAILY; ?> ) {
						$j("#rcal_repeat_every_label").text("<?php _e('days',RCAL_DOMAIN); ?>");

					} else if ( sel == <?php echo ResourceCalendar_Repeat::MONTHLY; ?> ) {
						$j("#rcal_repeat_every_label").text("<?php _e('months',RCAL_DOMAIN); ?>");

					} else if ( sel == <?php echo ResourceCalendar_Repeat::YEARLY; ?> ) {
						$j("#rcal_repeat_every_label").text("<?php _e('years',RCAL_DOMAIN); ?>");

					}

				});

				$j('input[name="rcal_end_patern"]').change(function(){
					var sel = $j(this).val();
					$j("#rcal_ends_patern_count_input").val("");
					$j("#rcal_ends_patern_count_input").prop("disabled",true);
					$j("#rcal_repeat_end").val("");
					$j("#rcal_repeat_end").prop("disabled",true);
					if (sel == <?php echo ResourceCalendar_Repeat::END_CNT ?> ) {
						$j("#rcal_ends_patern_count_input").prop("disabled",false);
						$j("#rcal_ends_patern_count_input").val("1");
					}
					else if (sel == <?php echo ResourceCalendar_Repeat::END_DATE ?> ) {

						$j("#rcal_repeat_end").val(fnDayFormat(after_day,"<?php echo __('%m/%d/%Y',RCAL_DOMAIN); ?>"));
						$j("#rcal_repeat_end").prop("disabled",false);
					}
				});

				$j("#rcal_ends_patern_count_input").change(function(){
					$j("#rcal_ends_patern_count").prop("checked",true);
					$j("#rcal_repeat_end").val("");
				});

				$j("#rcal_repeat_end").change(function(){
					$j("#rcal_ends_patern_until").prop("checked",true);
					$j("#rcal_ends_patern_count_input").val("");
				});
<?php //ここまで繰り返し ?>

			<?php
					foreach($this->resource_datas as $k1 => $d1 ) {
						if ($d1['setting_patern_cd'] == ResourceCalendar_Config::SETTING_PATERN_ORIGINAL)
							echo 'resource_items['.$d1['resource_cd'].'] = true;';
					}
			?>

			$j("#rcal_dialog").dialog({
					autoOpen: false,
					title: "<?php _e("Edit recurring event",RCAL_DOMAIN); ?>",
					closeOnEscape: false,
					modal: true,
					width: 400,
					buttons: {
					"<?php _e("Only",RCAL_DOMAIN); ?>": function(){
							_afterConfirmRepeat(<?php echo ResourceCalendar_Repeat::ONLY_UPDATE; ?>);
							$j(this).dialog("close");
						},
					"<?php _e("All",RCAL_DOMAIN); ?>": function(){
							_afterConfirmRepeat(<?php echo ResourceCalendar_Repeat::ALL_UPDATE; ?>);
							$j(this).dialog("close");
						},
					"<?php _e("Cancel",RCAL_DOMAIN); ?>": function(){
						$j(this).dialog("close");
					},
				}
			});


			<?php parent::echoSetItemLabel(); ?>
			<?php  parent::set_datepickerDefault(); ?>
			<?php  parent::set_datepicker("rcal_target_day",true); ?>
			<?php  parent::set_datepicker("rcal_repeat_valid_from",true); ?>
			<?php  parent::set_datepicker("rcal_repeat_end",true); ?>
			<?php parent::echoCommonButton();			//共通ボタン	?>



<?php //document ready ?>
		});

		function setTargetDate( target_date , hh , mm  ) {
			if (hh > 23 ) {
				hh = hh - 24;
				target_date.setDate(target_date.getDate() + 1 );
			}
			target_date.setHours(hh);
			target_date.setMinutes(mm);
		}

		function setTargetDateYMD(target_date, ymd ) {
			target_date.setYear(ymd.getFullYear());
			target_date.setMonth(ymd.getMonth());
			target_date.setDate(ymd.getDate());
		}

		function _fnEditSetting(id,func) {
			var set_data = $j("#rcal_original_name").val() + ":" + $j("#rcal_original_from").val() + "-" + 	$j("#rcal_original_to").val();

			var setcn = '<div id="rcal_res_each_'+id+'" ><span class="rcal_in_span">'+set_data+'</span><input type="button" class="rcal_in_button rcal_button rcal_button_short rcal_short_width_no_margin" value="<?php _e('Select',RCAL_DOMAIN); ?>" id="rcal_res_each_sel_'+id+'"/><input  type="button"  class="rcal_in_button rcal_button rcal_button_short rcal_short_width_no_margin" value="<?php _e('Delete ',RCAL_DOMAIN); ?>"id="rcal_res_each_del_'+id+'"/><input type="hidden" id="rcal_res_each_name_'+id+'" value="'+$j("#rcal_original_name").val()+'" /><input type="hidden" id="rcal_res_each_from_'+id+'" value="'+$j("#rcal_original_from").val()+'" /><input type="hidden" id="rcal_res_each_to_'+id+'" value="'+$j("#rcal_original_to").val()+'" /></div>';
			if (func == "add" )
				$j("#rcal_original_result").append(setcn);
			else
				$j("#rcal_res_each_"+save_result_select_id).replaceWith(setcn);

			$j("#rcal_res_each_sel_"+id).click(function () {
				$j("#rcal_original_name").val($j("#rcal_res_each_name_"+id).val());
				$j("#rcal_original_from").val($j("#rcal_res_each_from_"+id).val());
				$j("#rcal_original_to").val($j("#rcal_res_each_to_"+id).val());
				save_result_select_id = id;
			});
			$j("#rcal_res_each_del_"+id).click(function () {
				$j(this).parent().remove();
				save_result_select_id = "";

			});
			$j("#rcal_original_name").val("");
			$j("#rcal_original_from").val("");
			$j("#rcal_original_to").val("");
			save_result_select_id = "";
		}

		<?php parent::echoDataTableSeqUpdateRow("resource","resource_cd","rcal_"); ?>
		function fnSelectRow(target_col) {


			fnDetailInit();

			$j(target.fnSettings().aoData).each(function (){
				$j(this.nTr).removeClass("row_selected");
			});
			$j(target_col.parentNode).addClass("row_selected");
			var position = target.fnGetPosition( target_col );
			var setData = target.fnSettings();
			var dataDetail = setData['aoData'][position[0]]['_aData'];
			save_position = position[0];
			save_k1 = dataDetail['reservation_cd'];


			$j("#rcal_name").val(htmlspecialchars_decode(dataDetail['rcal_name']));
			$j("#rcal_tel").val(dataDetail['tel']);
			$j("#rcal_mail").val(dataDetail['mail']);
			$j("#rcal_remark").val(htmlspecialchars_decode(dataDetail['rcal_remark']));

			$j("#rcal_button_update").removeAttr("disabled");
			$j("#rcal_button_clear").show();

			$j("#rcal_data_detail").show();
			$j("#rcal_button_detail").val("<?php _e('Hide Details',RCAL_DOMAIN); ?>");

			<?php //時間指定のselectをつくる?>
			selected_day = new Date(dataDetail['time_from_ymd']);
			_fnMakeTimeItem();
			<?php //予約時間の設定方法、リソースコードがある場合は決められた時間帯の入力 ?>
			$j("#rcal_resource_cd").val(dataDetail['resource_cd']).change();
			target_day_from =  new Date(dataDetail['time_from']);
			target_day_to = new Date(dataDetail['time_to']);

			$j("#rcal_target_day").val(fnDayFormat(selected_day,"<?php echo __('%m/%d/%Y',RCAL_DOMAIN); ?>"));

			var setTimeFrom =  _fnOver24CheckAndEdit(dataDetail['hhmm_from']);
			var setTimeTo =  _fnOver24CheckAndEdit(dataDetail['hhmm_to']);
			if (resource_items[dataDetail['resource_cd']] ) {
				var setKey = setTimeFrom
							+ '-'
							+ setTimeTo;
				$j("#rcal_setting_patern_"+dataDetail['resource_cd']).val(setKey).change();
			}
			else {
				$j("#rcal_time_from").val(fnDayFormat(target_day_from,"%Y/%m/%d")+" "+setTimeFrom).change();
				$j("#rcal_time_to").val(fnDayFormat(target_day_to,"%Y/%m/%d")+" "+setTimeTo).change();
			}
			<?php //繰り返し ?>
			if (dataDetail["repeat_patern"] != <?php echo ResourceCalendar_Repeat::NO_REPEAT; ?>) {
				$j("#rcal_repeat_onOff").prop("checked",true).change();
				save_repeat = dataDetail["repeat_cd"];
				$j("#rcal_repeat_patern").val(dataDetail["repeat_patern"]).change();
				$j("#rcal_repeat_every").val(dataDetail["repeat_every"]);
				$j("#rcal_repeat_on input[type='checkbox']").prop("checked",false);
				if (dataDetail["repeat_patern"] == <?php echo ResourceCalendar_Repeat::WEEKLY; ?> ) {
					var weeks_split = dataDetail["repeat_weeks"].split(",");
					for ( var i = 0 ; i < weeks_split.length ; i++ ) {
						if ($j("#rcal_repeat_on_"+weeks_split[i]).prop("disabled") == false) {
							$j("#rcal_repeat_on_"+weeks_split[i]).prop("checked",true);
						}
					}
				}
				$j("#rcal_repeat_valid_from").val(dataDetail["rcal_valid_from"]);
				$j("input[name=rcal_end_patern]").val([dataDetail["repeat_end_patern"]]);
				$j("#rcal_ends_patern_count_input").val("");
				$j("#rcal_ends_patern_until").val("");
				if (dataDetail["repeat_end_patern"] == <?php echo ResourceCalendar_Repeat::END_CNT; ?> ) {
					$j("#rcal_ends_patern_count_input").val(dataDetail["repeat_cnt"]);
				}
				else if (dataDetail["repeat_end_patern"] == <?php echo ResourceCalendar_Repeat::END_DATE; ?> ) {
					$j("#rcal_repeat_end").val(dataDetail["rcal_valid_to"]);
				}
			}
			else {
				$j("#rcal_repeat_onOff").prop("checked",false).change();
			}
			<?php if ($this->config_datas['RCAL_CONFIG_USE_SUBMENU'] == ResourceCalendar_Config::USE_SUBMENU) : ?>
				<?php parent::echoSubMenuUpdate($this->category_datas,"dataDetail"); ?>
			<?php endif; ?>

		}

		function _fnOver24CheckAndEdit(hhmm) {
			//開始時刻より小さい
			var checkHH = +hhmm.substr(0,2);
			if (+rcalSchedule.config.open_time.substr(0,2) > checkHH ) {
				return ("0"+(checkHH+24)).substr(-2)+":"+hhmm.substr(2,2);
			}
			return hhmm.substr(0,2)+":"+hhmm.substr(2,2);
		}

		function _fnMakeTimeItem() {
			$j('#rcal_setting_patern_time_wrap').children().remove();

			var setcn = rcalSchedule.makeSelectDate(selected_day);
			$j('#rcal_setting_patern_time_wrap').append('<ul><li class="rcal_li"><select id="rcal_time_from" name="rcal_time_from" class="rcal_sel rcal_time rcal_nocheck" >'+setcn+'</select></li></ul>');
			$j('#rcal_setting_patern_time_wrap').append('<ul><li class="rcal_li"><select id="rcal_time_to" name="rcal_time_from" class="rcal_sel rcal_time rcal_nocheck" >'+setcn+'</select></li></ul>');


			$j("#rcal_time_from").attr("placeholder",check_items["rcal_time_from"]["label"]);
			$j("#rcal_time_from").parent().before('<li class="rcal_label"><label id="rcal_time_from_lbl" for="rcal_time_from" >'+check_items["rcal_time_from"]["label"]+':</label></li>');
			$j("#rcal_time_to").attr("placeholder",check_items["rcal_time_to"]["label"]);
			$j("#rcal_time_to").parent().before('<li class="rcal_label"><label id="rcal_time_to_lbl" for="rcal_time_to" >'+check_items["rcal_time_to"]["label"]+':</label></li>');

			$j('#rcal_time_from').on('change',function(){
				var start  = $j(this).val();
				if (start && start != -1 )	{
						target_day_from = new Date(start);
				}
			});
			$j('#rcal_time_to').on('change',function(){
				var end  = $j(this).val();
				if (end && end != -1 )	{
					target_day_to = new Date(end);
				}
			});

		}



		<?php parent::echoDataTableEditColumn("reservation"); ?>
		<?php parent::echoTextDateReplace(); ?>
		<?php parent::echoDayFormat(); ?>


		function fnClickAddRow(set_operate) {

			var except_item = "rcal_resource_cd,rcal_repeat_patern,rcal_repeat_on,rcal_repeat_patern,rcal_repeat_every,rcal_repeat_valid_from,rcal_ends_patern_count,rcal_cnt,rcal_ends_patern_until,rcal_repeat_end"
			if ( ! checkItem("rcal_data_detail",except_item) ) return false;

 			operate = set_operate;
			_UpdateEvent();
		}

		function _UpdateEvent() {
			if ($j("#rcal_repeat_onOff").prop("checked") ) {
				//繰り返しで登録の時はそのまま登録
				if (operate == "inserted" ) {
					_afterConfirmRepeat(<?php echo ResourceCalendar_Repeat::ALL_UPDATE; ?>);
				}
				else {
					<?php //このイベントのみを変更するのか、それ以外を変更するのか？ ?>
					$j('#rcal_dialog').dialog('open');
				}
			}
			else {
				var repeat_cnt;
				var repeat_valid_from;
				var repeat_valid_to;
				var repeat_end_patern;
				var repeat_weeks;
				var repeat_every;
				var repeat_need_update;

				//record_arrayとtemp_p2のみ使用
				_sendByAjax(
						_getRecordArray()
						,_getP2()
						, <?php echo ResourceCalendar_Repeat::NO_REPEAT; ?>
						,repeat_every
						,repeat_valid_from
						,repeat_valid_to
						,repeat_end_patern
						,repeat_weeks
						,repeat_cnt
						,repeat_need_update
						,<?php echo ResourceCalendar_Repeat::ONLY_UPDATE; ?>
					);
			}
		}

		function fnClickDeleteRow(target_col) {


			if (!$j("#rcal_repeat_onOff").prop("checked") ) {
				if (! confirm("<?php _e("This reservation delete ?",RCAL_DOMAIN); ?>") ) {
					return;
				}
			}

			operate = "deleted";
			_UpdateEvent();

		}


		function _getRecordArray() {
			var record_array = Object();
<?php if ($this->config_datas['RCAL_CONFIG_USE_SUBMENU'] == ResourceCalendar_Config::USE_SUBMENU) : ?>
	<?php parent::echoSubMenuSet(); ?>
<?php endif; ?>

			return record_array;
		}

		function _getP2() {
			var temp_p2 = '';
			if (operate != 'inserted') {
				var setData = target.fnSettings();
				temp_p2 = setData['aoData'][save_position]['_aData']['activate_key'];
			}
			return temp_p2;
		}

		function _afterConfirmRepeat(repeat_only_this_event) {
			var repeat_patern = $j("#rcal_repeat_patern").val();
			var repeat_every =$j("#rcal_repeat_every").val();
			var repeat_valid_from = $j("#rcal_repeat_valid_from").val();
			var repeat_valid_to = $j("#rcal_repeat_end").val();
			if (repeat_valid_to == "" ) {
				repeat_valid_to = "<?php echo ResourceCalendar_Component::editCountryYmd(2099,12,31); ?>";
			}
			var repeat_end_patern = $j('input[name="rcal_end_patern"]:checked').val();
			var tmp = new Array();
			$j("#rcal_repeat_on input[type=checkbox]").each(function (){
				if ( $j(this).is(":checked") ) {
					tmp.push( $j(this).val() );
				}
			});
			var repeat_weeks = tmp.join(",");
			var repeat_cnt = $j("#rcal_ends_patern_count_input").val();

			<?php //[TODO]reservationをDEL/DEFするかを判定する、変更有無で値を変える。?>
			var repeat_need_update = <?php echo ResourceCalendar_Repeat::NO_NEED_UPDATE; ?>;
			<?php //この変更だけを変える場合は、rcal_repeat_paternをクリアする ?>
			if (repeat_only_this_event == <?php echo ResourceCalendar_Repeat::ONLY_UPDATE; ?>) {
				repeat_patern = <?php echo ResourceCalendar_Repeat::NO_REPEAT; ?>;
			}
			else {
				if (operate == "updated" ) {
					<?php
					/*
					//繰り返しと関係ない項目かの判断
					//時間やresource_cdを変更した場合は、今までは予約できていても、すでに予約がある場合があるので
					//全部削除して登録しなおす
					 */
					?>
					var setData = target.fnSettings();
					var ev_tmp = setData['aoData'][save_position]['_aData'];
					if ( repeat_patern != ev_tmp["repeat_patern"]
						||	repeat_every != ev_tmp["repeat_every"]
						||	repeat_valid_from != ev_tmp["valid_from"]
						||	repeat_valid_to != ev_tmp["valid_to"]
						||	repeat_end_patern != ev_tmp["repeat_end_patern"]
						||	repeat_weeks != ev_tmp["repeat_weeks"]
						||	repeat_cnt != ev_tmp["repeat_cnt"]
						||	save_repeat != ev_tmp["repeat_cd"]
						||	toYYYYMMDD(target_day_from).slice(-5).replace(":","") != ev_tmp["from"]
						||	toYYYYMMDD(target_day_to).slice(-5).replace(":","") != ev_tmp["to"]
						||	$j("#rcal_resource_cd").val() != ev_tmp["resource_cd"]
						) {
						repeat_need_update = <?php echo ResourceCalendar_Repeat::NEED_UPDATE; ?>;
					}
				}
			}

			_sendByAjax(
					_getRecordArray()
					,_getP2()
					,repeat_patern
					,repeat_every
					,repeat_valid_from
					,repeat_valid_to
					,repeat_end_patern
					,repeat_weeks
					,repeat_cnt
					,repeat_need_update
					,repeat_only_this_event
				);


		}

		function _sendByAjax(
				record_array
				,temp_p2
				,repeat_patern
				,repeat_every
				,repeat_valid_from
				,repeat_valid_to
				,repeat_end_patern
				,repeat_weeks
				,repeat_cnt
				,repeat_need_update
				,repeat_only_this_event
				) {
			$j.ajax({
				type: "post",
				url:  "<?php echo get_bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php?action=rcalbooking",
				dataType : "json",
				data: {
					"rcal_resource_cd":$j("#rcal_resource_cd").val()
					,"id":save_k1
					,"rcal_name":$j("#rcal_name").val()
					,"rcal_mail":  $j("#rcal_mail").val()
					,"rcal_time_from":toYYYYMMDD(target_day_from)
					,"rcal_time_to":toYYYYMMDD(target_day_to)
<?php if ($this->config_datas['RCAL_CONFIG_USE_SUBMENU'] == ResourceCalendar_Config::USE_SUBMENU) : ?>
					,"rcal_memo":record_array
<?php else: ?>
					,"rcal_memo":""
<?php endif; ?>
					,"rcal_remark": $j("#rcal_remark").val()
					,"rcal_tel": $j("#rcal_tel").val()
					,"rcal_user_login": ""
					,"rcal_repeat_cd": save_repeat
					,"rcal_repeat_patern": repeat_patern
					,"rcal_repeat_every": repeat_every
					,"rcal_repeat_valid_from": repeat_valid_from
					,"rcal_repeat_valid_to": repeat_valid_to
					,"rcal_repeat_end_patern": repeat_end_patern
					,"rcal_repeat_weeks": repeat_weeks
					,"rcal_repeat_cnt": repeat_cnt
					,"rcal_repeat_need_update": repeat_need_update
					,"rcal_repeat_only_this" : repeat_only_this_event
					,"rcal_from_reservations": true
					,"type":operate
					,"p2":temp_p2
					,"nonce":"<?php echo $this->nonce; ?>"
					,"menu_func":"Booking_Edit"
				},
				success: function(data) {
					_setReturnData(data);
				},
				error:  function(XMLHttpRequest, textStatus){
					alert (textStatus);
				}
			});

		}


		function _setReturnData(data) {
			if (data === null || data.status == "Error" ) {
				alert(data.message);
			}
			else {
				target.fnClearTable();
				for(var seq in data.set_data) {
					target.fnAddData( data.set_data[seq] );
				}
				fnDetailInit();
				$j(target.fnSettings().aoData).each(function (){
					$j(this.nTr).removeClass("row_selected");
				});
			}
		}

		function toYYYYMMDD( date ){
			var month = date.getMonth() + 1;
			return  [date.getFullYear(),( '0' + month ).slice( -2 ),('0' + date.getDate()).slice(-2)].join( "-" ) + " "+ ('0' + date.getHours() ).slice(-2)+ ":" + ( '0' + date.getMinutes() ).slice( -2 );
		}


		function fnDetailInit() {
			$j("#rcal_data_detail input[type=\"text\"]").val("");
			$j("#rcal_data_detail input[type=\"email\"]").val("");
			$j("#rcal_data_detail input[type=\"tel\"]").val("");
			$j("#rcal_data_detail select").val("");
			$j("#rcal_data_detail textarea").val("");

			$j("#rcal_button_update").attr("disabled", true);
			$j("#rcal_button_insert").attr("disabled", false);

			save_k1 = "";


			<?php
				//時間指定と設定パターン（午前１など）
				foreach($this->resource_datas as $k1 => $d1 ) {
					if ($d1['setting_patern_cd'] == ResourceCalendar_Config::SETTING_PATERN_ORIGINAL) {
						echo '$j("#rcal_setting_patern_'.$d1['resource_cd'].'_wrap").hide();';
					}
				}
			?>


			$j("#rcal_repeat_area").hide();
			$j("#rcal_repeat_onOff").prop("checked",false);

			$j("#rcal_target_day").prop("disabled",false);
			$j("#rcal_target_day").val(fnDayFormat(today,"<?php echo __('%m/%d/%Y',RCAL_DOMAIN); ?>")).trigger("change");

			<?php parent::echo_clear_error(); ?>

		}


		function _getFileName(file_path) {
			file_name = file_path.substring(file_path.lastIndexOf('/')+1, file_path.length);
			return file_name;
		}


	<?php parent::echoCheckClinet(array('chk_required','zenkaku','chkZip','chkTel','chkMail','chkTime','chkDate','lenmax','reqOther','num','reqCheck')); ?>
	<?php parent::echoColumnCheck(array('chk_required','lenmax')); ?>

	</script>

	<h2 id="rcal_admin_title"><?php _e('Reservations',RCAL_DOMAIN); ?></h2>
	<?php echo parent::echoShortcode(); ?>
	<div id="rcal_button_div" >
	<input id="rcal_button_insert" type="button" value="<?php _e('Add',RCAL_DOMAIN); ?>"/>
	<input id="rcal_button_update" type="button" value="<?php _e('Update',RCAL_DOMAIN); ?>"/>
	<input id="rcal_button_clear" type="button" value="<?php _e('Clear',RCAL_DOMAIN); ?>"/>
	<input id="rcal_button_detail" type="button" />
	</div>

	<div id="rcal_data_detail"  >


		<label ><?php _e('Status',RCAL_DOMAIN); ?>:</label><span id="rcal_status_name"></span>

		<input type="text" id="rcal_target_day" class="chkDate" />
		<label ><?php echo $this->config_datas['RCAL_CONFIG_RESOURCE_NAME']; ?>:</label>
		<select id="rcal_resource_cd" name="rcal_resource_cd" class="rcal_sel">
<?php
		$echo_data = '';
		$echo_data .= '<option value="">'.__('select please',RCAL_DOMAIN).'</option>';
		foreach($this->resource_datas as $k1 => $d1 ) {
			$echo_data .= '<option value="'.$d1['resource_cd'].'">'.htmlspecialchars($d1['name'],ENT_QUOTES).'</option>';
		}
		echo $echo_data;
?>
		</select>
		<div id="rcal_setting_patern_time_wrap" ></div>
		<?php
		//時間指定と設定パターン（午前１など）
		foreach($this->resource_datas as $k1 => $d1 ) {
			if ($d1['setting_patern_cd'] == ResourceCalendar_Config::SETTING_PATERN_ORIGINAL) {
				echo '<div id="rcal_setting_patern_'.$d1['resource_cd'].'_wrap" class="rcal_patern_original" >';
				echo '<label >'.__('TargetTime',RCAL_DOMAIN).':</label>';
				echo '<select id="rcal_setting_patern_'.$d1['resource_cd'].'"  class="rcal_sel rcal_patern_original_sel rcal_nocheck"  >';
				$row_array = explode(';',$d1['setting_data']);
				foreach ($row_array as $k2 => $d2 ) {
					$col_array = explode(',',$d2);
					$dispFrom = ResourceCalendar_Component::editOver24($col_array[1]);
					$dispTo = ResourceCalendar_Component::editOver24($col_array[2]);
					echo '<option value="'.$col_array[1].'-'.$col_array[2].'" >'.$col_array[0].'('.$dispFrom.'-'.$dispTo.')</option>';
				}
				echo '</select>';
				echo '</div>';
			}
		}
		?>

		<input type="text" id="rcal_name" />
		<input type="tel" id="rcal_tel"  />
		<input type="email" id="rcal_mail"  />



<div id="rcal_reservation_detail" class="rcal_line" >
		<?php if ($this->config_datas['RCAL_CONFIG_USE_SUBMENU'] == ResourceCalendar_Config::USE_SUBMENU) : ?>
		<?php parent::echoSubmenu($this->category_datas); ?>
		<?php endif; //submenuを使えるか ?>

		<ul>
			<li class="rcal_label" ><label ><?php _e('Repeat',RCAL_DOMAIN); ?>:</label></li>
			<li class="rcal_li" > <input type="checkbox" id="rcal_repeat_onOff" class="rcal_category_option" /></li>
		</ul>
		<div id="rcal_repeat_area">
		<ul>
			<li class="rcal_label" ><label ><?php _e("Repeats",RCAL_DOMAIN); ?></label></li>
			<li class="rcal_li">
				<select id="rcal_repeat_patern" class="rcal_sel">
					<option value="<?php echo ResourceCalendar_Repeat::DAILY; ?>" ><?php _e("Daily",RCAL_DOMAIN); ?></option>
					<option value="<?php echo ResourceCalendar_Repeat::WEEKLY; ?>" ><?php _e("Weekly",RCAL_DOMAIN); ?></option>
					<option value="<?php echo ResourceCalendar_Repeat::MONTHLY; ?>" ><?php _e("Monthly",RCAL_DOMAIN); ?></option>
					<option value="<?php echo ResourceCalendar_Repeat::YEARLY; ?>" ><?php _e("Yearly",RCAL_DOMAIN); ?></option>
				</select>
			</li>
		</ul>

		<ul>
			<li class="rcal_label" ><label ><?php _e("Repeat every",RCAL_DOMAIN); ?>:</label></li>
			<li class="rcal_li">
				<select id="rcal_repeat_every" class="rcal_sel" >
				<?php
					for($i = 1;$i < 31 ; $i++) {
						echo '<option value="'.$i.'" >'.$i.'</option>';
					}
				?>
				</select>
				<label id="rcal_repeat_every_labelx"></label>
			</li>
		</ul>

		<ul id="rcal_repeat_days">
			<li class="rcal_label" ><label ><?php _e("Repeat on",RCAL_DOMAIN); ?>:</label></li>
			<li class="rcal_li">
				<?php
				echo '<div id="rcal_repeat_on" class="rcal_checkbox" >	';
				$week = array(__('sun',RCAL_DOMAIN),__('mon',RCAL_DOMAIN), __('tue',RCAL_DOMAIN), __('wed',RCAL_DOMAIN), __('thr',RCAL_DOMAIN), __('fri',RCAL_DOMAIN), __('sat',RCAL_DOMAIN));
				for ( $i = 0 ; $i < 7 ; $i++ ) {
					echo '<input type="checkbox" id="rcal_repeat_on_'.$i.'" class="rcal_repeat_on rcal_nocheck" value="'.$i.'" /><label for="rcal_repeat_on_'.$i.'">'.$week[$i].'</label>';
				}
				echo '</div>';
				?>
			</li>
		</ul>

		<ul><li class="rcal_li"><input  id="rcal_repeat_valid_from" size="10"/></li></ul>
		<ul>
			<li class="rcal_label" ><label ><?php _e("Ends",RCAL_DOMAIN); ?>:</label></li>
			<li class="rcal_li ">
				<input id="rcal_ends_patern_count" name="rcal_end_patern" type="radio"  value="<?php echo ResourceCalendar_Repeat::END_CNT; ?>" class="rcal_category_option">
				<label for="rcal_ends_patern_count" ><?php _e("After",RCAL_DOMAIN); ?></label>
				<input class="rcal_cnt" id="rcal_ends_patern_count_input" >
				<label><?php _e("occurrences",RCAL_DOMAIN); ?></label>
			</li>
		</ul>

				<ul>
			<li class="rcal_label" ><label ></label></li>
			<li class="rcal_li">
				<input id="rcal_ends_patern_until" name="rcal_end_patern" type="radio"  value="<?php echo ResourceCalendar_Repeat::END_DATE; ?>" class="rcal_category_option">
				<label for="rcal_ends_patern_until" ><?php _e("On",RCAL_DOMAIN); ?></label>
				<input class="rcal_date" id="rcal_repeat_end" >
			</li>
		</ul>

		</div>
		<?php //rcal_reservation_detal?>
</div>
		<textarea id="rcal_remark" ></textarea>
		<div class="spacer"></div>

	</div>

	<table class="flexme" id='rcal_lists'>
	<thead>
	</thead>
	</table>
	<div id="rcal_dialog" style="display:none"><?php _e("Change only this reservation, or all reservations in the series ?",RCAL_DOMAIN); ?></div>
<?php
	}	//show_page
}		//class

