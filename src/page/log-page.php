<?php

	require_once(RCAL_PLUGIN_SRC_DIR . 'page/resource-calendar-page.php');


class Log_Page extends ResourceCalendar_Page {

	private $set_items = null;




	function __construct() {
		parent::__construct();
		$this->set_items = array('logged_day','logged_time','operation','logged_remark');
	}




	public function show_page() {
?>

<script type="text/javascript">
		var $j = jQuery


		var target;
		var save_k1 = "";
		var save_item_cds_aft = "";
		var save_user_login = "";
		var save_mail = "";
		var save_tel = "";
		var save_p2 = "";

		var save_operate = "inserted";

		<?php parent::echoClientItem($this->set_items); //for only_branch?>


		$j(document).ready(function() {

			<?php parent::echoSetItemLabel(); ?>

			$j("#rcal_button_div input").addClass("rcal_button");
			$j("#rcal_button_detail").click(function(){
				$j("#rcal_data_detail").toggle();
				if ($j("#rcal_data_detail").is(":visible") ) $j("#rcal_button_detail").val("<?php _e('Hide Details',RCAL_DOMAIN); ?>")
				else $j("#rcal_button_detail").val("<?php _e('Show Details',RCAL_DOMAIN); ?>");
			});

			$j("#rcal_data_detail").hide();
			$j("#rcal_button_detail").val("<?php _e('Show Details',RCAL_DOMAIN); ?>");


			$j("#rcal_button_redisplay").click(function() {
				fnDetailInit();
				target.fnClearTable();					//テーブルデータクリア
				target.fnReloadAjax();				   //再読み込み
				target.fnPageChange( 'first' );		//ページングの最初へ移動

			});


			target = $j("#rcal_lists").dataTable({
				"sAjaxSource": "<?php echo get_bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php?action=rcallog",
				<?php parent::echoDataTableLang(); ?>
				<?php parent::echoTableItem($this->set_items); ?>


				"fnServerParams": function ( aoData ) {
				  aoData.push( { "name": "menu_func","value":"Log_Init" } );
				  aoData.push( { "name": "get_cnt","value":$j("#rcal_get_cnt").val() } );
				  aoData.push( { "name": "nonce","value":"<?php echo $this->nonce; ?>" } );
	},
				"fnDrawCallback": function () {
					$j("#rcal_lists  tbody .rcal_select").click(function(event) {
						fnSelectRow(this);
					});
				},
				fnRowCallback: function( nRow, aData, iDisplayIndex, iDataIndex ) {
					<?php  parent::echoDataTableSelecter("name",false); ?>
					element.append(sel_box);
				}
			});

		});

<?php //taregt_colはtdが前提 ?>
		function fnSelectRow(target_col) {
			fnDetailInit();

			$j(target.fnSettings().aoData).each(function (){
				$j(this.nTr).removeClass("row_selected");
			});
			$j(target_col.parentNode).addClass("row_selected");
			var position = target.fnGetPosition( target_col );
			var setData = target.fnSettings();


			$j("#rcal_logged_day").val(htmlspecialchars_decode(setData['aoData'][position[0]]['_aData']['rcal_logged_day']));
			$j("#rcal_logged_time").val(htmlspecialchars_decode(setData['aoData'][position[0]]['_aData']['rcal_logged_time']));
			$j("#rcal_operation").val(htmlspecialchars_decode(setData['aoData'][position[0]]['_aData']['rcal_operation']));
			$j("#rcal_remark").val(htmlspecialchars_decode(setData['aoData'][position[0]]['_aData']['rcal_remark']));

			$j("#rcal_data_detail").show();

			$j("#rcal_button_detail").val("<?php _e('Hide Details',RCAL_DOMAIN); ?>");

		}


		<?php //parent::echoDisplayErrorLable(); ?>





		function fnDetailInit( ) {

			$j("#rcal_data_detail input[type=\"text\"]").val("");
			$j("#rcal_data_detail textarea").val("");
			<?php parent::echo_clear_error(); ?>
		}


	</script>


	<h2 id="rcal_admin_title"><?php _e('View Log',RCAL_DOMAIN); ?></h2>


	<div id="rcal_button_div" >
	<input id="rcal_button_detail" type="button" />
	<input id="rcal_get_cnt" type="text" value="100" class="rcal_short_title_width"/>
	<input id="rcal_button_redisplay" type="button" value="<?php _e('Redisplay',RCAL_DOMAIN); ?>"/>
	</div>

	<div id="rcal_data_detail" >
		<input type="text" id="rcal_logged_day" value="" />
		<input type="text" id="rcal_logged_time" value="" />
		<textarea id="rcal_operation"  ></textarea>
		<textarea id="rcal_remark"  ></textarea>

		<div class="spacer"></div>

	</div>

	<table class="flexme" id='rcal_lists'>
	<thead>
	</thead>
	</table>

<?php

	}	//show_page
}		//class

