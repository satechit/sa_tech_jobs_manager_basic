<?php

use SAJobsF\Jobs\Jobs;
use SAJobsF\Jobs\Logs;
use SAJobsF\Jobs\ReceivedJobs;

if ( ! defined( 'ABSPATH' ) ) {
	echo 'I am WordPress plugin file. Please do not run me directly.';
	exit();
}

if ( ! function_exists( 'mime_content_type' ) ) {

	function mime_content_type( $filename ) {

		$mime_types = [
			'txt'  => 'text/plain',
			'htm'  => 'text/html',
			'html' => 'text/html',
			'php'  => 'text/html',
			'css'  => 'text/css',
			'js'   => 'application/javascript',
			'json' => 'application/json',
			'xml'  => 'application/xml',
			'swf'  => 'application/x-shockwave-flash',
			'flv'  => 'video/x-flv',

			// images
			'png'  => 'image/png',
			'jpe'  => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'jpg'  => 'image/jpeg',
			'gif'  => 'image/gif',
			'bmp'  => 'image/bmp',
			'ico'  => 'image/vnd.microsoft.icon',
			'tiff' => 'image/tiff',
			'tif'  => 'image/tiff',
			'svg'  => 'image/svg+xml',
			'svgz' => 'image/svg+xml',

			// archives
			'zip'  => 'application/zip',
			'rar'  => 'application/x-rar-compressed',
			'exe'  => 'application/x-msdownload',
			'msi'  => 'application/x-msdownload',
			'cab'  => 'application/vnd.ms-cab-compressed',

			// audio/video
			'mp3'  => 'audio/mpeg',
			'qt'   => 'video/quicktime',
			'mov'  => 'video/quicktime',

			// adobe
			'pdf'  => 'application/pdf',
			'psd'  => 'image/vnd.adobe.photoshop',
			'ai'   => 'application/postscript',
			'eps'  => 'application/postscript',
			'ps'   => 'application/postscript',

			// ms office
			'doc'  => 'application/msword',
			'rtf'  => 'application/rtf',
			'xls'  => 'application/vnd.ms-excel',
			'ppt'  => 'application/vnd.ms-powerpoint',

			// open office
			'odt'  => 'application/vnd.oasis.opendocument.text',
			'ods'  => 'application/vnd.oasis.opendocument.spreadsheet',
		];

		$ext = strtolower( array_pop( explode( '.', $filename ) ) );
		if ( array_key_exists( $ext, $mime_types ) ) {
			return $mime_types[ $ext ];
		} else if ( function_exists( 'finfo_open' ) ) {
			$finfo    = finfo_open( FILEINFO_MIME );
			$mimetype = finfo_file( $finfo, $filename );
			finfo_close( $finfo );

			return $mimetype;
		} else {
			return 'application/octet-stream';
		}
	}
}

if ( ! class_exists( 'FreeSATechJobsManager' ) ) {
	class FreeSATechJobsManager {

		/**
		 * Use ''application/x-zip-compressed' for zip files
		 *
		 * @var array
		 */
		private $file_input_accept = [
			'application/msword',
			'application/pdf',
			'image/*',
			'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
		];

		const DOMAIN = 'satech_jobs_domain';
		const JOBS_P_TITLE = "SA Jobs Manager";
		const JOBS_P_PATH = __DIR__;
		const JOBS_F_URL = JOBS_F_URL;
		const URL = JOBS_F_URL;
		const JOBS_P_DOMAIN = 'SAjobsF_';
		const JOBS_P_CAPABILITY = 'manage_options';
		const OPTIONS_KEY = 'SAJobsF_Jobs';

		const CurrentDBVersion = '1.1';
		const CurrentDBUpdateDate = '28-03-2020';
		const AjaxKey = 'SA_jobsF_jobs_ajax';

		public $is_perma_enabled;

		public $Tables;

		public $DB2;

		private $plugin_slug;
		private $api_url;

		public function __construct() {
			$this->plugin_slug = basename( dirname( JOBSP_F_FILE ) );
			if ( $this->is_developer_pc() ) {
				$this->api_url = 'http://localhost/satech/jobs/';
			} else {
				$this->api_url = 'http://www.satechitcompany.com/__wp_updates__/__jobs__/';
			}

			global $wpdb;
			if ( ! class_exists( 'MysqliDb' ) ) {
				include_once( __DIR__ . "/src/MysqliDb.php" );
			}
			try {
				$this->DB2 = new \MysqliDb ( DB_HOST, DB_USER, DB_PASSWORD, DB_NAME );
			} catch ( \Exception $e ) {
				die( $e->getMessage() );
			}

			$this->Tables['job_ads']          = $wpdb->prefix . 'SA_jobsF';
			$this->Tables['job_applications'] = $wpdb->prefix . 'SA_jobsF_apps';
			//$this->Tables['application_comments'] = $wpdb->prefix . 'SA_jobsF_received_notes';
			$this->Tables['categories'] = $wpdb->prefix . 'SA_jobsF_categories';
			//$this->Tables['templates']            = $wpdb->prefix . 'SA_jobsF_templates';
			$this->Tables['currencies'] = $wpdb->prefix . 'SA_jobsF_currencies';
			//$this->Tables['email_logs']           = $wpdb->prefix . 'SA_jobsF_email_logs';
			//$this->Tables['logs']                 = $wpdb->prefix . 'SA_jobsF_logs';

			/**
			 * If new DB version available then run install new table.
			 */

			if ( version_compare( $this->get_db_version(), self::CurrentDBVersion, '<' ) ) {
				$this->install();
			}

			/**
			 * Checking is perma link is enabled/disabled
			 */
			$this->is_perma_enabled = ( '' != \get_option( 'permalink_structure' ) );

			if ( is_admin() ) {
				add_action( 'admin_menu', [ $this, 'admin_menu' ] );
				add_filter( 'submenu_file', [ $this, 'add_job_hidden' ] );

				add_action( 'admin_enqueue_scripts', [ $this, 'load_scripts_and_styles' ] );

				## Creating settings link
				add_filter( 'plugin_action_links_' . plugin_basename( JOBSP_F_FILE ), [ $this, 'add_action_links' ] );

				## Setting up database
				register_activation_hook( JOBSP_F_FILE, [ $this, 'install' ] );

				## Enabling ajax requests
				add_action( 'wp_ajax_' . self::AjaxKey, [ $this, 'ajax' ] );

				## Enabling ajax request for front-end
				add_action( 'wp_ajax_nopriv_' . self::AjaxKey, [ $this, 'ajax' ] );

				## Admin notices.
				add_action( 'admin_notices', [ $this, 'show_admin_notices' ] );

				add_action( 'admin_head', [ $this, 'admin_head' ] );
			} else {
				//add_action ('pre_amp_render_post', [$this, 'aplying_job_form']);
				add_filter( 'the_content', [ $this, 'applying_job_form' ] );
			}

			add_shortcode( 'sa_jobs_basic_list_design1', [ $this, 'jobs_shortcode1' ] );
			add_shortcode( 'sa_jobs_basic_list_design2', [ $this, 'jobs_shortcode2' ] );

			add_action( 'init', [ $this, 'load_plugin_textdomain' ] );

			## Cron actions.
			add_action( 'wp', [ $this, 'schedule_cron' ] );
			add_action( 'satech_cron', [ $this, 'cron_function' ] );

			## Include only SATech's classes. Ignore other classes.
			try {
				spl_autoload_register( function ( $class ) {
					if ( substr( $class, 0, 12 ) === 'SAJobsF\Jobs' ) {
						$path = self::JOBS_P_PATH . DIRECTORY_SEPARATOR . 'src' . substr( $class, 12 ) . '.php';
						$path = str_replace( "\\", DIRECTORY_SEPARATOR, $path );
						if ( is_file( $path ) ) {
							include_once( $path );
						}
					}
				} );
			} catch ( Exception $e ) {
				$this->dump( $e->getMessage(), true );
			}
		}

		public function plugin_api_call( $def, $action, $args ) {
			global $wp_version;

			if ( ! isset( $args->slug ) || ( $args->slug != $this->plugin_slug ) ) {
				return false;
			}

			// Get the current version
			$plugin_info     = get_site_transient( 'update_plugins' );
			$current_version = $plugin_info->checked[ $this->plugin_slug . '/' . $this->plugin_slug . '.php' ];
			$args->version   = $current_version;

			$request_string = [
				'body'       => [
					'action'  => $action,
					'request' => serialize( $args ),
					'api-key' => md5( get_bloginfo( 'url' ) ),
				],
				'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo( 'url' ),
			];

			$request = wp_remote_post( $this->api_url, $request_string );

			if ( is_wp_error( $request ) ) {
				$res = new WP_Error( 'plugins_api_failed', esc_attr__( 'An Unexpected HTTP Error occurred during the API request.</p> <p><a href="?" onclick="document.location.reload(); return false;">Try again</a>' ), $request->get_error_message() );
			} else {
				$res = unserialize( $request['body'] );

				if ( $res === false ) {
					$res = new WP_Error( 'plugins_api_failed', esc_attr__( 'An unknown error occurred' ), $request['body'] );
				}
			}

			return $res;
		}

		public function show_admin_notices() {
			if ( $this->get_option( 'job_form_page' ) == '' && get_current_screen()->base != 'sa-job-manager_page_SAjobsF_jobs_settings' ) {
				$class   = 'notice notice-error';
				$message = esc_attr__( 'Please select the page for job application form and description', self::DOMAIN );

				$url = "admin.php?page=SAjobsF_jobs_settings";
				$url = admin_url( $url );
				printf( '<div class="%1$s"><p>%2$s - <a href="%3$s">SA Job Manager Settings</a></p></div>', esc_attr( $class ), esc_html( $message ), $url );
			}
		}

		/**
		 * This method sets cron schedule, if not set.
		 */
		public function schedule_cron() {
			if ( ! wp_next_scheduled( 'satech_cron' ) ) {
				wp_schedule_event( time(), 'hourly', 'satech_cron' );
			}
		}

		/**
		 * Method called by cron method, (Every hour).
		 */
		public function cron_function() {
			( new ReceivedJobs() )->delete_auto_jobs();
		}

		public function applying_job_form( $content ) {
			if ( get_the_ID() == $this->get_option( 'job_form_page' ) ) {
				wp_enqueue_style( 'SAjobsF_jobs_fa_css', self::JOBS_F_URL . 'assets/font-awesome-4.7.0/css/font-awesome.min.css' );

				$form_design = $this->get_option( 'job_application_form' );

				wp_enqueue_style( 'jobsP_intl_css', self::JOBS_F_URL . 'assets/intl-tel/css/intlTelInput.min.css' );
				wp_enqueue_script( 'jobsP_intl_js', self::JOBS_F_URL . 'assets/intl-tel/js/intlTelInput.min.js', [ 'jquery' ] );

				wp_localize_script( 'SAjobsF_jobs_submit', 'im_ajax_object', [
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
				] );

				wp_enqueue_script( 'SAjobsF_jobs_submit', self::JOBS_F_URL . 'assets/js/job.js', [ 'jquery' ], $this->getVersion(), true );
				wp_enqueue_style( 'SAjobsF_jobs_shortcodes_css', self::JOBS_F_URL . 'assets/shortcodes.css', [], $this->getVersion() );

				global $wp_query;

				ob_start();
				if ( isset( $_GET['job_id'] ) ) {
					$job_id = $_GET['job_id'];
				} else if ( isset( $wp_query->query['page'] ) ) {
					$job_id = $wp_query->query['page'];
				} else {
					$job_id = 0;
				}
				$jobs     = new Jobs();
				$job_data = $jobs->get_job( $job_id, true );
				if ( ! isset( $job_data['id'] ) ) {
					echo "Job data not found...";

					return;
				}
				$jobs->view_update( $job_id );
				include( __DIR__ . '/front-end/bulma.php' );
				$content .= ob_get_clean();
			}

			return $content;
		}

		public function admin_head() {
			echo "<style>" . file_get_contents( self::JOBS_P_PATH . "/assets/admin_header.css" ) . "</style>";

			if ( $this->is_mobile() ) {
				echo "<style>" . file_get_contents( self::JOBS_P_PATH . "/assets/admin_header_mobile.css" ) . "</style>";
			}
		}

		public function load_plugin_textdomain() {
			load_plugin_textdomain( 'jobsp-domain', false, dirname( plugin_basename( JOBSP_F_FILE ) ) . '/langs/' );
		}

		/**
		 * Delete all tables created by this plugin.
		 * and re-create all tables.
		 */
		public function drop_tables() {
			foreach ( $this->Tables as $key => $table ) {
				$query = "DROP TABLE `" . $table . "`";

				$this->DB2->rawQuery( $query );
			}

			## Removing all settings from WordPress options table.
			\delete_option( self::OPTIONS_KEY );

			## Removing folders
			$array      = wp_upload_dir();
			$foler_path = $array['basedir'] . DIRECTORY_SEPARATOR . 'satech_basic_jobs' . DIRECTORY_SEPARATOR;
			$this->delete_folder( $foler_path );

			$this->install();
		}

		/**
		 * Get DB version saved in database
		 */
		public function get_db_version() {
			return (float) $this->get_option( 'db_version' );
		}

		public function ajax() {
			include_once( self::JOBS_P_PATH . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'ajax.php' );

			wp_die();
		}

		public function add_action_links( $links ) {
			$links[] = '<a style="color:#ffa94c;font-weight:bold;" href="' . esc_url( admin_url( 'admin.php?page=SAjobsF_jobs_settings' ) ) . '">' . esc_attr__( 'Settings', self::DOMAIN ) . '</a>';

			return $links;
		}

		public function install() {
			include_once __DIR__ . "/admin/system/db.php";
		}

		public function add_job_hidden( $submenu_file ) {
			global $plugin_page;

			$hidden_submenus = [
				'SAjobsF_jobs_add_page' => true,
			];

			// Select another submenu item to highlight (optional).
			if ( $plugin_page && isset( $hidden_submenus[ $plugin_page ] ) ) {
				$submenu_file = 'SAjobsF_jobs_management';
			}

			// Hide the submenu.
			foreach ( $hidden_submenus as $submenu => $unused ) {
				remove_submenu_page( 'SAjobsF_jobs_management', $submenu );
			}

			return $submenu_file;
		}

		/**
		 * Configure admin panel menus.
		 */
		public function admin_menu() {
			add_menu_page( esc_attr__( 'Job Ads', self::DOMAIN ), esc_attr__( self::JOBS_P_TITLE, self::DOMAIN ), self::JOBS_P_CAPABILITY, 'SAjobsF_jobs_management', [
				$this,
				'show_page',
			], self::JOBS_F_URL . 'assets/images/logo-20x20.png?3', '5.623' );

			try {
				$cnt = $this->DB2->where( 'deleted', 0 )->getValue( $this->Tables['job_ads'], 'count(*)' );
			} catch ( Exception $e ) {
				$cnt = 0;
			}
			$menu_title = esc_attr__( 'Job Ads', self::DOMAIN );
			if ( $cnt > 0 ) {
				if ( $cnt < 10 ) {
					$cnt = '0' . $cnt;
				}
				$menu_title .= " <span class='jobsp_badge'>{$cnt}</span>";
			}
			add_submenu_page( 'SAjobsF_jobs_management', esc_attr__( 'Job Ads', self::DOMAIN ), $menu_title, self::JOBS_P_CAPABILITY, 'SAjobsF_jobs_management', [
				$this,
				'show_page',
			] );
			add_submenu_page( 'SAjobsF_jobs_management', esc_attr__( 'Job Ads', self::DOMAIN ), '', self::JOBS_P_CAPABILITY, 'SAjobsF_jobs_add_page', [
				$this,
				'save_job_admin_page',
			] );

			try {
				$cnt = $this->DB2->getValue( $this->Tables['categories'], 'count(*)' );
			} catch ( Exception $e ) {
				$cnt = 0;
			}
			$menu_title = esc_attr__( 'Job Categories', self::DOMAIN );
			if ( $cnt > 0 ) {
				if ( $cnt < 10 ) {
					$cnt = '0' . $cnt;
				}
				$menu_title .= " <span class='jobsp_badge'>{$cnt}</span>";
			}
			add_submenu_page( 'SAjobsF_jobs_management', esc_attr__( 'Job Categories', self::DOMAIN ), $menu_title, self::JOBS_P_CAPABILITY, 'SAjobsF_jobs_cats', [
				$this,
				'cats_page',
			] );

			$menu_title = esc_attr__( 'Job Applications', self::DOMAIN );
			$cntUnread  = 0;
			try {
				$cntUnread = $this->DB2->where( 'read_by_admin', 0 )
				                       ->where( 'deleted', 0 )
				                       ->getValue( $this->Tables['job_applications'], 'count(*)' );
			} catch ( Exception $e ) {
				$cntUnread = 0;
			}

			try {
				$cnt = $this->DB2->where( 'deleted', 0 )->getValue( $this->Tables['job_applications'], 'count(*)' );
			} catch ( Exception $e ) {
				$cnt = 0;
			}

			if ( $cntUnread > 0 ) {
				if ( $cntUnread < 10 ) {
					$cntUnread = '0' . $cntUnread;
				}
				$menu_title .= " <span class='jobsp_badge_red'>{$cntUnread}</span>";
			} else if ( $cnt > 0 ) {
				if ( $cnt < 10 ) {
					$cnt = '0' . $cnt;
				}
				$menu_title .= " <span class='jobsp_badge'>{$cnt}</span>";
			}
			add_submenu_page( 'SAjobsF_jobs_management', esc_attr__( 'Job Applications', self::DOMAIN ), $menu_title, self::JOBS_P_CAPABILITY, 'SAjobsF_jobs_received', [
				$this,
				'received_jobs_page',
			] );

			add_submenu_page( 'SAjobsF_jobs_management', esc_attr__( 'Jobs Settings', self::DOMAIN ), '<b style="color:#ffa94c"> <i class="fa fa-cog"></i> ' . esc_attr__( 'Settings', self::DOMAIN ) . '</b>', self::JOBS_P_CAPABILITY, 'SAjobsF_jobs_settings', [
				$this,
				'settings_page',
			] );

			add_submenu_page( 'SAjobsF_jobs_management', esc_attr__( 'How to use Jobs Plugin', self::DOMAIN ), '<i class="fa fa-question"></i> ' . esc_attr__( 'User Manual', self::DOMAIN ), self::JOBS_P_CAPABILITY, 'SAjobsF_jobs_user_manual', [
				$this,
				'user_manual_page',
			] );
		}

		/**
		 * Adding user manual page in admin panel.
		 */
		public function user_manual_page() {
			if ( ! current_user_can( self::JOBS_P_CAPABILITY ) ) {
				return;
			}

			echo "<div class='jobsP_loader hide'><span><i class='fa fa-spin fa-spinner'></i> " . esc_attr__( "Please wait .....", self::DOMAIN ) . "</span></div>";

			include_once self::JOBS_P_PATH . '/admin/pages/user_manual.php';
		}

		public function save_template() {
			echo "<h1>Save template</h1>";
		}

		/**
		 * Add/Edit job ad page in admin panel
		 */
		public function save_job_admin_page() {
			if ( ! current_user_can( self::JOBS_P_CAPABILITY ) ) {
				return;
			}

			echo "<div class='jobsP_loader'><span><i class='fa fa-spin fa-spinner'></i> " . esc_attr__( "Please wait .....", self::DOMAIN ) . "</span></div>";

			include_once( self::JOBS_P_PATH . '/admin/pages/add_job.php' );
		}

		/**
		 * Job ads page in admin panel.
		 */
		public function show_page() {
			if ( ! current_user_can( self::JOBS_P_CAPABILITY ) ) {
				return;
			}

			echo "<div class='jobsP_loader hide'><span><i class='fa fa-spin fa-spinner'></i> " . esc_attr__( "Please wait .....", self::DOMAIN ) . "</span></div>";

			include_once( self::JOBS_P_PATH . '/admin/pages/jobs.php' );
		}

		public function received_jobs_page() {
			if ( ! current_user_can( self::JOBS_P_CAPABILITY ) ) {
				return;
			}

			echo "<div class='jobsP_loader hide'><span><i class='fa fa-spin fa-spinner'></i> " . esc_attr__( "Please wait .....", self::DOMAIN ) . "</span></div>";

			include_once self::JOBS_P_PATH . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'pages' . DIRECTORY_SEPARATOR . 'received.php';
		}

		public function cats_page() {
			if ( ! current_user_can( self::JOBS_P_CAPABILITY ) ) {
				return;
			}

			echo "<div class='jobsP_loader hide'><span><i class='fa fa-spin fa-spinner'></i> " . esc_attr__( "Please wait .....", self::DOMAIN ) . "</span></div>";

			include_once self::JOBS_P_PATH . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'pages' . DIRECTORY_SEPARATOR . 'cats.php';
		}

		public function settings_page() {
			if ( ! current_user_can( self::JOBS_P_CAPABILITY ) ) {
				return;
			}

			echo "<div class='jobsP_loader hide'><span><i class='fa fa-spin fa-spinner'></i> " . esc_attr__( "Please wait .....", self::DOMAIN ) . "</span></div>";

			include_once self::JOBS_P_PATH . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'pages' . DIRECTORY_SEPARATOR . 'settings.php';
		}

		public function load_scripts_and_styles( $hook ) {
			$page = $_GET['page'] ?? '';

			$JS_Keys = [
				'ajax_key' => self::AjaxKey,
			];

			switch ( $page ) {
				case 'SAjobsF_jobs_cats':
				case 'SAjobsF_jobs_management':
				case 'SAjobsF_jobs_settings':
				case 'SAjobsF_jobs_received':
				case 'SAjobsF_jobs_user_manual':
				case 'SAjobsF_jobs_add_page':
					wp_enqueue_script( 'SAjobsF_jobs_functions', self::JOBS_F_URL . 'assets/js/functions.js', [], $this->getVersion(), true );
					wp_enqueue_script( 'SAjobsF_jobs_jquery_url', 'https://cdnjs.cloudflare.com/ajax/libs/urljs/2.3.1/url.min.js', [ 'jquery' ], false, true );
					wp_enqueue_style( 'SAjobsF_jobs_fa_css', self::JOBS_F_URL . 'assets/font-awesome-4.7.0/css/font-awesome.min.css' );
					wp_enqueue_style( 'SAjobsF_jobs_jquery_confirm_css', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.4/jquery-confirm.min.css' );
					wp_enqueue_style( 'SAjobsF_jobs_jquery_fancybox_css', 'https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.css' );

					wp_enqueue_script( 'SAjobsF_jobs_jquery_confirm_js', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.4/jquery-confirm.min.js', [ 'jquery' ], false, true );
					wp_enqueue_script( 'SAjobsF_jobs_tmpl', 'https://cdnjs.cloudflare.com/ajax/libs/blueimp-JavaScript-Templates/3.13.0/js/tmpl.min.js' );
					wp_enqueue_script( 'SAjobsF_jobs_jquery_modal_js', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.js', [ 'jquery' ], false, true );
					wp_enqueue_script( 'SAjobsF_jobs_jquery_fancybox_js', 'https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js', [ 'jquery' ], false, true );


					if ( $page == 'SAjobsF_jobs_cats' ) {
						wp_enqueue_script( 'SAjobsF_jobs_custom_js', self::JOBS_F_URL . 'assets/js/admin_cats.js', [ 'jquery' ], $this->getVersion(), true );
						wp_localize_script( 'SAjobsF_jobs_custom_js', 'jobsP', $JS_Keys );
						wp_enqueue_style( 'jobsP_css_framework', 'https://cdnjs.cloudflare.com/ajax/libs/bulma/0.8.0/css/bulma.min.css' );
					} elseif ( $page == 'SAjobsF_jobs_management' ) {
						wp_enqueue_style( 'jobsP_css_framework', 'https://cdnjs.cloudflare.com/ajax/libs/bulma/0.8.0/css/bulma.min.css' );
						wp_enqueue_script( 'SAjobsF_jobs_custom_js', self::JOBS_F_URL . 'assets/js/admin_jobs.js', [ 'jquery' ], $this->getVersion(), true );
						wp_localize_script( 'SAjobsF_jobs_custom_js', 'jobsP', $JS_Keys );
						wp_enqueue_style( 'SAjobsF_jobs_jobs_css', self::JOBS_F_URL . 'assets/jobs.css', [], $this->getVersion() );
					} elseif ( $page == 'SAjobsF_jobs_received' ) {
						wp_enqueue_style( 'jobsP_css_framework', 'https://cdnjs.cloudflare.com/ajax/libs/bulma/0.8.0/css/bulma.min.css' );
						wp_enqueue_script( 'jquery-ui-tabs' );
						wp_enqueue_style( 'SAjobsF_jobs_received_css', self::JOBS_F_URL . 'assets/received.css', [], $this->getVersion() );
						wp_enqueue_script( 'SAjobsF_jobs_custom_js', self::JOBS_F_URL . 'assets/js/admin_received.js', [ 'jquery' ], $this->getVersion(), true );
						wp_localize_script( 'SAjobsF_jobs_custom_js', 'jobsP', $JS_Keys );
					} elseif ( $page == 'SAjobsF_jobs_settings' ) {
						wp_enqueue_style( 'jobsP_css_framework', 'https://cdnjs.cloudflare.com/ajax/libs/bulma/0.8.0/css/bulma.min.css' );
						wp_enqueue_script( 'SAjobsF_jobs_settings', self::JOBS_F_URL . 'assets/js/admin_settings.js', [ 'jquery' ], $this->getVersion(), true );
						wp_enqueue_script( 'SAjobsF_jobs_clipboard', 'https://cdnjs.cloudflare.com/ajax/libs/clipboard-polyfill/2.8.6/clipboard-polyfill.js', [ 'jquery' ], null, true );
						wp_localize_script( 'SAjobsF_jobs_settings', 'jobsP', $JS_Keys );
						wp_enqueue_style( 'SAjobsF_jobs_settings_css', self::JOBS_F_URL . 'assets/settings.css', [], $this->getVersion() );
					} else if ( $page == 'SAjobsF_jobs_user_manual' ) {
						wp_enqueue_style( 'SAjobsF_jobs_um_css', self::JOBS_F_URL . 'assets/user_manual.css', [], $this->getVersion() );
						wp_enqueue_script( 'SAjobsF_jobs_um_js', self::JOBS_F_URL . 'assets/js/admin_um.js', [ 'jquery' ], $this->getVersion(), true );
					} else if ( $page == 'SAjobsF_jobs_add_page' ) {
						wp_enqueue_script( 'SAjobsF_jobs_ckeditor', 'https://cdn.ckeditor.com/4.14.0/standard-all/ckeditor.js' );
						wp_enqueue_script( 'SAjobsF_jobs_addjob_js', self::JOBS_F_URL . 'assets/js/admin_add_job.js', [ 'jquery' ], $this->getVersion(), true );
					}

					wp_enqueue_style( 'SAjobsF_jobs_custom_css', self::JOBS_F_URL . 'assets/admin_css.css', [], $this->getVersion() );
					break;
			}
		}

		/**
		 * Enqueue front end and editor JavaScript and CSS
		 */
		public function hello_gutenberg_scripts() {
			$blockPath = '/dist/block.js';
			$stylePath = '/dist/block.css';

			// Enqueue the bundled block JS file
			wp_enqueue_script( 'hello-gutenberg-block-js', plugins_url( $blockPath, JOBSP_F_FILE ), [
				'wp-i18n',
				'wp-blocks',
				'wp-editor',
				'wp-components',
			], $this->getVersion() );

			// Enqueue frontend and editor block styles
			wp_enqueue_style( 'hello-gutenberg-block-css', plugins_url( $stylePath, JOBSP_F_FILE ), '', $this->getVersion() );

		}

		public function show_error_in_add_job() {
			$Jobs    = new SAJobsF\Jobs\Jobs();
			$class   = 'notice notice-error';
			$message = $Jobs->get_error_message( $_REQUEST['error'] );

			printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
		}

		public function set_option( $key, $value ) {
			$allOptions = \get_option( self::OPTIONS_KEY );

			if ( $allOptions === false ) {
				$allOptions = [];
			}

			$allOptions[ $key ] = $value;

			return update_option( self::OPTIONS_KEY, $allOptions );
		}

		public function get_option( $key ) {
			$allOptions = \get_option( self::OPTIONS_KEY );

			return $allOptions[ $key ] ?? null;
		}

		public function delete_option( $key ) {
			$allOptions = \get_option( self::OPTIONS_KEY );

			if ( isset( $allOptions[ $key ] ) ) {
				unset( $allOptions[ $key ] );
			}

			return \update_option( self::OPTIONS_KEY, $allOptions );
		}

		public function dump( $data, $inFile = false ) {
			if ( is_bool( $data ) && $data === true ) {
				$string = 'TRUE';
			} else if ( is_bool( $data ) && $data === false ) {
				$string = 'FALSE';
			} else if ( is_null( $data ) ) {
				$string = 'NULL';
			} else {
				$string = print_r( $data, true );
			}
			if ( $inFile || ( ! empty( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ) == 'xmlhttprequest' ) ) {
				$string .= PHP_EOL;
				$string .= wp_debug_backtrace_summary() . PHP_EOL;
				@file_put_contents( self::JOBS_P_PATH . DIRECTORY_SEPARATOR . 'dump.txt', $string, FILE_APPEND );

			} else {
				echo '<pre>';
				echo $string;
				echo '</pre>';
			}
		}

		/**
		 * Static version of dump.
		 *
		 * @param      $data
		 * @param bool $inFile
		 */
		public static function dump_static( $data, $inFile = false ) {
			( new self )->dump( $data, $inFile );
		}

		public function json( $content ) {
			header( 'Content-Type: application/json' );
			if ( ! ( is_array( $content ) || is_object( $content ) ) ) {
				echo json_encode( [ 'error' => $content ] );
			} else {
				echo json_encode( $content );
			}
			exit();
		}

		public function date( $date ) {
			return \date( \get_option( 'date_format' ), strtotime( $date ) );
		}

		public function delete_folder( $dirPath ) {
			if ( ! is_dir( $dirPath ) ) {
				return false;
			}
			if ( substr( $dirPath, strlen( $dirPath ) - 1, 1 ) != '/' ) {
				$dirPath .= '/';
			}
			$files = glob( $dirPath . '*', GLOB_MARK );
			foreach ( $files as $file ) {
				if ( is_dir( $file ) ) {
					$this->delete_folder( $file );
				} else {
					unlink( $file );
				}
			}

			return rmdir( $dirPath );
		}

		/**
		 * @return string
		 *
		 * This method will return format from WordPress settings for MySQL query.
		 *               usage: SELECT DATE_FORMAT(`date_column`, $result_of_this_function) FROM `table_name`
		 */
		public function get_mysql_date_format() {
			$wp_date_format = \get_option( 'date_format' );
			$format_keys    = [
				'F' => '%M',
				'j' => '%e',
				'Y' => '%Y',
				'y' => '%y',
				'm' => '%m',
				'd' => '%d',
			];

			return str_replace( array_keys( $format_keys ), array_values( $format_keys ), $wp_date_format );
		}

		public function get_mysql_datetime_format() {
			$wp_date_format = \get_option( 'date_format' ) . ' ' . \get_option( 'time_format' );
			$format_keys    = [
				'F' => '%M',
				'j' => '%e',
				'Y' => '%Y',
				'y' => '%y',
				'm' => '%m',
				'd' => '%d',
				'D' => '%a',
				'H' => '%H',
				'g' => '%h',
				'i' => '%i',
				's' => '%s',
				'a' => '%p',
				'A' => '%p',
			];

			return str_replace( array_keys( $format_keys ), array_values( $format_keys ), $wp_date_format );
		}

		public function get_file_url( $filePath ) {
			if ( ! is_file( $filePath ) ) {
				return '#';
			}

			$hash         = wp_check_filetype( $filePath );
			$ext          = $hash['ext'];
			$hash['path'] = $filePath;
			$hash         = urlencode( base64_encode( json_encode( $hash ) ) );

			return self::JOBS_F_URL . 'serve_file.php?file=' . $hash . '.' . $ext;
		}

		public function jobs_shortcode1( $atts ) {
			wp_enqueue_style( 'SAjobsF_jobs_fa_css', self::JOBS_F_URL . 'assets/font-awesome-4.7.0/css/font-awesome.min.css' );
			wp_enqueue_script( 'SAjobsF_markjs', 'https://cdnjs.cloudflare.com/ajax/libs/mark.js/8.11.1/jquery.mark.min.js', [ 'jquery' ] );
			wp_enqueue_style( 'SAjobsF_jobs_shortcodes_css', self::JOBS_F_URL . 'assets/shortcodes.css', [], $this->getVersion() );
			wp_enqueue_script( 'SAjobsF_jobs_shortcodes_js', self::JOBS_F_URL . 'assets/js/shortcodes.js', [ 'jquery' ], $this->getVersion() );

			ob_start();

			$template = 'classic';
			include( self::JOBS_P_PATH . '/shortcodes/jobs.php' );

			return ob_get_clean();
		}

		public function jobs_shortcode2( $atts ) {
			wp_enqueue_style( 'SAjobsF_jobs_fa_css', self::JOBS_F_URL . 'assets/font-awesome-4.7.0/css/font-awesome.min.css' );
			wp_enqueue_script( 'SAjobsF_markjs', 'https://cdnjs.cloudflare.com/ajax/libs/mark.js/8.11.1/jquery.mark.min.js', [ 'jquery' ] );
			wp_enqueue_style( 'SAjobsF_jobs_shortcodes_css', self::JOBS_F_URL . 'assets/shortcodes.css', [], $this->getVersion() );
			wp_enqueue_script( 'SAjobsF_jobs_shortcodes_js', self::JOBS_F_URL . 'assets/js/shortcodes.js', [ 'jquery' ], $this->getVersion() );

			ob_start();

			$template = 'singleline';
			include( self::JOBS_P_PATH . '/shortcodes/jobs.php' );

			return ob_get_clean();
		}

		/**
		 * @param $date
		 *
		 * @return string
		 * @deprecated
		 */
		public function timeago( $date ) {
			$timestamp = strtotime( $date );

			$strTime = [ "second", "minute", "hour", "day", "month", "year" ];
			$length  = [ "60", "60", "24", "30", "12", "10" ];

			$currentTime = time();

			if ( $currentTime >= $timestamp ) {
				$diff = $currentTime - $timestamp;
				for ( $i = 0; $diff >= $length[ $i ] && $i < count( $length ) - 1; $i++ ) {
					$diff = $diff / $length[ $i ];
				}

				$diff = round( $diff );

				if ( $diff < 2 ) {
					return $diff . " " . $strTime[ $i ] . " ago";
				} else {
					return $diff . " " . $strTime[ $i ] . "s ago";
				}
			}

			return $date;
		}

		/**
		 * Create headers for email.
		 *
		 * @return array
		 */
		public function email_headers( $args = [] ) {
			$default = [
				'cc'  => '',
				'bcc' => '',
			];
			$args    = wp_parse_args( $args, $default );

			$headers[] = 'Content-Type: text/html; charset=iso-8859-1';

			$from_name = $this->get_option( 'from_name' );
			$from_name = empty( $from_name ) ? \get_option( 'blogname' ) : $from_name;

			$from_email = $this->get_option( 'from_email' );
			$from_email = is_email( $from_email ) ? $from_email : \get_option( 'admin_email' );

			$headers[] = 'From: "' . $from_name . '" <' . $from_email . '>';
			$headers[] = 'MIME-Version: 1.0';

			if ( is_email( trim( $args['cc'] ) ) ) {
				$headers[] = 'Cc: ' . $args['cc'];
			}
			if ( is_email( trim( $args['bcc'] ) ) ) {
				$headers[] = 'Bcc: ' . $args['bcc'];
			}

			return $headers;
		}

		/**
		 * Send emails u sing this function.
		 *
		 * @param array $args
		 *
		 * @return string
		 * @throws Exception
		 */
		public function send_mail( $args = [] ) {
			$default = [
				'email'          => '',
				'cc'             => '',
				'bcc'            => '',
				'subject'        => '',
				'content'        => '',
				'application_id' => 0,
				'attached_files' => [],
			];
			$args    = wp_parse_args( $args, $default );

			$args['email'] = trim( (string) $args['email'] );
			if ( ! \is_email( $args['email'] ) ) {
				throw new  Exception( esc_attr__( 'Invalid email address provided.', self::DOMAIN ) );
			}

			$args['subject'] = trim( $args['subject'] );
			if ( $args['subject'] == '' ) {
				throw new Exception( esc_attr__( 'Email subject missing.', self::DOMAIN ) );
			}

			$args['content'] = trim( $args['content'] );
			if ( $args['content'] == '' ) {
				throw new Exception( esc_attr__( 'Email content missing.', self::DOMAIN ) );
			}

			if ( ! empty( $args['attached_files'] ) && ! is_array( $args['attached_files'] ) ) {
				$args['attached_files'] = explode( ',', $args['attached_files'] );
			}

			$args['subject'] = \wp_unslash( $args['subject'] );
			$args['content'] = \wp_unslash( $args['content'] );

			if ( ! empty( $args['application_id'] ) ) {
				$row = $this->DB2->where( 'id', $args['application_id'] )->getOne( $this->Tables['job_applications'] );

				$Fields          = [
					'{company_name}'      => $this->get_option( 'company_name' ),
					'{application_id}'    => $args['application_id'],
					'{ad_id}'             => $row['ad_id'],
					'{job_ad_id}'         => $row['ad_id'],
					'{applicant_name}'    => $row['applicant_name'],
					'{applicant_email}'   => $row['applicant_email'],
					'{applicant_contact}' => $row['applicant_contact'],
					'{applicant_message}' => $row['applicant_message'],
					'{job_category_name}' => $row['job_category_name'],
					'{job_category}'      => $row['job_category_name'],
					'{job_title}'         => $row['job_title'],
					'{title}'             => $row['job_title'],
				];
				$args['content'] = strtr( $args['content'], $Fields );
			}

			$response = \wp_mail( $args['email'], $args['subject'], $args['content'], $this->email_headers( [
				'cc'  => $args['cc'],
				'bcc' => $args['bcc'],
			] ), $args['attached_files'] );
			if ( $response ) {
				Logs::save_log( 'Email sent to: ' . $args['email'] . '<br>Subject: ' . $args['subject'] . '<SATECH></SATECH><hr>' . $args['content'], $args['application_id'] );

				return 'OK';
			} else {
				return esc_attr__( $GLOBALS['phpmailer']->ErrorInfo, self::DOMAIN );
			}
		}

		public function getVersion() {
			if ( ! function_exists( 'get_plugin_data' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			}
			$plugin_data = get_plugin_data( JOBSP_F_FILE );

			return $plugin_version = $plugin_data['Version'];
		}

		public function getFileExtension( $filename ) {
			$filename = strtolower( $filename );

			return pathinfo( $filename, PATHINFO_EXTENSION );
		}

		public function is_mysql_column( $table_name, $column_name ) {
			$query = "SHOW COLUMNS FROM `{$table_name}` LIKE '{$column_name}'";

			return $this->DB2->rawQuery( $query ) ? true : false;
		}

		public function drop_mysql_column( $table_name, $column_name ) {
			$query = "ALTER TABLE `{$table_name}` DROP COLUMN `$column_name`";

			try {
				return $this->DB2->rawQuery( $query );
			} catch ( Exception $e ) {
				// Do nothing
			}
		}

		/**
		 * @return bool
		 *
		 * Check if this is developer's machine.
		 */
		public function is_developer_pc() {
			if ( defined( 'SA_TECH_LOCALHOST' ) && ( SA_TECH_LOCALHOST === 1 || SA_TECH_LOCALHOST === true ) ) {
				return true;
			}

			$_SERVER['HTTP_HOST']   = $_SERVER['HTTP_HOST'] ?? '';
			$_SERVER['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'] ?? '';

			return ( $_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['HTTP_HOST'] == '127.0.0.1' || $_SERVER['REMOTE_ADDR'] == '127.0.0.1' || $_SERVER['REMOTE_ADDR'] == 'localhost' || $_SERVER['REMOTE_ADDR'] == '::1' );
		}

		/**
		 * Check if this is our testing server.
		 *
		 * @return bool
		 */
		public function is_test_server() {
			if ( $this->is_developer_pc() ) {
				return true;
			}
			$_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST'] ?? '';

			$domain = strtolower( $_SERVER['HTTP_HOST'] );

			return in_array( $domain, [
				'jobs.satechitcompany.com',
				'satechitcompany.com',
				'www.satechitcompany.com',
			] );
		}

		public function get_ip_address() {
			foreach ( [
				          'HTTP_CLIENT_IP',
				          'HTTP_X_FORWARDED_FOR',
				          'HTTP_X_FORWARDED',
				          'HTTP_X_CLUSTER_CLIENT_IP',
				          'HTTP_FORWARDED_FOR',
				          'HTTP_FORWARDED',
				          'REMOTE_ADDR',
			          ] as $key ) {
				if ( array_key_exists( $key, $_SERVER ) === true ) {
					foreach ( array_map( 'trim', explode( ',', $_SERVER[ $key ] ) ) as $ip ) {
						if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) !== false ) {
							return $ip;
						}
					}
				}
			}

			if ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
				return $_SERVER['REMOTE_ADDR'];
			}

			return '';
		}

		public function pagination_stats( $page, $pages, $pageSize, $total_records ) {
			$total_records = (int) $total_records;
			$range         = 3;

			if ( $page <= $range ) {
				$start = 1;
			} else {
				$start = $page - $range;
			}
			$end = $start + $range + $range;
			if ( $pages < $end ) {
				$end = $pages;
			}
			$from = ( ( $page * $pageSize ) - $pageSize ) + 1;
			if ( $total_records == 0 ) {
				$from = 0;
			}
			$to = $pageSize * $page;
			if ( $to > $total_records ) {
				$to = $total_records;
			}

			/**
			 * This stats will helpful to show pagination HTML, Start (From page number), End (Till page number e.g. Start=2 to End=5)
			 *      From (From record number), To (To record number)
			 */
			return [
				'start' => $start,      //
				'end'   => $end,
				'from'  => $from,
				'to'    => $to,
			];
		}

		/**
		 * This method will return MySQL table name of users.
		 *
		 * @return string
		 */
		public function get_users_table_name() {
			global $wpdb;

			return $wpdb->prefix . 'users';
		}

		/**
		 * Save email logs in database.
		 *
		 * @param $args
		 *
		 * @return string
		 */
		public function save_email_log( $args ) {
			$default = [
				'application_id' => 0,
				'sent_to'        => '',
				'subject'        => '',
				'content'        => '',
			];
			$args    = \wp_parse_args( $args, $default );

			$args['application_id'] = (int) $args['application_id'];
			$data['application_id'] = $args['application_id'];

			if ( empty( $args['sent_to'] ) ) {
				return 'Sent emails missing.';
			}
			if ( is_array( $args['sent_to'] ) || is_object( $args['sent_to'] ) ) {
				$args['sent_to'] = implode( ',', $args['sent_to'] );
			}
			$data['sent_to'] = $args['sent_to'];
			$data['subject'] = $args['subject'];

			if ( empty( $args['content'] ) ) {
				return 'Email content missing.';
			}
			$data['content'] = $args['content'];

			$data['ip']       = $this->get_ip_address();
			$data['sent_by']  = get_current_user_id();
			$data['datetime'] = current_time( 'mysql' );

			try {
				if ( $this->DB2->insert( $this->Tables['email_logs'], $data ) ) {
					return 'OK';
				} else {
					return $this->DB2->getLastError();
				}
			} catch ( \Exception $e ) {
				return $e->getMessage();
			}
		}

		public function get_file_accept() {
			return implode( ',', $this->file_input_accept );
		}

		public function is_image_file( $filename ) {
			$filename = basename( $filename );
			$ext      = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );
			switch ( $ext ) {
				case 'jpg':
				case 'jpeg':
				case 'gif':
				case 'png':
				case 'bmp':
				case 'tiff':
				case 'jiff':
					return true;
					break;
				default:
					return false;
			}
		}

		/**
		 * Automatically applies "p" and "br" markup to text.
		 * Basically [nl2br](http://php.net/nl2br) on steroids.
		 *
		 *     echo Text::auto_p($text);
		 *
		 * [!!] This method is not foolproof since it uses regex to parse HTML.
		 *
		 * @param string  $str subject
		 * @param boolean $br  convert single linebreaks to <br />
		 *
		 * @return  string
		 */
		public static function auto_p( $str, $br = true ) {
			# Trim whitespace
			if ( ( $str = trim( $str ) ) === '' ) {
				return '';
			}

			# Standardize newlines
			$str = str_replace( [ "\r\n", "\r" ], "\n", $str );

			// Trim whitespace on each line
			$str = preg_replace( '~^[ \t]+~m', '', $str );
			$str = preg_replace( '~[ \t]+$~m', '', $str );

			# The following regexes only need to be executed if the string contains html
			if ( $html_found = ( strpos( $str, '<' ) !== false ) ) {
				# Elements that should not be surrounded by p tags
				$no_p = '(?:p|div|h[1-6r]|ul|ol|li|blockquote|d[dlt]|pre|t[dhr]|t(?:able|body|foot|head)|c(?:aption|olgroup)|form|s(?:elect|tyle)|a(?:ddress|rea)|ma(?:p|th))';

				# Put at least two linebreaks before and after $no_p elements
				$str = preg_replace( '~^<' . $no_p . '[^>]*+>~im', "\n$0", $str );
				$str = preg_replace( '~</' . $no_p . '\s*+>$~im', "$0\n", $str );
			}

			# Do the <p> magic!
			$str = '<p>' . trim( $str ) . '</p>';
			$str = preg_replace( '~\n{2,}~', "</p>\n\n<p>", $str );

			# The following regexes only need to be executed if the string contains html
			if ( $html_found !== false ) {
				// Remove p tags around $no_p elements
				$str = preg_replace( '~<p>(?=</?' . $no_p . '[^>]*+>)~i', '', $str );
				$str = preg_replace( '~(</?' . $no_p . '[^>]*+>)</p>~i', '$1', $str );
			}

			# Convert single linebreaks to <br />
			if ( $br === true ) {
				$str = preg_replace( '~(?<!\n)\n(?!\n)~', "<br />\n", $str );
			}

			return $str;
		}

		public function nl2br( $html ) {
			return $this::nl2br_static( $html );
		}

		public static function nl2br_static( $html ) {
			return wp_unslash( $html );
		}

		/**
		 * Check if visitor is on mobile device or not.
		 *
		 * @return bool
		 */
		public function is_mobile() {
			$useragent = $_SERVER['HTTP_USER_AGENT'] ?? '';

			return preg_match( '/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', $useragent ) || preg_match( '/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', substr( $useragent, 0, 4 ) );
		}

		public function sanitize( $array_or_string ) {
			if ( is_string( $array_or_string ) ) {
				$array_or_string = sanitize_textarea_field( $array_or_string );
			} elseif ( is_array( $array_or_string ) ) {
				foreach ( $array_or_string as $key => &$value ) {
					if ( is_array( $value ) ) {
						$value = $this->sanitize( $value );
					} else {
						$value = sanitize_textarea_field( $value );
					}
				}
			}

			return $array_or_string;
		}
	}
}
$SAJobsF = new FreeSATechJobsManager();
