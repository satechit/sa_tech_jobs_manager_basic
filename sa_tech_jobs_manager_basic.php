<?php
/**
 * Plugin Name: SA Tech Jobs Manager (Basic)
 * Description: <strong>SA Tech Jobs Manager</strong> is a powerful but easy to use WordPress recruitment plugin. It is equally useful for both companies and individual recruiters.
 * Plugin URI: https://www.satechitcompany.com/free-sa-tech-jobs-manager-for-wordpress/
 * Version: 1.0.5
 * Author: SA Tech IT Company
 * Author URI: https://www.satechitcompany.com
 */

if ( ! defined( 'ABSPATH' ) ) {
	echo 'I am WordPress plugin file. Please do not run me directly.';
	exit();
}

define( 'JOBS_F_URL', plugin_dir_url( __FILE__ ) );
define( 'JOBSP_F_FILE', __FILE__ );

include_once( __DIR__ . '/__plugin_init.php' );