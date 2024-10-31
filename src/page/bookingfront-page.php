<?php

	require_once(RCAL_PLUGIN_SRC_DIR . 'page/resource-calendar-page.php');


class BookingFront_Page extends ResourceCalendar_Page {

	const Y_PIX = 550;

	private $resource_datas = null;
	private $month_datas = null;

	private $first_hour = '';
	private $last_hour = '';

	private $reseration_cd = '';


	private $url = '';

	private $reservation_datas = null;
	private $category_datas = null;

	private $user_inf = null;


	private $valid_from = '';
	private $valid_to = '';


	public function __construct() {
		parent::__construct();
		$this->target_year = date_i18n("Y");
		$url = get_bloginfo('wpurl');
		if (is_ssl() && strpos(strtolower ( $url),'https') === false ) {
			$url = preg_replace("/[hH][tT][tT][pP]:/","https:",$url);
		}
		$this->url = $url;


	}


	public function set_resource_datas ($datas) {
		$this->resource_datas = $datas;
		if (count($this->resource_datas) === 0 ) {
			throw new ResourceCalendarException(ResourceCalendar_Component::getMsg('E010',__function__.':'.__LINE__ ) );
		}
	}

	public function set_month_datas ($datas) {
		$this->month_datas = $datas;
	}

	public function set_reservation_datas($reservation_datas) {
		$this->reservation_datas = $reservation_datas;

	}

	public function set_category_datas ($set_data ) {
		$this->category_datas = $set_data;
	}

	public function set_user_inf($user_inf) {
		//携帯優先
		$user_inf["set_tel"] = "";
		if (!empty($user_inf["mobile"] )) {
			$user_inf["set_tel"] = $user_inf["mobile"] ;
		}
		else if (!empty($user_inf["tel"] )) {
			$user_inf["set_tel"] = $user_inf["tel"] ;
		}
		$this->user_inf = $user_inf;
	}


	public function set_config_datas($config_datas) {
		parent::set_config_datas($config_datas);
		$edit = ResourceCalendar_Component::computeDate($config_datas['RCAL_CONFIG_AFTER_DAY']);
		$this->insert_max_day = substr($edit,0,4).','.(intval(substr($edit,5,2))-1).','.(intval(substr($edit,8,2))+1);

		$this->first_hour = substr($config_datas['RCAL_CONFIG_OPEN_TIME'],0,2);
		$this->last_hour = substr($config_datas['RCAL_CONFIG_CLOSE_TIME'],0,2);
//		if (intval($this->last_hour) > 0 ) $this->last_hour++;

		$now = date_i18n('Ymd');
		$this->valid_from = ResourceCalendar_Component::computeDate(-1*$config_datas['RCAL_CONFIG_BEFORE_DAY'],substr($now,0,4),substr($now,4,2),substr($now,6,2));
		//$this->valid_to = ResourceCalendar_Component::computeDate($this->config_datas['RCAL_CONFIG_AFTER_DAY'],substr($now,0,4),(+substr($now,4,2)-1),substr($now,6,2));
		$this->valid_to = ResourceCalendar_Component::computeDate($this->config_datas['RCAL_CONFIG_AFTER_DAY'],substr($now,0,4),(substr($now,4,2)),substr($now,6,2));
	}

	public function getValidTo() {
		return $this->valid_to;
	}

// 	private function _editDate($yyyymmdd) {
// 		return substr($yyyymmdd,0,4). substr($yyyymmdd,5,2).  substr($yyyymmdd,8,2);
// 	}
// 	private function _editTime($yyyymmdd) {
// 		return substr($yyyymmdd,11,2). substr($yyyymmdd,14,2);
// 	}

	private function _echoReservationButton(){
		if (($this->config_datas['RCAL_CONFIG_ENABLE_RESERVATION'] ==  ResourceCalendar_Config::USER_ANYONE)
			|| ( $this->config_datas['RCAL_CONFIG_ENABLE_RESERVATION'] ==  ResourceCalendar_Config::USER_REGISTERED
				&&  is_user_logged_in() ) ) {
			echo '<a  data-role="button"  id="rcal_regist_button" class="rcal_tran_button" href="javascript:void(0)" >'
				.__('Booking',RCAL_DOMAIN).'</a>';
		}
	}

	private function _echoSearchButton() {
		$require_array = explode(',',$this->config_datas['RCAL_CONFIG_REQUIRED']);
		$nameRequire = in_array('rcal_name',$require_array) ? "required": "";
		echo '<ul><li class="rcal_li"><input type="text" id="rcal_name" '.$nameRequire.' />';

		if ($this->isPluginAdmin() ) {
			echo '<input id="rcal_button_search" type="button" class="rcal_button" value="'.__('Search',RCAL_DOMAIN).'" />';
		}
		echo '</li></ul>';
	}



	public function show_page() {
?>
<?php

	$url =   get_permalink();
	$parts = explode('/',$url);
	$addChar = "?";
	if (strpos($parts[count($parts)-1],"?") ) {
		$addChar = "&";
	}
	$url = $url.$addChar."rcal_desktop=true";
	$edit_resource = array();
	$limit_resource = array();
	$reserve_possible_cnt = 0;



	$chk_from = str_replace("-","",substr($this->valid_from,0,10));
	$chk_to = str_replace("-","",substr($this->valid_to,0,10));
	$limit_exist = false;
	foreach ($this->resource_datas as $k1 => $d1 ) {
		if (($chk_from <= $d1['chk_to']  && $d1['chk_to'] <= $chk_to ) ||
			($chk_from <= $d1['chk_from']  && $d1['chk_from'] <= $chk_to ) ){
			$limit_exist = true;
		}
		if ( empty($d1['photo_result'][0]) ) {
			$tmp='<span class="rcal_noimg" >'.htmlspecialchars($d1['name'],ENT_QUOTES).'</span>';
			$tmp2="";
		}
		else {
			$tmp = "<img src='".$d1['photo_result'][0]['photo_resize_path']."' alt='' />";
			$url = site_url();
			$url = substr($url,strpos($url,':')+1);
			$url = str_replace('/','\/',$url);
			$tmp2 = $d1['photo_result'][0]['photo_path'];
			if (is_ssl() ) {
				$tmp = preg_replace("/([hH][tT][tT][pP]:".$url.")/","https:".$url,$tmp);
				$tmp2 = preg_replace("/([hH][tT][tT][pP]:".$url.")/","https:".$url,$tmp2);
			}
			else {
				$tmp = preg_replace("/([hH][tT][tT][pP][sS]:".$url.")/","http:".$url,$tmp);
				$tmp2 = preg_replace("/([hH][tT][tT][pP][sS]:".$url.")/","http:".$url,$tmp2);
			}
		}

		$edit_resource[$d1['resource_cd']]['img'] = $tmp;
		$edit_resource[$d1['resource_cd']]['label'] = htmlspecialchars($d1['name'],ENT_QUOTES);
		$edit_resource[$d1['resource_cd']]['href'] = $tmp2;

		$limit_resource[] = array($d1['resource_cd'],$d1['chk_from'] ,$d1['chk_to'] );

	}
	$init_target_day = date_i18n('Ymd');
	$resource_holiday_class = "rcal_holiday";
	$resource_holiday_set = __('Holiday',RCAL_DOMAIN);
	//必須項目
	$require_array = explode(',',$this->config_datas['RCAL_CONFIG_REQUIRED']);
	$nameRequire = in_array('rcal_name',$require_array) ? "required": "";
	$telRequire = in_array('rcal_tel',$require_array) ? "required": "";
	$mailRequire = in_array('rcal_mail',$require_array) ? "required": "";
	if ($this->config_datas['RCAL_CONFIG_ENABLE_RESERVATION'] == ResourceCalendar_Config::CONFIRM_BY_MAIL ) {
		$mailRequire = "required";
	}

?>
<div id="rcal_content" role="main">

<?php if ($this->config_datas['RCAL_CONFIG_CAL_SIZE'] !== 100) : ?>
	<style>
		.ui-datepicker {
			font-size: <?php echo $this->config_datas['RCAL_CONFIG_CAL_SIZE']; ?>%;
		}
		.entry-content th,td {
			font-size: <?php echo $this->config_datas['RCAL_CONFIG_CAL_SIZE']; ?>%;
			padding:0px;
		}
	</style>
<?php endif; ?>

	<script type="text/javascript" charset="utf-8">
		var $j = jQuery;
		var top_pos;
		var bottom_pos;
		var today = "<?php echo $init_target_day; ?>";
		var after_day = new Date(<?php echo substr($this->valid_to,0,4).",".(+substr($this->valid_to,5,2)-1).",".substr($this->valid_to,8,2); ?>);

		var target_day_from = new Date();
		var target_day_to = new Date();
		var operate = "";
		var save_id = "";
		var save_repeat = <?php echo ResourceCalendar_Repeat::NO_REPEAT; ?>;

		var is_limitExist = <?php if ($limit_exist) echo "true"; else echo "false"; ?>;

		var is_holiday= false;

		var selected_day;	<?php //表示用と設定用でわけとく ?>

		var isTouch = ('ontouchstart' in window);
		var tap_interval = <?php echo ResourceCalendar_Config::TAP_INTERVAL; ?>;

		var resource_items = new Array();

		var save_user_login = "";

		var setMonth = new Object();

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
		foreach ( $this->month_datas as $k1 => $d1 ) {
			echo 'rcalSchedule._months["'.$k1.'"]= {};';
			foreach ( $d1 as $k2 => $d2 ) {
				$title_data = array();
				$exist_tentative = false;
				foreach ($d2 as $k3=>$d3 ) {
					if ($k3 == ResourceCalendar_Reservation_Status::COMPLETE ){
						$title_data [] = sprintf(__('Completed Reservations',RCAL_DOMAIN).":%d ",$d3);
					}
					else if ($k3 == ResourceCalendar_Reservation_Status::TEMPORARY ){
						$title_data[] = sprintf(__('Temporary Reservations',RCAL_DOMAIN).":%d  ",$d3);
						$exist_tentative = true;
					}
				}
				echo 'rcalSchedule._months["'.$k1.'"]["'.$k2.'"]= "'.implode('\n',$title_data).'";';
				if ($exist_tentative ) {
					echo 'rcalSchedule._months["'.$k1.'"]["'.$k2.'_flg"]= true;';
				}
			}
		}
		foreach ($this->resource_datas as $k1 => $d1 ) {
				echo 'var fr = fnSetDay("'.$d1['chk_from'].'");';
				echo 'var to = fnSetDay("'.$d1['chk_to'].'");';
				echo 'rcalSchedule.config.resource_holidays["'.$d1['resource_cd'].'"]= [fr,to];';
		}
	if ($this->config_datas['RCAL_CONFIG_USE_SUBMENU'] == ResourceCalendar_Config::USE_SUBMENU) {
		//カテゴリーのパターンを設定する
		echo 'var category_patern = new Object();';
		foreach($this->category_datas as $k1 => $d1 ) {
			echo 'category_patern["i'.$d1['category_cd'].'"]='.$d1['category_patern'].';';
		}
	}
?>

		$j(window).on('resize', function(){
			_fnCalcDisplayMonth();
			AutoFontSize();
			setDayData(fnDayFormat(selected_day,"%Y%m%d"));
		});

		$j(window).load(function(){

			<?php if (RCAL_DEMO ) : ?>
				$j("#rcal_login_div").show();
			<?php endif; ?>

			$j("#rcal_main").show();
			_fnCalcDisplayMonth();
			var top = 	$j("#rcal_main_data").outerHeight()	- $j("#rcal_holiday").css("font-size").toLowerCase().replace("px","");
			$j("#rcal_holiday").css("padding-top",top / 2 + "px");
			$j("#rcal_holiday").height($j("#rcal_main_data").outerHeight()- (top/2));
			$j("#rcal_holiday").width($j("#rcal_main_data").outerWidth());
			<?php parent::echoSetItemLabelMobile(); ?>
			<?php
				$res = parent::echoMobileData($this->reservation_datas,date_i18n('Ymd') ,$this->first_hour);
				$from = substr($this->valid_from,0,10);
				$to = str_replace("-","",substr($this->valid_to,0,10));
				for($i=0;$i<10000;$i++) {
					$target =  date("Ymd",strtotime($from." + ".($i)." days") );
					if (array_key_exists($target,$res)) {
						echo "rcalSchedule._daysResource[".$target."] = ".$res[$target].";";
					}
// 					else if (parent::checkReapeatTargetDay($target,$res_repeat)) {
// 						echo "rcalSchedule._daysResource[".$target."] = ".$res_repeat[$target].";";
// 					}
					else {
						echo "rcalSchedule._daysResource[".$target."] = {\"e\":0};";
					}
					if ($target == $to ) break;
				}
			?>

			<?php //ヘッダがどんなかわからないのでいちづけ  ?>
			top_pos = $j("#rcal_main").offset().top;
			bottom_pos = top_pos + $j("#rcal_main").height();
			$j("html,body").animate({ scrollTop: top_pos }, 'fast');

			AutoFontSize();

			setDayData(today);

<?php if ( ! ResourceCalendar_Component::isMobile() ) : ?>
			$j(".lightbox").colorbox();
<?php endif; ?>
	});

<?php //現状はここにはこないのでコメント?>
<?php /*
// 		function _fnGetMonthData(base_day){
// 			<?php //base_day YYYYMM?>
// 			var yyyy = base_day.substr(0,4);
// 			var mm = base_day.substr(-2);
//			var last = new Date(yyyy,mm,0); <?php //翌月の0日=今月末 ?>
// 			$j.ajax({
// 				 	type: "post",
//					url:  "<?php echo get_bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php?action=rcalbooking",
// 					dataType : "json",
// 					data: {
// 						"from":yyyy+'-'+mm+'-1',
// 						"to":$j.format.date(last, "yyyy-MM-dd"),
//						"nonce":"<?php echo $this->nonce; ?>",
// 						"menu_func":"Booking_Get_Month"
// 					},
// 					success: function(data) {
// 						rcalSchedule._months[data.set_data.yyyymm] = {};
// 						if (data.set_data.cnt > 0 ) {
// <?php //[TODO]ここは見直し？
// //							var tmp_target_day = "";
// //							var index = 0;
// //							var tmp_array = new Object();
// //							for(var k1 = 0 ;k1 < data.cnt ;k1++) {
// //								if (tmp_target_day == "" ) tmp_target_day = data.datas[k1]["target_day"];
// //								if ( tmp_target_day != data.datas[k1]["target_day"]) {
// //									getReservation[tmp_target_day] = tmp_array;
// //									tmp_array = new Object();
// //									index = 0;
// //								}
// //								tmp_array[index++] = data.datas[k1];
// //								tmp_target_day = data.datas[k1]["target_day"];
// //							}
// //							getReservation[tmp_target_day] = tmp_array;
// ?>
// 						}
// 					},
// 					error:  function(XMLHttpRequest, textStatus){
// 						alert (textStatus);
// 					}
// 			 });
// 		}
*/ ?>
		function existMonthData(targetDate ) {
			if (!rcalSchedule._months[yyyymm]) return false;
			return true;
		}



		$j(document).ready(function() {


			<?php parent::echoSearchCustomer($this->url,$this->nonce); //検索画面 ?>

			<?php parent::set_datepicker_date($this->config_datas['RCAL_SP_DATES']); ?>

			<?php  parent::set_datepickerDefault(); ?>
			<?php
				$addCode = "";
			//現状は情報を最初に取得しているのでここはいらない。[FROM]
// 				$addCode = '
// 						,onChangeMonthYear: function( year, month, inst ) {

// 							var lastMonth = new Date(year ,month-1,1);
// 							lastMonth.setMonth(lastMonth.getMonth()-1);
// 							var yyyymm = lastMonth.getFullYear() + ("0"+(lastMonth.getMonth()+1)).slice(-2);
// 							if (!rcalSchedule._months[yyyymm]) {
// 								_fnGetMonthData(yyyymm);
// 							}
// 							var nextMonth = new Date(year ,month+1,1);
// 							nextMonth.setMonth(nextMonth.getMonth()+1);
// 							yyyymm = nextMonth.getFullYear() + ("0"+(nextMonth.getMonth()+1)).slice(-2);
// 							if (!rcalSchedule._months[yyyymm]) {
// 								_fnGetMonthData(yyyymm);
// 							}

// 					}';
			//現状は情報を最初に取得しているのでここはいらない。[TO]

				$addCode .= ',changeMonth: false,onSelect: function(dateText, inst) { target_day_from = new Date(dateText); target_day_to = new Date(dateText);var yyyymmdd = _fnTextDateReplace(dateText); _fnSetResource(yyyymmdd);setDayData(yyyymmdd); }';
				$display_month = 1;

				if ( !ResourceCalendar_Component::isMobile() ) $display_month = $this->config_datas['RCAL_CONFIG_SHOW_DATEPICKER_CNT'];

				parent::set_datepicker("rcal_calendar",false,$this->config_datas['RCAL_CONFIG_CLOSED'],$addCode,$display_month,true,false);
			?>

			<?php  parent::set_datepicker("rcal_repeat_start",true,$this->config_datas['RCAL_CONFIG_CLOSED'],"",1,false,false); ?>
			<?php  parent::set_datepicker("rcal_repeat_end",true,$this->config_datas['RCAL_CONFIG_CLOSED'],"",1,false,false); ?>

			var timer;

			<?php parent::echoSetHolidayMobile($this->resource_datas,$this->target_year);	?>

			<?php
				foreach($this->resource_datas as $k1 => $d1 ) {
					if ($d1['setting_patern_cd'] == ResourceCalendar_Config::SETTING_PATERN_ORIGINAL)
						echo 'resource_items['.$d1['resource_cd'].'] = true;';
				}
			?>

			$j("#rcal_page_regist").hide();
			$j("#rcal_holiday").hide();

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

			$j("#rcal_repeat_onOff").change(function(){
				if($j(this).prop('checked')) {
<?php
// 					var mW = $j("#rcal_repeat").find('.rcal_modalBody').innerWidth() / 2;
// 					var mH = $j("#rcal_repeat").find('.rcal_modalBody').innerHeight() / 2;
// 					var obj = $j("#rcal_repeat").find('.rcal_modalBody');
// 					$j(obj).css("position","absolute");
// 					$j(obj).css("top", ( $j(window).height() - $j(obj).height() ) / 2+$j(window).scrollTop() + "px");
// 					$j(obj).css("left", ( $j(window).width() - $j(obj).width() ) / 2+$j(window).scrollLeft() + "px");
?>
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


			$j("#rcal_regist_button").click(function(){
<?php if (RCAL_DEMO ) : ?>
				$j("#rcal_login_div").hide();
<?php endif; ?>


<?php
if ($this->config_datas['RCAL_CONFIG_USE_SUBMENU'] == ResourceCalendar_Config::USE_SUBMENU) {
	echo "\$j(\".rcal_category_check_opt input\").prop(\"checked\",false);";
	foreach($this->category_datas as $k1 => $d1 ) {

// 		if ($d1['category_patern'] == ResourceCalendar_Category::RADIO ) {
// 			echo " \$j(\"#category_i{$d1['category_cd']}_0\").prop(\"checked\",true);";
// 		}
// 		elseif ($d1['category_patern'] == ResourceCalendar_Category::CHECK_BOX ) {
// 			echo " \$j(\"#category_i{$d1['category_cd']}_0\").prop(\"checked\",true);";
// 		}
		if ($d1['category_patern'] == ResourceCalendar_Category::SELECT ) {
			$tmp_array = explode(',',$d1['category_values']);
			echo " \$j(\"#category_i{$d1['category_cd']}\").val(\"".$tmp_array[0]."\");";
		}
		elseif ($d1['category_patern'] == ResourceCalendar_Category::TEXT ) {
			echo " \$j(\"#category_i{$d1['category_cd']}\").val(\"\");";
		}
	}
}
?>

				$j(".rcal_modal").hide();

				var now = new Date();
				now.setMinutes(now.getMinutes()+<?php echo $this->config_datas['RCAL_CONFIG_RESERVE_DEADLINE']; ?>);
				_fnAddReservation(now.getHours()+1);


				$j('#rcal_resource_cd').prop('selectedIndex', 0).change();
				$j("#rcal_exec_regist").text("<?php _e('Booking',RCAL_DOMAIN); ?>");
			<?php 	if (is_user_logged_in() && (! $this->isPluginAdmin()) ) : ?>
					$j("#rcal_name").val("<?php echo $this->user_inf["user_name"]; ?>");
					$j("#rcal_tel").val("<?php echo $this->user_inf["set_tel"]; ?>");
					$j("#rcal_mail").val("<?php echo $this->user_inf["user_email"]; ?>");
					save_user_login = "<?php echo $this->user_inf["user_login"]; ?>"
			<?php endif; ?>
				$j('#rcal_repeat_onOff').prop('checked', false).change();
				save_repeat = <?php echo ResourceCalendar_Repeat::NO_REPEAT; ?>;
			});

			$j("#rcal_mainpage").click(function(){
				$j("#rcal_page_main").show();
				$j("html,body").animate({ scrollTop: top_pos }, 'fast');
			});

			$j("#rcal_mainpage_regist").click(function(){
				$j("#rcal_page_main").show();
				$j("#rcal_page_regist").hide();
				_fnCalcDisplayMonth();
				AutoFontSize();
				setDayData(fnDayFormat(selected_day,"%Y%m%d"));
				$j("html,body").animate({ scrollTop: top_pos }, 'fast');
<?php if (RCAL_DEMO ) : ?>
			$j("#rcal_login_div").show();
<?php endif; ?>
			});
			$j("#rcal_exec_regist").click(function(){
				_UpdateEvent();
			});

			$j("#rcal_exec_delete").click(function() {
				<?php //繰り返しがついていないのであれば、ここで確認する ?>
				if (!$j("#rcal_repeat_onOff").prop("checked") ) {
					if (! confirm("<?php _e("This reservation delete ?",RCAL_DOMAIN); ?>") ) {
						return;
					}
				}

				operate = "deleted";
				_UpdateEvent();

			});

			$j(".rcal_patern_original_sel").change(function(){
				var time_fromto = $j(this).val();
				if (time_fromto) {
					var time_fromto_array = time_fromto.split("-");
					setTargetDate(target_day_from,+time_fromto_array[0].substr(0,2),+time_fromto_array[0].substr(3,2));
					setTargetDate(target_day_to,+time_fromto_array[1].substr(0,2),+time_fromto_array[1].substr(3,2));
//ref
//					target_day_from.setHours(+time_fromto_array[0].substr(0,2));
//					target_day_from.setMinutes(+time_fromto_array[0].substr(3,2));
//					target_day_to.setHours(+time_fromto_array[1].substr(0,2));
//					target_day_to.setMinutes(+time_fromto_array[1].substr(3,2));
//ref

				}
				else {
					alert("<?php _e('select please',RCAL_DOMAIN); ?>");
				}
			});


			if (document.getElementById("rcal_today") != null ) {
				$j("#rcal_today").click(function() {
					setDayData(today);
					$j("#rcal_calendar").datepicker("setDate", fnDayFormat(selected_day,"<?php echo __('%m/%d/%Y',RCAL_DOMAIN); ?>"));
				});
			}

			$j(document).on('click','.rcal_on_business',function(){
				var tmp_val = $j(this.children).text();
				_fnAddReservation(+tmp_val.split(":")[1]);
				$j("#rcal_resource_cd").val(tmp_val.split(":")[0]).change();
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

			$j("#rcal_searchdate").change(function(){
				var in_date = _fnTextDateReplace( $j("#rcal_searchdate").val() );
				<?php //in_dateはYYYYMMDD形式で戻る ?>
				if ( in_date === false ) return;
				setDayData(in_date);
			});
<?php  	if (($this->config_datas['RCAL_CONFIG_ENABLE_RESERVATION'] ==  ResourceCalendar_Config::USER_ANYONE) ||
 			( $this->config_datas['RCAL_CONFIG_ENABLE_RESERVATION'] ==  ResourceCalendar_Config::USER_REGISTERED &&  is_user_logged_in() ) ): ?>

			$j(".rcal_time_li").click(function() {
<?php if (RCAL_DEMO ) : ?>
			$j("#rcal_login_div").hide();
<?php endif; ?>
				var tmp_resource_cd = this.parentElement.id.split("_")[2];
				var tmp_time = +$j(this).children().text();
				if (! tmp_time ) tmp_time = <?php echo  +substr($this->config_datas['RCAL_CONFIG_OPEN_TIME'],0,2); ?>;
				_fnAddReservation(tmp_time);
//				$j("#rcal_resource_cd").val(tmp_resource_cd).change();
				$j("#rcal_resource_cd").val(tmp_resource_cd);
				<?php //ここで時間を設定しないと上のchangeでクリアされる　?>

				$j("#rcal_setting_patern_time_wrap").hide();
				$j(".rcal_patern_original").hide();
				if (resource_items[tmp_resource_cd]) {
					$j("#rcal_setting_patern_"+tmp_resource_cd+"_wrap").show();
					$j("#rcal_setting_patern_"+tmp_resource_cd).prop("selectedIndex", 0).change();
				}
				else {
					$j("#rcal_setting_patern_time_wrap").show();
					$j("#rcal_time_from").val(toHHMM(target_day_from));
					$j("#rcal_time_to").val(toHHMM(target_day_to));
				}
				$j('#rcal_repeat_onOff').prop('checked', false).change();
				save_repeat = <?php echo ResourceCalendar_Repeat::NO_REPEAT; ?>;
			});
<?php endif; ?>
<?php //windows loadへ移す ?>
			<?php //parent::echoSetItemLabelMobile(); ?>
<?php
// 				$res = parent::echoMobileData($this->reservation_datas,date_i18n('Ymd') ,$this->first_hour);
// 				$repeat_inf = array();
// 				$from = substr($this->valid_from,0,10);
// 				$to = str_replace("-","",substr($this->valid_to,0,10));
// 				for($i=0;$i<10000;$i++) {
// 					$target =  date("Ymd",strtotime($from." + ".($i+1)." days") );
// 					if (array_key_exists($target,$res)) {
// 						echo "rcalSchedule._daysResource[".$target."] = ".$res[$target].";";
// 					}
// // 					else if (parent::checkReapeatTargetDay($target,$res_repeat)) {
// // 						echo "rcalSchedule._daysResource[".$target."] = ".$res_repeat[$target].";";
// // 					}
// 					else {
// 						echo "rcalSchedule._daysResource[".$target."] = {\"e\":0};";
// 					}
// 					if ($target == $to ) break;
// 				}
// //ヘッダがどんなかわからないのでいちづけ
// 			top_pos = $j("#rcal_main").offset().top;
// 			bottom_pos = top_pos + $j("#rcal_main").height();
// 			$j("html,body").animate({ scrollTop: top_pos }, 'fast');

// 			AutoFontSize();

// 			setDayData(today);
?>


	});

		<?php parent::echoCheckDeadline	($this->config_datas['RCAL_CONFIG_RESERVE_DEADLINE']); ?>

		<?php parent::echoTextDateReplace(); ?>


		function _fnSetResource(date) {
<?php //リソースで期限指定がある場合は、日によって出力するコンボの内容を変える ?>
			if (is_limitExist) {

				$j('#rcal_resource_cd').children().remove();
<?php
		$echo_data = '';
		foreach($this->resource_datas as $k1 => $d1 ) {
			$from = +$d1['chk_from'];
			$name = htmlspecialchars($d1['name'],ENT_QUOTES);
			echo <<<EOT
			if ( (${from} <= date ) && ( date <= ${d1['chk_to']} ) ) {
				\$j("#rcal_resource_cd").append("<option value=\"${d1['resource_cd']}\">${name}</option>");
			}
EOT;
		}
		echo $echo_data;
?>
			}
		}

		function _fnMakeTimeItem() {
			$j('#rcal_setting_patern_time_wrap').children().remove();

			var setcn = rcalSchedule.makeSelectDate(selected_day);
			$j('#rcal_setting_patern_time_wrap').append('<ul><li class="rcal_li"><select id="rcal_time_from" name="rcal_time_from" class="rcal_sel rcal_time" >'+setcn+'</select></li></ul>');
			$j('#rcal_setting_patern_time_wrap').append('<ul><li class="rcal_li"><select id="rcal_time_to" name="rcal_time_from" class="rcal_sel rcal_time" >'+setcn+'</select></li></ul>');


			$j("#rcal_time_from").attr("placeholder",check_items["rcal_time_from"]["label"]);
			$j("#rcal_time_from").parent().before('<li class="rcal_label"><label id="rcal_time_from_lbl" for="rcal_time_from" >'+check_items["rcal_time_from"]["label"]+':</label></li>');
			$j("#rcal_time_to").attr("placeholder",check_items["rcal_time_to"]["label"]);
			$j("#rcal_time_to").parent().before('<li class="rcal_label"><label id="rcal_time_to_lbl" for="rcal_time_to" >'+check_items["rcal_time_to"]["label"]+':</label></li>');

			$j('#rcal_time_from').on('change',function(){
				var start  = $j(this).val();
				if (start && start != -1 )	{
						target_day_from = new Date(start);
<?php
//ref					setTargetDate(target_day_from,+start.substr(0,2),+start.substr(3,2));
//ref
//					target_day_from.setHours(+start.substr(0,2));
//					target_day_from.setMinutes(+start.substr(3,2));
//
?>
				}
			});
			$j('#rcal_time_to').on('change',function(){
				var end  = $j(this).val();
				if (end && end != -1 )	{
					target_day_to = new Date(end);
<?php
//ref					setTargetDate(target_day_to,+end.substr(0,2),+end.substr(3,2));
//ref
//					target_day_to.setHours(+end.substr(0,2));
//					target_day_to.setMinutes(+end.substr(3,2));
//
?>
				}
			});

		}

		function _fnCalcDisplayMonth() {

			var screen_cnt = $j(".ui-datepicker-inline").children().length;
			var base = $j(".ui-datepicker-group-first").width();
			if ( ! base  ) {
				base = $j("#rcal_calendar").children().width();
				if (! base ) return;
			}
			var w = $j("#rcal_content").width() ;
			if (w > base * 3 ) {
				$j("#rcal_calendar").datepicker("option", "numberOfMonths", 3);
			}
			else if (w > base * 2 ) {
				$j("#rcal_calendar").datepicker("option", "numberOfMonths", 2);
			}
			else {
				$j("#rcal_calendar").datepicker("option", "numberOfMonths", 1);
			}
		}

		function _fnAddReservation (startHour) {
			<?php //過去は予約できないようにしとく ?>
			var chk_date = 	new Date(selected_day);
			<?php //開始時間が開店時間よりちいさい場合は２４時間の翌日なので調整する ?>
			if (startHour < <?php echo(+substr($this->config_datas['RCAL_CONFIG_OPEN_TIME'],0,2)); ?> ) {
				startHour += 24;
			}

			if (startHour) {
				chk_date.setHours(startHour);
			}

			if (!_checkDeadline(chk_date,rcalSchedule.config.open_time,rcalSchedule.config.close_time) ) return;

			$j("#rcal_page_main").hide();
			$j("#rcal_page_regist").show();
			$j("#rcal_exec_delete").hide();
			$j("#rcal_target_day").text(fnDayFormat(selected_day,"<?php echo __('%m/%d/%Y',RCAL_DOMAIN); ?>"));
			target_day_from = new Date(selected_day);
			if (startHour) {
				target_day_from.setHours(startHour);
			}
			target_day_to = new Date(target_day_from.getTime());
			operate = "inserted";
			setStatus();
			save_id = "";


			_fnMakeTimeItem();


			$j("#rcal_exec_regist").text("<?php _e('Booking',RCAL_DOMAIN); ?>");
			$j("#rcal_remark").val("");


		}

		function _fnCalcDay(ymd,add) {
			var clas = Object.prototype.toString.call(ymd).slice(8, -1);
			if (clas !== 'Date') {
				return ymd;
			}
			var tmpDate = ymd.getDate();
			ymd.setDate(tmpDate + add);
			return fnDayFormat(ymd,"%Y%m%d");
		}

		function setDayData(yyyymmdd) {
			yyyymmdd=yyyymmdd+"";
			<?php //すでにエラーになっている場合は、表示しておしまい ?>
			if (rcalSchedule._daysResource[yyyymmdd]) {
				if (rcalSchedule._daysResource[yyyymmdd]["err"]) {
					<?php //エラーなのでDatePickerの日付を戻す ?>
					$j("#rcal_calendar").datepicker("setDate", fnDayFormat(selected_day,"<?php echo __('%m/%d/%Y',RCAL_DOMAIN); ?>"));
					alert(rcalSchedule._daysResource[yyyymmdd]["err"]);
					return false;
				}
			}
<?php //初期表示で設定しているので未使用 ?>
<?php /*
			<?php //初めての日付はサーバへ	?>
			else {
				_GetEvent(yyyymmdd);
				 return;
			}
*/ ?>
			var yyyy = yyyymmdd.substr(0,4);
			var mm = yyyymmdd.substr(4,2);
			var dd = yyyymmdd.substr(6,2);
			selected_day = new Date(yyyy, +mm - 1,dd);


			$j("#rcal_searchdate").val(fnDayFormat(selected_day,"<?php echo __('%m/%d/%Y',RCAL_DOMAIN); ?>"));
			$j(".rcal_tile").off("click");
			$j(".rcal_tile").remove();
			$j(".rcal_resource_holiday").remove();

			$j("#rcal_searchdays").text(rcalSchedule.config.day_full[selected_day.getDay()]);

<?php			//各liの幅が異なるので配列で ?>
			var tmp_width = Array();
<?php			//単純にひとつめだと期間によって消えている場合がある。 ?>
			var search_idx = null;
			$j("#rcal_main_data ul").each(function(){
				if ($j(this).is(':visible')) {
					search_idx = $j(this).attr("id");
				}
			});
			$j("#"+search_idx+" li.rcal_time_li").each(function(){
				tmp_width.push($j(this).outerWidth());
			});
<?php			//予約の部分でも使用 ?>
			var left_start = $j("#"+search_idx+" li:first-child").outerWidth();

			var setWidth = tmp_width.join(",");
			rcalSchedule.setWidth(setWidth);

			<?php		//リソース単位の使えない日　?>
			for ( var i = 0 ; i < rcalSchedule.config.resource_holidays.length ; i ++ ) {
				if ( rcalSchedule.config.resource_holidays[i] ) {
					if ((rcalSchedule.config.resource_holidays[i][0] <=  selected_day) &&
						(selected_day <= rcalSchedule.config.resource_holidays[i][1]  )) {
						$j("#rcal_st_"+i).show();
					}
					else {
						$j("#rcal_st_"+i).hide();
					}
				}
			}
			<?php		//休みだったら ?>
			if (rcalSchedule.chkHoliday(selected_day) ) {

				var top = 	$j("#rcal_main_data").outerHeight()	- $j("#rcal_holiday").css("font-size").toLowerCase().replace("px","");
				$j("#rcal_holiday").css("padding-top",top / 2 + "px");
				$j("#rcal_holiday").height($j("#rcal_main_data").outerHeight()- (top/2));
				$j("#rcal_holiday").css("left",rcalSchedule.getHolidayLeft(selected_day,left_start));
				$j("#rcal_holiday").width(rcalSchedule.getHolidayWidth(selected_day));
				$j("#rcal_holiday").show();
				if (rcalSchedule.chkFullHoliday(selected_day) ) {
					$j("#rcal_regist_button").hide();
					return;
				}
				else {
					$j("#rcal_regist_button").show();
				}
			}
			else {
				$j("#rcal_holiday").hide();
				$j("#rcal_regist_button").show();
			}
<?php //過去の場合は予約ボタンをおせないように ?>
			if (yyyymmdd < <?php echo $init_target_day; ?> ) {
				$j("#rcal_regist_button").hide();
			}

<?php /*
		tmpb:0:左開始位置（5分単位） 1:幅 2:イベントID（ログインしていない場合はランダム） 3:開始時刻(YYMM) 4:終了時刻(YYMM) 5:エディットOK(1)NG(0)
		tmpd:0:備考 1:P2 2:名前 3:電話 4:メール
*/ ?>
			for(var seq0 in rcalSchedule._daysResource[yyyymmdd]["d"]){
				for(var resource_cd in rcalSchedule._daysResource[yyyymmdd]["d"][seq0]){
					var base=+rcalSchedule._daysResource[yyyymmdd]["d"][seq0][resource_cd]["s"];
					var height = Math.floor($j("#rcal_st_" + resource_cd).outerHeight()/base)-2;	//微調整

					for(var seq1 in rcalSchedule._daysResource[yyyymmdd]["d"][seq0][resource_cd]["d"]) {
						for(var level in rcalSchedule._daysResource[yyyymmdd]["d"][seq0][resource_cd]["d"][seq1]) {
							var tmpb = rcalSchedule._daysResource[yyyymmdd]["d"][seq0][resource_cd]["d"][seq1][level]["b"];
							var tmpd = rcalSchedule._daysResource[yyyymmdd]["d"][seq0][resource_cd]["d"][seq1][level]["d"];
							var left = rcalSchedule.getLeft( left_start,tmpb[0] );
							var width = rcalSchedule.getWidth( tmpb[0], tmpb[0]+tmpb[1] );
							var top = (+level) * height;
							var eid = 'rcal_event_'+resource_cd+'_'+tmpb[2];
							rcalSchedule._events[tmpb[2]]={"resource_cd":resource_cd,"from":tmpb[3],"to":tmpb[4],"status":tmpb[6],"repeat_cd":tmpb[7],"repeat_patern":tmpb[8]};

							var set_class = "rcal_tile";
							var set_title = "";
							if (tmpb[6] == <?php echo ResourceCalendar_Reservation_Status::COMPLETE; ?> ) {
								set_title =  "<?php _e('Completed Reservation',RCAL_DOMAIN); ?>";
								set_class += " rcal_myres_comp";
							}
							else if (tmpb[6] == <?php echo ResourceCalendar_Reservation_Status::TEMPORARY; ?>) {
								set_title = "<?php _e('Temporary Reservation',RCAL_DOMAIN); ?>";
								set_class += " rcal_myres_temp";
							}
							if (tmpb[5]=="<?php echo ResourceCalendar_Edit::OK; ?>") {
								set_class += " rcal_myres_edit";
							}

							var setcn = '<div id="'+eid+'" class="'+set_class+'"style="position:absolute; top:'+top+'px; height: '+height+'px; left:'+left+'px; width:'+width+'px;">'+set_title+'</div>';

							$j("#rcal_st_"+resource_cd+"_dummy").prepend(setcn);

							if (tmpb[5]=="<?php echo ResourceCalendar_Edit::OK; ?>") {
								rcalSchedule.setEventDetail(tmpb[2],tmpd);
								$j("#"+eid).on("click",function(){
									_fnMakeTimeItem();
									$j("#rcal_page_main").hide();
									$j("#rcal_page_regist").show();
									$j("#rcal_exec_delete").show();
									$j("#rcal_exec_regist").text("<?php _e("Reservation Update",RCAL_DOMAIN); ?>");
									var ids = this.id.split("_");
									save_id = ids[3];
									var ev_tmp = rcalSchedule._events[save_id];

									$j("#rcal_resource_cd").val(ev_tmp["resource_cd"]).change();



									var calcYmdFrom =  new Date(selected_day);
									var settimeFrom = ev_tmp["from"].substr(0,2)+":"+ev_tmp["from"].substr(2,2);
									var setKey = settimeFrom + '-';
									if (23 < +ev_tmp["from"].substr(0,2)) {
										calcYmdFrom = computeDate(calcYmdFrom,1);
										settimeFrom = ("0"+(+(ev_tmp["from"].substr(0,2))-24)).substr(-2)+":"+ev_tmp["from"].substr(2,2);
									}
									else {
									}
									var calcYmdTo = new Date(selected_day);
									var settimeTo = ev_tmp["to"].substr(0,2)+":"+ev_tmp["to"].substr(2,2);
									setKey = setKey + settimeTo;
									if (23 < +ev_tmp["to"].substr(0,2)) {
										calcYmdTo = computeDate(calcYmdTo,1);
										settimeTo = ("0"+(+(ev_tmp["to"].substr(0,2))-24)).substr(-2)+":"+ev_tmp["to"].substr(2,2);
									}
									<?php //予約時間の設定方法、リソースコードがある場合は決められた時間帯の入力 ?>
									if (resource_items[ev_tmp["resource_cd"]] ) {
										<?php //calcFromでやるとrcal_setting_patern_の中で24時過ぎた時に翌日になってしまう ?>
										target_day_from = new Date(selected_day);
										target_day_to = new Date(selected_day);
										<?php //var setKey = settimeFrom + '-' + settimeTo; ?>
										$j("#rcal_setting_patern_"+ev_tmp["resource_cd"]).val(setKey).trigger('change',[true]);
									}
									else {
										target_day_from = calcYmdFrom;
										$j("#rcal_time_from").val(fnDayFormat(target_day_from,"%Y/%m/%d")+" "+settimeFrom).change();
										target_day_to = calcYmdTo;
										$j("#rcal_time_to").val(fnDayFormat(target_day_to,"%Y/%m/%d")+" "+settimeTo).change();
									}
									$j("#rcal_name").val(htmlspecialchars_decode(ev_tmp["name"]));
									$j("#rcal_tel").val(ev_tmp["tel"]);
									$j("#rcal_mail").val(ev_tmp["mail"]);
									$j("#rcal_remark").val(htmlspecialchars_decode(ev_tmp["remark"]));
									$j("#rcal_target_day").text($j("#rcal_searchdate").val());

									if (ev_tmp["repeat_patern"] != <?php echo ResourceCalendar_Repeat::NO_REPEAT; ?>) {
										$j("#rcal_repeat_onOff").prop("checked",true).change();
										save_repeat = ev_tmp["repeat_cd"];
										$j("#rcal_repeat_patern").val(ev_tmp["repeat_patern"]).change();
										$j("#rcal_repeat_every").val(ev_tmp["repeat_every"]);
										$j("#rcal_repeat_on input[type='checkbox']").prop("checked",false);
										if (ev_tmp["repeat_patern"] == <?php echo ResourceCalendar_Repeat::WEEKLY; ?> ) {
											var weeks_split = ev_tmp["repeat_weeks"].split(",");
											for ( var i = 0 ; i < weeks_split.length ; i++ ) {
												if ($j("#rcal_repeat_on_"+weeks_split[i]).prop("disabled") == false) {
													$j("#rcal_repeat_on_"+weeks_split[i]).prop("checked",true);
												}
											}
										}
										$j("#rcal_repeat_valid_from").val(ev_tmp["valid_from"]);
										$j("input[name=rcal_end_patern]").val([ev_tmp["repeat_end_patern"]]);
										$j("#rcal_ends_patern_count_input").val("");
										$j("#rcal_ends_patern_until").val("");
										if (ev_tmp["repeat_end_patern"] == <?php echo ResourceCalendar_Repeat::END_CNT; ?> ) {
											$j("#rcal_ends_patern_count_input").val(ev_tmp["repeat_cnt"]);
										}
										else if (ev_tmp["repeat_end_patern"] == <?php echo ResourceCalendar_Repeat::END_DATE; ?> ) {
											$j("#rcal_repeat_end").val(ev_tmp["valid_to"]);
										}
									}
									else {
										$j("#rcal_repeat_onOff").prop("checked",false).change();
									}

<?php if ($this->config_datas['RCAL_CONFIG_USE_SUBMENU'] == ResourceCalendar_Config::USE_SUBMENU) : ?>
			<?php parent::echoSubMenuUpdate($this->category_datas,"ev_tmp"); ?>
<?php endif; ?>
									operate = "updated";
									setStatus();

								});
							}
						}
					}
				}
			}
		}

		<?php
		parent::echoClientItemMobile(array('search_day','customer_name','booking_tel','time_from','time_to','booking_mail','remark','booking_starts'));
		?>
		<?php parent::echoDayFormat(); ?>
		<?php parent::echosSetDay(); ?>
		<?php parent::echoCheckDeadline	($this->config_datas['RCAL_CONFIG_RESERVE_DEADLINE']); ?>


		function AutoFontSize(){
			var each = $j("#rcal_main_data ul li:nth-child(2)").outerWidth();
<?php //字は12px。時間はゼロ埋めしているので2ケタ。初期表示で０の場合があるので判定をいれとく ?>
			if (each > 0 ) {
				var fpar = Math.floor(each/24*100);
				$j(".rcal_main_line li").css("font-size",fpar+"%");
				$j(".rcal_main_line li:first-child").css("font-size","100%");
			}
		}


<?php //現状はここにはこないのでコメント?>
<?php /*
		function _GetEvent(targetDay) {
			$j.ajax({
				 	type: "post",
					url:  "<?php echo get_bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php?action=rcalbooking",
					dataType : "json",
					data: {
						"rcal_target_day":targetDay
						,"first_hour":<?php echo +$this->first_hour; ?>
						,"nonce":"<?php echo $this->nonce; ?>"
						,"menu_func":"Booking_Get_Reservation"
					},
					success: function(data) {
						if (data.status == "Error" ) {
							rcalSchedule._daysResource[targetDay] = {"err":data.message};
							<?php //エラーなのでDatePickerの日付を戻す ?>
							$j("#rcal_searchdate").val(fnDayFormat(selected_day,"<?php echo __('%m/%d/%Y',RCAL_DOMAIN); ?>"));
							$j("#rcal_calendar").datepicker("setDate", fnDayFormat(selected_day,"<?php echo __('%m/%d/%Y',RCAL_DOMAIN); ?>"));
							alert(data.message);
						}
						else {
							rcalSchedule._daysResource[targetDay] = data.set_data[targetDay];
							setDayData(targetDay)
						}
					},
					error:  function(XMLHttpRequest, textStatus){
						alert (textStatus);
					}
			 });
		}
*/ ?>

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
				temp_p2 = rcalSchedule._events[save_id]['p2'];
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
					var ev_tmp = rcalSchedule._events[save_id];
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
					,"id":save_id
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
					,"rcal_user_login": save_user_login
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
			if (data.status == "Error" ) {
				alert(data.message);
			}
			else {
				var setTargetDate = fnDayFormat(new Date(selected_day),"%Y%m%d");

				for(var setDate in data.set_data) {
					rcalSchedule._daysResource[setDate] = data.set_data[setDate];
					var yyyymm = setDate.slice(0,6);
<?php /*
tmpb:0:左開始位置（5分単位） 1:幅 2:イベントID（ログインしていない場合はランダム） 3:開始時刻(YYMM) 4:終了時刻(YYMM) 5:エディットOK(1)NG(0)
displayDataはDatePickerでマウスオーバの際に表示する予約数用
*/ ?>
					var displayData = Array(0,0,0);
					rcalSchedule._months[yyyymm][setDate+"_flg"]= false;
					for(var seq0 in rcalSchedule._daysResource[setDate]["d"]){
						for(var resource_cd in rcalSchedule._daysResource[setDate]["d"][seq0]){
							for(var seq1 in rcalSchedule._daysResource[setDate]["d"][seq0][resource_cd]["d"]) {
								for(var level in rcalSchedule._daysResource[setDate]["d"][seq0][resource_cd]["d"][seq1]) {
									var tmpb = rcalSchedule._daysResource[setDate]["d"][seq0][resource_cd]["d"][seq1][level]["b"];
									displayData[tmpb[6]]++;
									if (tmpb[6] == <?php echo ResourceCalendar_Reservation_Status::TEMPORARY; ?> ) {
										rcalSchedule._months[yyyymm][setDate+"_flg"]= true;
									}
								}
							}
						}
					}
					var setString = null;
					if (displayData[1] > 0 ) {
						setString = "<?php _e('Completed Reservations',RCAL_DOMAIN); ?>:"+displayData[1]+"\n";
					}
					if (displayData[2] > 0 ) {
						setString += "<?php _e('Temporary Reservations',RCAL_DOMAIN); ?>:"+displayData[2];
					}
					rcalSchedule._months[yyyymm][setDate]=setString;
				}
				$j("#rcal_calendar").datepicker("refresh");

				$j("#rcal_mainpage_regist").trigger("click");
				<?php /*
				setDayDataで変更した日の表示しているBOXを変更する
				*/ ?>
				setDayData(setTargetDate);

				if (operate != "deleted")	alert(data.message);
<?php if (RCAL_DEMO ) : ?>
$j("#rcal_login_div").show();
<?php endif; ?>
			}
		}

		function computeDate(date, addDays) {
			var baseSec = date.getTime();
			var addSec = addDays * 86400000;//日数 * 1日のミリ秒数
			var targetSec = baseSec + addSec;
			date.setTime(targetSec);
			return date;
		}

		function toYYYYMMDD( date ){
			var month = date.getMonth() + 1;
			return  [date.getFullYear(),( '0' + month ).slice( -2 ),('0' + date.getDate()).slice(-2)].join( "-" ) + " "+ ('0' + date.getHours() ).slice(-2)+ ":" + ( '0' + date.getMinutes() ).slice( -2 );
		}

		function toHHMM( calcDate ) {
			var hhmm = ('0' + calcDate.getHours()).slice(-2) + ':' + ('0' + calcDate.getMinutes()).slice(-2);
			var yyyymmdd = calcDate.getFullYear() + '/' + ('0' + (calcDate.getMonth() + 1)).slice(-2) + '/' + ('0' + calcDate.getDate()).slice(-2) + ' ';
			return (yyyymmdd + hhmm);
<?php 	//ref
		//	return ('0'+date.getHours()).slice(-2)+ ":" + ('0'+date.getMinutes()).slice(-2);
?>
		}

<?php //target_dateはDate前提 ?>
		function setTargetDate( target_date , hh , mm  ) {
			if (hh > 23 ) {
				hh = hh - 24;
				target_date.setDate(target_date.getDate() + 1 );
			}
			target_date.setHours(hh);
			target_date.setMinutes(mm);
		}

		function setStatus() {
			$j("#rcal_status_name").text("");
			if (operate == "inserted" ) {
				$j("#rcal_status_name").text("<?php _e('Register',RCAL_DOMAIN); ?>");
			}
			else if (operate == "updated" ) {
				var status = rcalSchedule._events[save_id]["status"];
				if (rcalSchedule._events[save_id]["status"] == <?php echo ResourceCalendar_Reservation_Status::COMPLETE; ?>) {
					$j("#rcal_status_name").text("<?php _e('Completed Reservation',RCAL_DOMAIN); ?>" );
				}
				else if (rcalSchedule._events[save_id]["status"] == <?php echo ResourceCalendar_Reservation_Status::TEMPORARY; ?>) {
					$j("#rcal_status_name").text("<?php _e('Temporary Reservation',RCAL_DOMAIN); ?>" );
				}
				else if (rcalSchedule._events[save_id]["status"] == <?php echo ResourceCalendar_Reservation_Status::CANCELED; ?>) {
					$j("#rcal_status_name").text("<?php _e('Canceled Reservation',RCAL_DOMAIN); ?>" );
				}
			}
		}

		function _fnGetServerData(base_day){
			<?php //base_day YYYYMM?>
			var yyyy = base_day.substr(0,4);
			var mm = base_day.substr(-2);
			var last = new Date(yyyy,mm,0); <?php //翌月の0日=今月末 ?>
			$j.ajax({
				 	type: "post",
					url:  "<?php echo get_bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php?action=rcalbooking",
					dataType : "json",
					data: {
						"from":yyyy+'-'+mm+'-1',
						"to":$j.format.date(last, "yyyy-MM-dd"),
						"nonce":"<?php echo $this->nonce; ?>",
						"menu_func":"Bookint_Get_Month"
					},

					success: function(data) {
						setMonth[data.yyyymm] = data.cnt;
						if (data.cnt > 0 ) {
							var tmp_target_day = "";
							var index = 0;
							var tmp_array = new Object();
							for(var k1 = 0 ;k1 < data.cnt ;k1++) {
								if (tmp_target_day == "" ) tmp_target_day = data.datas[k1]["target_day"];
								if ( tmp_target_day != data.datas[k1]["target_day"]) {
									getReservation[tmp_target_day] = tmp_array;
									tmp_array = new Object();
									index = 0;
								}
								tmp_array[index++] = data.datas[k1];
								tmp_target_day = data.datas[k1]["target_day"];
							}
							getReservation[tmp_target_day] = tmp_array;
						}
					},
					error:  function(XMLHttpRequest, textStatus){
						alert (textStatus);
					}
			 });
		}

		<?php $this->echoRemoveModal(); ?>
</script>
</div>

	<?php if (RCAL_DEMO ) : ?>

		<div id="rcal_login_div" style="display:none" >
		<?php if ( is_user_logged_in() ) : ?>
				<a href="<?php echo get_bloginfo( 'wpurl' ); ?>/wp-admin" ><?php _e('Settings here',RCAL_DOMAIN); ?></a><br>
				<a href="<?php echo wp_logout_url(get_permalink() ); ?>" ><?php _e('Logout here',RCAL_DOMAIN); ?></a>
		<?php else : ?>
				<p><?php _e('Please try settings.',RCAL_DOMAIN); ?></p>
<a title="login" href="<?php echo wp_login_url(get_permalink() ) ?>"><?php _e('Login',RCAL_DOMAIN); ?></a>
<br /><?php echo _e('Username'); ?>: demologin
<br /><?php echo _e('Password'); ?>: demo001
		<?php endif; ?>
		</div>
	<?php endif; ?>

<div id="rcal_main" style="display:none" >
	<div id="rcal_page_main" >

		<div id="rcal_header_r3" class="rcal_line">
			<ul>
				<li class="rcal_date">
					<input type="text" id="rcal_searchdate" name="rcal_searchdate" placeholder="<?php _e('MM/DD/YYYY',RCAL_DOMAIN); ?>">
					<span id="rcal_searchdays"></span>
				</li>
			</ul>
			<ul>
				<li class="rcal_date"></li>
				<li class="rcal_date"></li>
				<li class="rcal_date"><input type="button" id="rcal_today" value="<?php _e('Today',RCAL_DOMAIN); ?>" ></li>
			</ul>
		</div>
		<div id="rcal_header" class="rcal_line" >
			<ul><li><div id="rcal_calendar" ></div></li></ul>
		</div>
		<div id="rcal_main_data" class="rcal_line rcal_main_line">
			<?php
			foreach ($edit_resource as $k1 => $d1) {
				echo "<ul id=\"rcal_st_{$k1}\"><li class=\"rcal_first_li\">";
				if (ResourceCalendar_Component::isMobile() || empty($d1['href']) ) {
					echo $d1['img'];
				}
				else {
					echo "<a class=\"lightbox\" rel=\"resource".$k1."\" href=\"".$d1['href']."\">".$d1['img']."</a>";
				}
				echo "</li>";
				for($i = +$this->first_hour ; $i < $this->last_hour ; $i++ ) {
					$time = $i;
					if ($time > 24 ) $time -= 24;
					echo '<li class="rcal_time_li">
						<span>'.sprintf("%02d",$time).'</span>
						</li>';
				}
				echo "<div id=\"rcal_st_{$k1}_dummy\"></div>";
				echo '</ul>';
			}
			?>
			<div id="rcal_holiday" class="rcal_holiday" ></div>

			<?php $this->_echoReservationButton(); ?>

		</div>

	</div>

	<div id="rcal_page_regist" >

<?php if ($this->isPluginAdmin() ) : ?>
	<div id="rcal_search" class="rcal_modal">
		<div class="rcal_modalBody">
			<div id="rcal_search_result"></div>
		</div>
	</div>
<?php endif; ?>


<?php  	if (($this->config_datas['RCAL_CONFIG_ENABLE_RESERVATION'] ==  ResourceCalendar_Config::USER_ANYONE) ||
 			( $this->config_datas['RCAL_CONFIG_ENABLE_RESERVATION'] ==  ResourceCalendar_Config::USER_REGISTERED &&  is_user_logged_in() ) ): ?>
		<div id="rcal_regist_detail" class="rcal_line" >
		<ul>
			<li class="rcal_label" ><label ><?php _e('Date',RCAL_DOMAIN); ?>:</label></li>
			<li class="rcal_li"><span id="rcal_target_day"></span></li>
		</ul>
		<ul>
			<li class="rcal_label" ><label ><?php _e('Status',RCAL_DOMAIN); ?>:</label></li>
			<li class="rcal_li"><span id="rcal_status_name"></span></li>
		</ul>

		<?php $this->_echoSearchButton(); ?>

		<ul><li class="rcal_li"><input type="tel" id="rcal_tel" <?php echo $telRequire; ?> /></li></ul>
		<ul><li class="rcal_li"><input type="email" id="rcal_mail"  <?php echo $mailRequire; ?> /></li></ul>

		<ul>
		<li class="rcal_label" ><label ><?php echo $this->config_datas['RCAL_CONFIG_RESOURCE_NAME']; ?>:</label></li>
		<li class="rcal_li" >
		<select id="rcal_resource_cd" name="rcal_resource_cd" class="rcal_sel">
<?php
		$echo_data = '';
		$echo_data .= '<option value="">'.__('select please',RCAL_DOMAIN).'</option>';
		foreach($this->resource_datas as $k1 => $d1 ) {
			if ($d1['chk_from'] <= $init_target_day &&  $init_target_day <= $d1['chk_to'] ) {
				$echo_data .= '<option value="'.$d1['resource_cd'].'">'.htmlspecialchars($d1['name'],ENT_QUOTES).'</option>';
			}
		}
		echo $echo_data;
?>
		</select>
		</li></ul>
		<div id="rcal_setting_patern_time_wrap" ></div>
		<?php
		//時間指定と設定パターン（午前１など）
		foreach($this->resource_datas as $k1 => $d1 ) {


			if ($d1['setting_patern_cd'] == ResourceCalendar_Config::SETTING_PATERN_ORIGINAL) {
				echo '<div id="rcal_setting_patern_'.$d1['resource_cd'].'_wrap" class="rcal_patern_original" >';
				echo '<ul>';
				echo '<li class="rcal_label" ><label >'.__('TargetTime',RCAL_DOMAIN).':</label></li>';
				echo '<li class="rcal_li"><select id="rcal_setting_patern_'.$d1['resource_cd'].'"  class="rcal_sel rcal_patern_original_sel"  >';
				$row_array = explode(';',$d1['setting_data']);
				foreach ($row_array as $k2 => $d2 ) {
					$col_array = explode(',',$d2);
					$dispFrom = ResourceCalendar_Component::editOver24($col_array[1]);
					$dispTo = ResourceCalendar_Component::editOver24($col_array[2]);
					echo '<option value="'.$col_array[1].'-'.$col_array[2].'" >'.$col_array[0].'('.$dispFrom.'-'.$dispTo.')</option>';
				}
				echo '</select></li>';
				echo '</ul></div>';
			}
		}
		?>
<?php if ($this->config_datas['RCAL_CONFIG_USE_SUBMENU'] == ResourceCalendar_Config::USE_SUBMENU) : ?>
<?php parent::echoSubmenu($this->category_datas); ?>
<?php endif; //submenuを使えるか ?>
		<ul>
			<li class="rcal_label" ><label ><?php _e('Repeat',RCAL_DOMAIN); ?>:</label></li>
			<li class="rcal_li" > <input type="checkbox" id="rcal_repeat_onOff"  /></li>
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
			<label id="rcal_repeat_every_label"></label>
			</li>
		</ul>

		<ul id="rcal_repeat_days">
			<li class="rcal_label" ><label ><?php _e("Repeat on",RCAL_DOMAIN); ?>:</label></li>
			<li class="rcal_li">
				<?php
				echo '<div id="rcal_repeat_on" class="rcal_checkbox" >	';
				$week = array(__('sun',RCAL_DOMAIN),__('mon',RCAL_DOMAIN), __('tue',RCAL_DOMAIN), __('wed',RCAL_DOMAIN), __('thr',RCAL_DOMAIN), __('fri',RCAL_DOMAIN), __('sat',RCAL_DOMAIN));
				for ( $i = 0 ; $i < 7 ; $i++ ) {
					echo '<label for="rcal_repeat_on_'.$i.'"><input type="checkbox" id="rcal_repeat_on_'.$i.'" value="'.$i.'" />'.$week[$i].'</label>';
				}
				echo '</div>';
				?>
			</li>
		</ul>

		<ul><li class="rcal_li"><input  id="rcal_repeat_valid_from" size="10"/></li></ul>
		<ul>
			<li class="rcal_label" ><label ><?php _e("Ends",RCAL_DOMAIN); ?>:</label></li>
			<li class="rcal_li">
				<input id="rcal_ends_patern_count" name="rcal_end_patern" type="radio"  value="<?php echo ResourceCalendar_Repeat::END_CNT; ?>">
				<label for="rcal_ends_patern_count" ><?php _e("After",RCAL_DOMAIN); ?>
					<input class="rcal_cnt" id="rcal_ends_patern_count_input" ><?php _e("occurrences",RCAL_DOMAIN); ?>
				</label>
			</li>
		</ul>

				<ul>
			<li class="rcal_label" ><label ></label></li>
			<li class="rcal_li">
				<input id="rcal_ends_patern_until" name="rcal_end_patern" type="radio"  value="<?php echo ResourceCalendar_Repeat::END_DATE; ?>">
				<label for="rcal_ends_patern_until"><?php _e("On",RCAL_DOMAIN); ?>
					<input class="rcal_date" id="rcal_repeat_end" >
				</label>
			</li>
		</ul>

		</div>

		<ul><li class="rcal_li"><textarea id="rcal_remark"  ></textarea></li></ul>

		</div>
		<div id="rcal_footer_r3" class="rcal_line">
			<ul>
			<li><a data-role="button" class="rcal_tran_button" id="rcal_exec_regist"  href="javascript:void(0)" ><?php _e('Booking',RCAL_DOMAIN); ?></a></li>
			<li><a data-role="button" class="rcal_tran_button" id="rcal_exec_delete"  href="javascript:void(0)" ><?php _e('Booking Cancel',RCAL_DOMAIN); ?></a></li>
			<li><a data-role="button" class="rcal_tran_button" id="rcal_mainpage_regist" href="#rcal-page-main"><?php _e('Close',RCAL_DOMAIN); ?></a></li>
			</ul>

		</div>
<?php endif;  //556行目あたりの予約をできるかの判定用		?>
	</div>
<?php /*?>
  <div data-role="footer">
	Copyright 2013-2014, Kuu
  </div>
<?php */?>
</div>
	<div id="rcal_dialog" style="display:none"><?php _e("Change only this reservation, or all reservations in the series ?",RCAL_DOMAIN); ?></div>

	<div id="rcal_hidden_photo_area">
<?php //複数写真用 ":

	if ( ! ResourceCalendar_Component::isMobile() ) {
		foreach ($this->resource_datas as $k1 => $d1 ) {
			if ( !empty($d1['photo_result'][0]) ) {


				for($i = 1;$i<count($d1['photo_result']);$i++  ){
					$tmp = "<a href='".$d1['photo_result'][$i]['photo_path']."' rel='resource".$d1['resource_cd']."' class='lightbox' ></a>";
					$url = site_url();
					$url = substr($url,strpos($url,':')+1);
					if (is_ssl() ) {
						$tmp = preg_replace("$([hH][tT][tT][pP]:".$url.")$","https:".$url,$tmp);
					}
					else {
						$tmp = preg_replace("$([hH][tT][tT][pP][sS]:".$url.")$","http:".$url,$tmp);
					}
					echo $tmp;
				}
			}
		}
	}
?>

</div>
<?php
	}	//show_page
}		//class

