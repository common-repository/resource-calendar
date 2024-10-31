<?php
/*
Plugin Name: Resource Calendar
Plugin URI: http://rcal.mallory.jp
Description: Resource Calendar enables the reservation of resources.
Version: 1.5.6
Author: tanaka-hisao
Author URI: http://rcal.mallory.jp
Text Domain: resource-calendar
Domain Path: /language/
*/

define( 'RCAL_DOMAIN', 'resource-calendar' );
define( 'RCAL_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'RCAL_PLUGIN_NAME', trim( dirname( RCAL_PLUGIN_BASENAME ), DIRECTORY_SEPARATOR ) );
define( 'RCAL_PLUGIN_DIR', plugin_dir_path(__FILE__)  );
define( 'RCAL_PLUGIN_SRC_DIR', RCAL_PLUGIN_DIR . 'src'.DIRECTORY_SEPARATOR );
define( 'RCAL_PLUGIN_URL',  plugin_dir_url(__FILE__) );
define( 'RCAL_PLUGIN_SRC_URL', RCAL_PLUGIN_URL  .DIRECTORY_SEPARATOR . 'src' );
define( 'RCAL_LOG_DIR', '..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR );


define( 'RCAL_DEMO', false);

define( 'RCAL_COLORBOX_SIZE', '80%');

define( 'RCAL_MAX_FILE_SIZE', 10 );	//１０メガまでUPLOAD
define( 'RCAL_UPLOAD_DIR_NAME',DIRECTORY_SEPARATOR.'resource-cal'.DIRECTORY_SEPARATOR);

$uploads = wp_upload_dir();

define( 'RCAL_UPLOAD_DIR', $uploads['basedir'].RCAL_UPLOAD_DIR_NAME);
define( 'RCAL_UPLOAD_URL', $uploads['baseurl'].RCAL_UPLOAD_DIR_NAME);

$rcal_calendar = new Resource_Calendar();

class Resource_Calendar {

	private $user_role = '';

	public function __construct() {


		add_action('init', array( &$this, 'init_session_start'));

		require_once(RCAL_PLUGIN_SRC_DIR.'comp/resource-calendar-component.php');
		register_activation_hook(__FILE__, array( &$this, 'rcal_install'));
		load_plugin_textdomain( RCAL_DOMAIN, false, RCAL_PLUGIN_NAME.'/language' );

		add_action( 'admin_init', array( &$this, 'admin_init' ) );
		add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( &$this, 'admin_javascript' ) );
		add_filter( 'get_pages', array( &$this, 'get_pages' ) );
		add_action( 'wp_enqueue_scripts', array( &$this, 'front_javascript' ) );
		add_shortcode('resource-calendar', array( &$this, 'rcal_calendar_shortcode'));
		add_shortcode('resource-confirm', array( &$this,'rcal_booking_confirm'));


		add_filter('user_contactmethods',array( &$this,'update_profile_fields'),10,1);

		add_action('wp_ajax_rcalbooking', array( &$this,'edit_booking'));
		add_action('wp_ajax_rcalresource', array( &$this,'edit_resource'));
		add_action('wp_ajax_rcalconfig', array( &$this,'edit_config'));
		add_action('wp_ajax_rcallog', array( &$this,'edit_log'));
		add_action('wp_ajax_rcalphoto', array( &$this,'edit_photo'));
		add_action('wp_ajax_rcalsearch', array( &$this,'edit_search'));
		add_action('wp_ajax_rcalmail', array( &$this,'edit_mail'));
		add_action('wp_ajax_rcalconfirm', array( &$this,'edit_confirm'));

		add_action('wp_ajax_rcalcategory', array( &$this,'edit_category'));
		add_action('wp_ajax_rcalreservation', array( &$this,'edit_reservation'));

		add_action('wp_ajax_nopriv_rcalbooking', array( &$this,'edit_booking'));
		add_action('wp_ajax_nopriv_rcalsearch', array( &$this,'edit_search'));
		add_action('wp_ajax_nopriv_rcalconfirm', array( &$this,'edit_confirm'));


		if (RCAL_DEMO ) {
			add_action( 'admin_bar_menu',  array( &$this,'remove_admin_bar_menu'), 201 );
//			add_action('admin_head',  array( &$this,'my_admin_head'));
			add_action('wp_before_admin_bar_render',  array( &$this,'add_new_item_in_admin_bar'));
			add_action('wp_dashboard_setup', array( &$this,'example_remove_dashboard_widgets'));
			remove_action( 'admin_menu', 'wpcf7_admin_menu', 9 );
		}


		if(!file_exists(RCAL_UPLOAD_DIR)){
			mkdir(RCAL_UPLOAD_DIR,0744,true);
		}

		add_action('admin_head',array( &$this, 'display_favicon'));


	}


//_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/ デモFrom
// 管理バーの項目を非表示
public function remove_admin_bar_menu( $wp_admin_bar ) {
	if (RCAL_DEMO && strtolower($this->user_role) != 'administrator') {
		$wp_admin_bar->remove_menu('wp-logo');			// ロゴ
		$wp_admin_bar->remove_menu('site-name');		// サイト名
		$wp_admin_bar->remove_menu('view-site');		// サイト名 -> サイトを表示
		$wp_admin_bar->remove_menu('comments');			// コメント
		$wp_admin_bar->remove_menu('new-content');		// 新規
		$wp_admin_bar->remove_menu('new-post');			// 新規 -> 投稿
		$wp_admin_bar->remove_menu('new-media');		// 新規 -> メディア
		$wp_admin_bar->remove_menu('new-link');			// 新規 -> リンク
		$wp_admin_bar->remove_menu('new-page');			// 新規 -> 固定ページ
		$wp_admin_bar->remove_menu('new-user');			// 新規 -> ユーザー
		$wp_admin_bar->remove_menu('updates');			// 更新
		$wp_admin_bar->remove_menu('my-account');		// マイアカウント
		$wp_admin_bar->remove_menu('user-info');		// マイアカウント -> プロフィール
		$wp_admin_bar->remove_menu('edit-profile');		// マイアカウント -> プロフィール編集
		$wp_admin_bar->remove_menu('logout');			// マイアカウント -> ログアウト
	}
}
// 管理バーのヘルプメニューを非表示にする
public function my_admin_head(){
	if (RCAL_DEMO && strtolower($this->user_role) != 'administrator')
		 echo '<style type="text/css">#contextual-help-link-wrap{display:none;}</style>';
}

public function add_new_item_in_admin_bar() {
	if (RCAL_DEMO && strtolower($this->user_role) != 'administrator') {
		global $wp_admin_bar;
		$wp_admin_bar->add_menu(array(
		'id' => 'new_item_in_admin_bar',
		'title' => __('Log Out'),
		'href' => wp_logout_url()
		));
	}
}
public function example_remove_dashboard_widgets() {
	if (RCAL_DEMO && strtolower($this->user_role) != 'administrator') {
		global $wp_meta_boxes;
		unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now']);		// 現在の状況
		unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_comments']);	// 最近のコメント
		unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_incoming_links']);	// 被リンク
		unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_plugins']);			// プラグイン
		unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_quick_press']);		// クイック投稿
		unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_recent_drafts']);		// 最近の下書き
		unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']);			// WordPressブログ
		unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_secondary']);			// WordPressフォーラム
	}
}
//_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/ デモTO

	public function init_session_start(){
		if (!isset($_SESSION))  session_start();
	}

	public function update_profile_fields( $contactmethods ) {
		if(!array_key_exists('zip', $contactmethods)) $contactmethods['zip']= __('zip',RCAL_DOMAIN);
		if(!array_key_exists('address', $contactmethods))$contactmethods['address']= __('address',RCAL_DOMAIN);
		if(!array_key_exists('tel', $contactmethods))$contactmethods['tel']= __('tel',RCAL_DOMAIN);
		if(!array_key_exists('mobile', $contactmethods))$contactmethods['mobile']= __('mobile',RCAL_DOMAIN);

		return $contactmethods;
	}


	public function admin_init() {
		if (!$this->_is_this_plugin_page()) return;
		remove_action( 'admin_notices', 'update_nag', 3 );
	}

	private function _is_this_plugin_page () {
		global $plugin_page;
		if ( ! isset( $plugin_page ) || ( substr($plugin_page,0,strlen(RCAL_DOMAIN))  !=	RCAL_DOMAIN )) {
			return false;
		}
		return true;
	}

	private function _get_userdata (&$user_role) {
		$edit_menu = array();
// 		global $current_user;
// 		get_currentuserinfo();
		$current_user = wp_get_current_user();
		$user_roles = $current_user->roles;
		$user_role = array_shift($user_roles);
	}



	public function admin_menu() {
		$show_menu = $this->_get_userdata($this->user_role);
		$result =  unserialize(get_option( 'RCAL_CONFIG'));
		if (empty($result['RCAL_CONFIG_RESOURCE_NAME']) ) {
			$name = __("Resource",RCAL_DOMAIN);
		}
		else {
			$name = $result['RCAL_CONFIG_RESOURCE_NAME'];
		}

		$auth = 'manage_options';
		if (RCAL_DEMO ) $auth = 'edit_posts';

		$maintenance = RCAL_DOMAIN.'_maintenace';
		add_menu_page(
			$name . __('Booking',RCAL_DOMAIN),
			$name .__('Booking',RCAL_DOMAIN),
			$auth,
			$maintenance,
			array( &$this,'edit_config'),
			WP_PLUGIN_URL.'/resource-calendar/images/menu-icon.png' );

		$my_admin_page = add_submenu_page(
				$maintenance,
				__('Environment Setting',RCAL_DOMAIN),
				__('Environment Setting',RCAL_DOMAIN),
				$auth,
				$maintenance,
				array( &$this, 'edit_config' ) );

		$my_admin_page = add_submenu_page(
				$maintenance,
				$name ." ".__('Information',RCAL_DOMAIN),
				$name ." ".__('Information',RCAL_DOMAIN),
				$auth ,
				RCAL_DOMAIN.'_resource',
				array( &$this, 'edit_resource' ) );

		if ( strtolower($this->user_role) == 'administrator') {
			$my_admin_page = add_submenu_page(
				$maintenance,
				__('View Log',RCAL_DOMAIN),
				__('View Log',RCAL_DOMAIN),
				$auth,
				RCAL_DOMAIN.'_log',
				array( &$this, 'edit_log' ) );
		}

		$my_admin_page = add_submenu_page(
				$maintenance,
				__('Mail Setting',RCAL_DOMAIN),
				__('Mail Setting',RCAL_DOMAIN),
				$auth ,
				RCAL_DOMAIN.'_mail',
				array( &$this, 'edit_mail' ) );

		if ($result['RCAL_CONFIG_USE_SUBMENU'] == ResourceCalendar_Config::USE_SUBMENU ) {
			$my_admin_page = add_submenu_page(
				$maintenance,
				__('Setting reservation details',RCAL_DOMAIN),
				__('Setting reservation details',RCAL_DOMAIN),
				$auth ,
				RCAL_DOMAIN.'_category',
				array( &$this, 'edit_category' ) );
		}

		$my_admin_page = add_submenu_page(
				$maintenance,
				__('Reservations',RCAL_DOMAIN),
				__('Reservations',RCAL_DOMAIN),
				$auth ,
				RCAL_DOMAIN.'_reservation',
				array( &$this, 'edit_reservation' ) );


		if (RCAL_DEMO && strtolower($this->user_role) != 'administrator') {
			global $menu;
			unset($menu[2]);//ダッシュボード
			unset($menu[4]);//メニューの線1
			unset($menu[5]);//ｐｏｓｔ
			unset($menu[10]);//メディア
			unset($menu[15]);//リンク
			unset($menu[20]);//ページ
			unset($menu[25]);//コメント
			unset($menu[59]);//メニューの線2
			unset($menu[60]);//テーマ
			unset($menu[65]);//プラグイン
			unset($menu[70]);//プロファイル
			unset($menu[75]);//ツール
			unset($menu[80]);//設定
			unset($menu[90]);//メニューの線3
		}
	}

	public function display_favicon(){
		if (!$this->_is_this_plugin_page()) return;
		echo "<!-- Favicon  From -->\r\n";
		echo '<link rel="shortcut icon" href="'.RCAL_PLUGIN_URL.'/images/favicon.ico">';
		echo "<!-- Favicon  To   -->\r\n";
	}


	public function edit_config() {
		require_once( RCAL_PLUGIN_SRC_DIR.'/control/config-control.php' );
		$control = new Config_Control();
		$control->exec();
	}
	public function edit_resource() {
		require_once( RCAL_PLUGIN_SRC_DIR.'/control/resource-control.php' );
		$control = new Resource_Control();
		$control->exec();
	}
	public function edit_mail() {
		require_once( RCAL_PLUGIN_SRC_DIR.'/control/mail-control.php' );
		$control = new Mail_Control();
		$control->exec();
	}

	public function edit_log() {
		require_once( RCAL_PLUGIN_SRC_DIR.'/control/log-control.php' );
		$control = new Log_Control();
		$control->exec();
	}

	public function edit_photo() {
		require_once( RCAL_PLUGIN_SRC_DIR.'/control/photo-control.php' );
		$control = new Photo_Control();
		$control->exec();
	}

	public function edit_booking() {
		require_once( RCAL_PLUGIN_SRC_DIR.'/control/booking-control.php' );
		$control = new Booking_Control();
		$control->exec();
	}

	public function edit_search() {
		require_once( RCAL_PLUGIN_SRC_DIR.'/control/search-control.php' );
		$control = new Search_Control();
		$control->exec();
	}

	public function edit_confirm() {
		require_once( RCAL_PLUGIN_SRC_DIR.'/control/confirm-control.php' );
		$control = new Confirm_Control();
		$control->exec();
	}

	public function edit_category() {
		require_once( RCAL_PLUGIN_SRC_DIR.'/control/category-control.php' );
		$control = new Category_Control();
		$control->exec();
	}



	public function edit_reservation() {
		require_once( RCAL_PLUGIN_SRC_DIR.'/control/reservation-control.php' );
		$control = new Reservation_Control();
		$control->exec();
	}

	public function admin_javascript($hook_suffix) {
		if (!$this->_is_this_plugin_page()) return;

		wp_enqueue_script( 'jquery');
		wp_enqueue_script( 'jquery-ui-datepicker');
		wp_enqueue_script( 'jquery-ui-dialog');
		wp_enqueue_script( 'edit', RCAL_PLUGIN_URL.'js/jquery.jeditable.js',array( 'jquery' ) );
		wp_enqueue_script( 'dataTables', RCAL_PLUGIN_URL.'js/jquery.dataTables.js',array( 'jquery' ) );
		wp_enqueue_script( 'dataTables_plugin1', RCAL_PLUGIN_URL.'js/fnReloadAjax.js',array( 'dataTables' ) );
		wp_enqueue_script( 'jsonparse', RCAL_PLUGIN_URL.'js/jquery.json-2.4.min.js',array( 'jquery' ) );
		wp_enqueue_script( 'dateformat', RCAL_PLUGIN_URL.'js/jquery.dateFormat.js',array( 'jquery' ) );
		wp_enqueue_script( 'dropzone', RCAL_PLUGIN_URL.'js/dropzone.min.js',array( 'jquery' ) );
		wp_enqueue_script( 'jquery-ui-sortable');
		wp_enqueue_script( 'colorbox', RCAL_PLUGIN_URL.'js/jquery.colorbox-min.js',array( 'jquery' ) );
		wp_enqueue_script( 'rcal', RCAL_PLUGIN_URL.'js/resource-calendar-admin.js',array( 'jquery' ) );

		//YYYY年MM月DD日にするのをやめ
		remove_action( 'admin_enqueue_scripts', 'wp_localize_jquery_ui_datepicker', 1000 );

		global $wp_scripts;
		$ui = $wp_scripts->query('jquery-ui-core');
		wp_enqueue_style('jquery-ui-smoothness', "//ajax.googleapis.com/ajax/libs/jqueryui/{$ui->ver}/themes/smoothness/jquery-ui.min.css",false, null );
		wp_enqueue_style('dataTables', RCAL_PLUGIN_URL.'css/dataTables.css');
		wp_enqueue_style('rcal', RCAL_PLUGIN_URL.'css/resource-calendar-admin.css');
		wp_enqueue_style('rcal-date', RCAL_PLUGIN_URL.'css/resource-calendar-datepicker.css');
		wp_enqueue_style('dropzone', RCAL_PLUGIN_URL.'css/dropzone.css');
		wp_enqueue_style('colorbox', RCAL_PLUGIN_URL.'css/colorbox.css');
	}

	public function front_javascript() {

		wp_enqueue_script( 'jquery');
		wp_enqueue_script( 'jquery-ui-datepicker');
		wp_enqueue_script( 'jquery-ui-dialog');
		wp_enqueue_script( 'dateformat', RCAL_PLUGIN_URL.'js/jquery.dateFormat.js',array( 'jquery' ) );
		wp_enqueue_script( 'rcal', RCAL_PLUGIN_URL.'js/resource-calendar.js',array( 'jquery' ) );
		global $wp_scripts;
		$ui = $wp_scripts->query('jquery-ui-core');
		wp_enqueue_style('jquery-ui-smoothness', "//ajax.googleapis.com/ajax/libs/jqueryui/{$ui->ver}/themes/smoothness/jquery-ui.min.css",false, null );
		wp_enqueue_style('rcal', RCAL_PLUGIN_URL.'css/resource-calendar.css');
		wp_enqueue_style('rcal-date', RCAL_PLUGIN_URL.'css/resource-calendar-datepicker.css');

		//YYYY年MM月DD日にするのをやめ
		remove_action( 'wp_enqueue_scripts', 'wp_localize_jquery_ui_datepicker', 1000 );

		if ( ! ResourceCalendar_Component::isMobile() ) {
			wp_enqueue_script( 'colorbox', RCAL_PLUGIN_URL.'js/jquery.colorbox-min.js',array( 'jquery' ) );
			wp_enqueue_style('colorbox', RCAL_PLUGIN_URL.'css/colorbox.css');

		}
	}


	public function rcal_calendar_shortcode($atts) {
		extract(shortcode_atts(array('branch_cd' => '1'), $atts));
		require_once(RCAL_PLUGIN_SRC_DIR.'/control/booking-control.php');
		$control = new Booking_Control();
		$control->exec();
	}

	public function rcal_booking_confirm($atts) {
		require_once(RCAL_PLUGIN_SRC_DIR.'/control/confirm-control.php');
		$control = new Confirm_Control();
		$control->exec();
	}


	function _isExixtColumn($table_name ,$column_name){
		global $wpdb;
		$sql = "show columns from ".$wpdb->prefix.$table_name;
		$columns = $wpdb->get_results($sql,ARRAY_A);
		foreach ($columns as $k1 => $d1 ) {
			if ($d1['Field'] == $column_name ) return true;
		}
		return false;
	}


	function get_pages( $pages ) {
		$confirm_page_id =  get_option('rcal_confirm_page_id');
		for ( $i = 0; $i < count($pages); $i++ ) {
			if ( !empty($pages[$i]->ID) && $pages[$i]->ID == $confirm_page_id  )
				unset( $pages[$i] );
		}

		return $pages;
	}


	function rcal_install(){


		if (!get_option('rcal_confirm_page_id') ) {
			$post = array(
				'ID' => '' 	//[ <投稿 ID> ] // 既存の投稿を更新する場合。
				,'menu_order' => 999 //[ <順序値> ] // 追加する投稿が固定ページの場合、ページの並び順を番号で指定できます。
				,'comment_status' => 'closed'	//[ 'closed' | 'open' ] // 'closed' はコメントを閉じます。
				,'ping_status' => 'closed' //[ 'closed' | 'open' ] // 'closed' はピンバック／トラックバックをオフにします。
				,'pinged' => '' //[ ? ] // ピンバック済。
				,'post_author' => '' //[ <user ID> ] // 作成者のユーザー ID。
				,'post_content' => '[resource-confirm]' //[ <投稿の本文> ] // 投稿の全文。
				,'post_date' => date_i18n('Y-m-d H:i:s') //[ Y-m-d H:i:s ] // 投稿の作成日時。
				,'post_date_gmt' => gmdate('Y-m-d H:i:s') //[ Y-m-d H:i:s ] // 投稿の作成日時（GMT）。
				,'post_excerpt' => '' //[ <抜粋> ] // 投稿の抜粋。
				,'post_name' => ''	//[ <スラッグ名> ] // 投稿スラッグ。
				,'post_parent' => 0	//[ <投稿 ID> ] // 親投稿の ID。
				,'post_password' => '' //[ <投稿パスワード> ] // 投稿の閲覧時にパスワードが必要になります。
				,'post_status' => 'publish' //[ 'draft' | 'publish' | 'pending'| 'future' ] // 公開ステータス。
				,'post_title' => __('Reservation Confirm',RCAL_DOMAIN)	//[ <タイトル> ] // 投稿のタイトル。
				,'post_type' => 'page' //[ 'post' | 'page' ] // 投稿タイプ名。
				,'tags_input' => '' //[ '<タグ>, <タグ>, <...>' ] // 投稿タグ。
				,'to_ping' => ''	//[ ? ] //?
			);

			$id = wp_insert_post( $post );
			update_option('rcal_confirm_page_id', $id);
		}


		global $wpdb;


		$charset_collate = '';

		if ( ! empty($wpdb->charset) )
			$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
		if ( ! empty($wpdb->collate) )
			$charset_collate .= " COLLATE $wpdb->collate";


		$current = date_i18n('Y-m-d H:i:s');


		if (get_option('rcal_installed') ) {
		}
		else {
			$wpdb->query("	CREATE  TABLE IF NOT EXISTS ".$wpdb->prefix."rcal_reservation (
								`reservation_cd`	INT not null  AUTO_INCREMENT,
								`resource_cd`		INT,
								`user_login`		varchar(60) default NULL,
								`name`		varchar(255) default NULL,
								`mail`		varchar(300) default NULL,
								`tel`		varchar(30) default NULL,
								`time_from`		DATETIME,
								`time_to`		DATETIME,
								`remark`		TEXT,
								`memo`			TEXT,
								`notes`			TEXT,
								`activate_key`	VARCHAR(8),
								`status`		INT default 0,
								`repeat_cd`		INT default 1,
								`delete_flg`	INT default 0,
								`insert_time`	DATETIME,
								`update_time`	DATETIME,
							  PRIMARY KEY  (`reservation_cd`),
							  INDEX idx_repeat_cd(`repeat_cd`)
							) ".$charset_collate);


			$wpdb->query("	CREATE  TABLE IF NOT EXISTS ".$wpdb->prefix."rcal_repeat (
								`repeat_cd`		INT not null AUTO_INCREMENT,
								`repeat_patern`	INT default 0,
								`repeat_every`	INT default 1,
								`valid_from`	DATETIME default '0000-00-00 00:00:00' ,
								`repeat_end_patern`	INT default 1,
								`valid_to`	DATETIME default '2099-12-31 00:00:00' ,
								`repeat_cnt`	INT default 0,
								`repeat_weeks`	varchar(100),
								`delete_flg`	INT default 0,
								`insert_time`	DATETIME,
								`update_time`	DATETIME,
							  PRIMARY KEY  (`repeat_cd`)
							) AUTO_INCREMENT=0 ".$charset_collate);


			$wpdb->query("	CREATE  TABLE IF NOT EXISTS ".$wpdb->prefix."rcal_resource (
								`resource_cd`		INT not null AUTO_INCREMENT,
								`name`		varchar(255) default NULL,
								`valid_from`	DATETIME default '0000-00-00 00:00:00' ,
								`valid_to`	DATETIME default '2099-12-31 00:00:00' ,
								`photo`			TEXT default null,
								`display_sequence`		INT default 0,
								`max_setting`		INT default 1,
								`setting_patern_cd`	INT default 1,
								`setting_data`	TEXT,
								`remark`		TEXT,
								`memo`			TEXT,
								`notes`			TEXT,
								`delete_flg`	INT default 0,
								`insert_time`	DATETIME,
								`update_time`	DATETIME,
							  PRIMARY KEY  (`resource_cd`)
							) ".$charset_collate);


			$wpdb->query("	CREATE  TABLE IF NOT EXISTS ".$wpdb->prefix."rcal_photo (
				`photo_id`		INT NOT NULL AUTO_INCREMENT,
				`photo_name`		varchar(255) default NULL,
				`photo_path`		varchar(255) default NULL,
				`photo_resize_path`		varchar(255) default NULL,
				`width`			INT NOT NULL default '0',
				`height`			INT NOT NULL default '0',
				`delete_flg` tinyint NOT NULL default '0',
				`insert_time` DATETIME,
				`update_time` DATETIME,
				UNIQUE  (`photo_id`)
			 ) ".$charset_collate);

			$wpdb->query("	CREATE  TABLE IF NOT EXISTS ".$wpdb->prefix."rcal_log (
				`no`		INT not null AUTO_INCREMENT,
				`sql`			TEXT,
				`remark`		TEXT,
				`insert_time`	DATETIME,
			  PRIMARY KEY  (`no`)
			) ".$charset_collate);

			$wpdb->query("	CREATE  TABLE IF NOT EXISTS ".$wpdb->prefix."rcal_category (
				`category_cd`	INT not null AUTO_INCREMENT,
				`category_name`	TEXT,
				`category_patern`	INT ,
				`category_values`	TEXT ,
				`display_sequence`		INT default 0,
				`delete_flg`	INT default 0,
				`insert_time`	DATETIME,
				`update_time`	DATETIME,
				PRIMARY KEY (`category_cd`)
			) ".$charset_collate);

			$current_time = date_i18n('Y-m-d H:i:s');

			//phpunitで単独で動かすと再度insertするので件数をここで確認する
			$result = $wpdb->get_results("SELECT COUNT(*) AS cnt FROM ".$wpdb->prefix."rcal_resource ",ARRAY_A);
			if (isset($result[0]['cnt']) && $result[0]['cnt'] == 0 ) {
				$wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix."rcal_repeat ".
						" (repeat_cd,repeat_patern,repeat_every,repeat_end_patern,repeat_cnt,repeat_weeks,delete_flg,insert_time,update_time) "
						." VALUES (0,1,0,1,0,null,0,%d,%d);",
						$current,$current));

				$wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix."rcal_resource ".
					" (name,setting_patern_cd,setting_data,remark,memo,notes,insert_time,update_time,display_sequence) "
					." VALUES (%s,%d,%s,'','','',%s,%s,%d);",
					__('Resource Sample 1',RCAL_DOMAIN),
					ResourceCalendar_Config::SETTING_PATERN_TIME,
					'',
					$current,$current,1));

				$wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix
					."rcal_resource (name,setting_patern_cd,setting_data,remark,memo,notes,insert_time,update_time,display_sequence) "
					." VALUES (%s,%d,%s,'','','',%s,%s,%d);",
					__('Resource Sample 2',RCAL_DOMAIN),
					ResourceCalendar_Config::SETTING_PATERN_ORIGINAL,
					__('AM1,09:00,10:30;AM2,10:30,12:00;PM1,13:00,14:30;PM2,14:30,16:00',RCAL_DOMAIN),
					$current,$current,2));

				$wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix
					."rcal_resource (name,setting_patern_cd,setting_data,remark,memo,notes,insert_time,update_time,display_sequence) "
					." VALUES (%s,%d,%s,'','','',%s,%s,%d);",
					__('Resource Sample 3',RCAL_DOMAIN),
					ResourceCalendar_Config::SETTING_PATERN_ORIGINAL,
					__('AM,09:00,12:00;PM,13:00,15:00',RCAL_DOMAIN),
					$current,$current,3));
				//1がOPTION、2がチェック、3がテキスト、4がセレクト
				$wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix."rcal_category (category_name,category_patern,category_values,display_sequence,insert_time,update_time) VALUES (%s,1,%s,1,%s,%s)",__('Option',RCAL_DOMAIN),__('opt1,opt2,opt3,opt4,opt5,opt6,opt7,opt8,opt9,opt10',RCAL_DOMAIN),$current,$current));
				$wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix."rcal_category (category_name,category_patern,category_values,display_sequence,insert_time,update_time) VALUES (%s,2,%s,2,%s,%s)",__('Check',RCAL_DOMAIN),__('Check here1,Check here2,Check here3',RCAL_DOMAIN),$current,$current));
				$wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix."rcal_category (category_name,category_patern,category_values,display_sequence,insert_time,update_time) VALUES (%s,3,null,3,%s,%s)",__('Text',RCAL_DOMAIN),$current,$current));
				$wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix."rcal_category (category_name,category_patern,category_values,display_sequence,insert_time,update_time) VALUES (%s,4,%s,4,%s,%s)",__('Select',RCAL_DOMAIN),__('1,2,3,4,5,6,7,8,9,10',RCAL_DOMAIN),$current,$current));
				$wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix."rcal_category (category_name,category_patern,category_values,display_sequence,insert_time,update_time) VALUES (%s,5,null,5,%s,%s)",__('Mail',RCAL_DOMAIN),$current,$current));
			}
			$country = get_locale();
			if (isset($country) && file_exists(RCAL_PLUGIN_DIR.'/language/holiday-'.$country.'.php') )require_once(RCAL_PLUGIN_DIR.'/language/holiday-'.$country.'.php');
			else require_once(RCAL_PLUGIN_DIR.'/language/holiday.php');
			if (isset($holiday)) {
				update_option('rcal_holiday', serialize($holiday));
			}

			update_option('rcal_installed', 1);
		}


	}


}


?>