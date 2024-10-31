<?php

	require_once(RCAL_PLUGIN_SRC_DIR . 'page/resource-calendar-page.php');


class Category_Page extends ResourceCalendar_Page {


	private $category_patern_datas = null;
	const SEQ_COLUMN = 3;

	public function __construct() {
		parent::__construct();
		$this->set_items = array('category_name','category_patern','category_value');

	}

	public function set_category_patern_datas ($set_datas) {
		$this->category_patern_datas = $set_datas;
	}



	public function show_page() {
?>

<script type="text/javascript">
		var $j = jQuery


		var target;
		var save_k1 = "";
		var save_all_flg = false;

		var staff_items = Array();

		<?php parent::echoClientItem($this->set_items); //for only_branch?>

		$j(document).ready(function() {
			<?php parent::echoSetItemLabel(); ?>
			<?php parent::echoCommonButton();			//共通ボタン	?>
			target = $j("#rcal_lists").dataTable({
				"sAjaxSource": "<?php echo get_bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php?action=rcalcategory",
				<?php parent::echoDataTableLang(); ?>
				<?php // ソートモードにしない ↓のbSortをfalseに
 					parent::echoTableItem(array('category_name','display_sequence','no_edit_remark'),false,"120px",true);
				//for only_branch?>
				"bSort":false,
				"fnServerParams": function ( aoData ) {
				  aoData.push( { "name": "menu_func","value":"Category_Init" } )
				},



				fnRowCallback: function( nRow, aData, iDisplayIndex, iDataIndex ) {
					<?php parent::echoDataTableSelecter("rcal_category_name"); ?>
					<?php parent::echoDataTableDisplaySequence(self::SEQ_COLUMN,"rcal_"); ?>

				},
			});
			$j("#rcal_category_patern").change(function() {
					$j("#rcal_category_value").attr("readonly",false);
					if ($j(this).val() == <?php echo ResourceCalendar_Category::TEXT; ?>
						|| $j(this).val() == <?php echo ResourceCalendar_Category::MAIL; ?>) {
						$j("#rcal_category_value").val("");
						$j("#rcal_category_value").attr("readonly",true);
					}
			});



		});


		<?php parent::echoDataTableSeqUpdateRow("category","rcal_category_cd","rcal_"); ?>

		function fnSelectRow(target_col) {
			fnDetailInit();


			$j(target.fnSettings().aoData).each(function (){
				$j(this.nTr).removeClass("row_selected");
			});
			$j(target_col.parentNode).addClass("row_selected");
			var position = target.fnGetPosition( target_col );
			var setData = target.fnSettings();

			save_k1 = position[0];

			$j("#rcal_category_name").val(htmlspecialchars_decode(setData['aoData'][position[0]]['_aData']['rcal_category_name']));
			$j("#rcal_category_patern").val(setData['aoData'][position[0]]['_aData']['rcal_category_patern']);
			$j("#rcal_category_value").val(htmlspecialchars_decode(setData['aoData'][position[0]]['_aData']['rcal_category_values']));

			$j("#rcal_category_value").attr("readonly",false);
			if (setData['aoData'][position[0]]['_aData']['rcal_category_patern'] == <?php echo ResourceCalendar_Category::TEXT; ?>
				|| setData['aoData'][position[0]]['_aData']['rcal_category_patern'] == <?php echo ResourceCalendar_Category::MAIL; ?> ) {
				$j("#rcal_category_value").attr("readonly",true);
			}

			$j("#rcal_button_update").removeAttr("disabled");
			$j("#rcal_button_clear").show();

			$j("#rcal_data_detail").show();
			$j("#rcal_button_detail").val("<?php _e('Hide Details',RCAL_DOMAIN);  ?>");



		}

		<?php	parent::echoDataTableDeleteRow("category","rcal_category"); ?>

		function fnClickAddRow(operate) {
			if($j("#rcal_category_patern").val() == <?php echo ResourceCalendar_Category::TEXT; ?>
				|| $j("#rcal_category_patern").val() == <?php echo ResourceCalendar_Category::MAIL; ?>) {
				if ( ! checkItem("rcal_data_detail","rcal_category_value") ) return false;
			}
			else {
				if ( ! checkItem("rcal_data_detail") ) return false;
			}

			var category_cd = "";
			var display_sequence = 0;
			var setData = target.fnSettings();
			if ( save_k1 !== ""  ) {
				category_cd = setData['aoData'][save_k1]['_aData']['rcal_category_cd'];
				display_sequence = setData['aoData'][save_k1]['_aData']['rcal_display_sequence'];
			}
			$j.ajax({
				 	type: "post",
					url:  "<?php echo get_bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php?action=rcalcategory",
					dataType : "json",
					data: {
						"rcal_category_cd":category_cd,
						"no":save_k1,
						"type":operate,
						"rcal_category_name":$j("#rcal_category_name").val(),
						"rcal_category_patern":$j("#rcal_category_patern").val(),
						"rcal_category_value":$j("#rcal_category_value").val(),
						"rcal_display_sequence":display_sequence,
						"nonce":"<?php echo $this->nonce; ?>",
						"menu_func":"Category_Edit"

					},

					success: function(data) {
						if (data === null || data.status == "Error" ) {
							alert(data.message);
						}
						else {
							if (operate =="inserted" ) {
								target.fnAddData( data.set_data );
							}
							else {
								target.fnUpdate( data.set_data ,parseInt(save_k1) );
							}
							fnDetailInit();
							$j(target.fnSettings().aoData).each(function (){
								$j(this.nTr).removeClass("row_selected");
							});

						}
					},
					error:  function(XMLHttpRequest, textStatus){
						alert (textStatus);
					}
			 });
		}


		function fnDetailInit() {
			$j("#rcal_data_detail input[type=\"text\"]").val("");
			$j("#rcal_data_detail select").val("");
			$j("#rcal_data_detail textarea").val("");

			$j("#rcal_button_update").attr("disabled", "disabled");
			$j("#rcal_category_value").attr("readonly",false);

			save_k1 = "";
			<?php parent::echo_clear_error(); ?>

		}

	<?php parent::echoCheckClinet(array('chk_required','lenmax')); ?>



	</script>

	<h2 id="rcal_admin_title"><?php _e('Setting reservation details',RCAL_DOMAIN); ?></h2>
	<?php echo parent::echoShortcode(); ?>
	<div id="rcal_button_div" >
	<input id="rcal_button_insert" type="button" value="<?php _e('Add',RCAL_DOMAIN); ?>"/>
	<input id="rcal_button_update" type="button" value="<?php _e('Update',RCAL_DOMAIN); ?>"/>
	<input id="rcal_button_clear" type="button" value="<?php _e('Clear',RCAL_DOMAIN); ?>"/>
	<input id="rcal_button_detail" type="button"/>
	</div>


	<div id="rcal_data_detail" >
		<input type="text" id="rcal_category_name" value="" />
		<select name="rcal_category_patern" id="rcal_category_patern" >
			<option value=""><?php _e('please select',RCAL_DOMAIN); ?></option>
		<?php
			foreach($this->category_patern_datas as $k1 => $d1 ) {
				echo '<option value="'.$k1.'">'.$d1.'</option>';
			}
		?>
		</select>
		<textarea id="rcal_category_value"  ></textarea>
		<div class="spacer"></div>

	</div>

	<table class="flexme" id='rcal_lists'>
	<thead>
	</thead>
	</table>
<?php
	}	//show_page
}		//class

