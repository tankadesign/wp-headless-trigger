<?php
/**
 * Plugin Name: WP Headless Trigger
 * Plugin URI: https://github.com/tankadesign/wp-headless-trigger
 * Description: A plugin which helps you using WordPress as a Headless CMS, takes a webhook url and triggers a build on your front end.
 * Version: 1.0.0
 * Author: Quema Labs (forked by Tanka Design)
 * Author URI: https://tankadesign.com
 * Text Domain: wp-headless-trigger
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /languages
 */

require plugin_dir_path( __FILE__ ) . 'inc/options-page.php';
require plugin_dir_path( __FILE__ ) . 'inc/trigger-webhook.php';

/**
 * Create Admin Panel
 */
function wp_headless_trigger_menu() {
  add_submenu_page( 'tools.php', esc_html__( 'WP Headless Trigger', 'wp-headless-trigger' ), esc_html__( 'Headless Trigger', 'wp-headless-trigger' ), 'manage_options', 'wp-headless-trigger', 'wp_headless_trigger_options_page' );
}
add_action( 'admin_menu', 'wp_headless_trigger_menu' );

/**
 * Create Settings
 */
function wp_headless_trigger_get_config () {
  return [
  	'interval' => 'wp-headless-trigger-immediately',
  	'name' => 'wp_headless_trigger_cron'
  ];  
}
 

function wp_headless_trigger_settings_init() {

  register_setting( 'wp_headless_trigger', 'wp_headless_trigger_settings' );
  add_settings_section(
      'wp_headless_trigger_section',
      esc_html__( 'Settings', 'wp-headless-trigger' ),
      '',
      'wp_headless_trigger'
  );
  add_settings_field(
      'wp_headless_trigger_webhook_url',
      esc_html__( 'Webhook URL', 'wp-headless-trigger' ),
      'wp_headless_trigger_webhook_url_render',
      'wp_headless_trigger',
      'wp_headless_trigger_section'
  );
  add_settings_field(
      'wp_headless_trigger_crontime',
      esc_html__( 'CRON time', 'wp-headless-trigger' ),
      'wp_headless_trigger_crontime_render',
      'wp_headless_trigger',
      'wp_headless_trigger_section'
  );

}

function wp_headless_trigger_add_cron_interval () {
  $config = wp_headless_trigger_get_config();
	$interval = $config['interval'];
	$schedules[$interval] = [
		'interval'  => 1,
		'display'   => "Immediately"
	];
	return $schedules;
}

function wp_headless_trigger_activate () {

  $config = wp_headless_trigger_get_config();
  $cron_name = $config['name'];
	$interval = $config['interval'];

  $args = [false];
	$scheduledTimestamp = wp_next_scheduled($cron_name, $args);
	if(!$scheduledTimestamp) {
		wp_schedule_event(time(), $interval, $cron_name, $args);
	}

}

function wp_headless_trigger_deactivate () {

  $config = wp_headless_trigger_get_config();
  $cron_name = $config['name'];
	
  $args = [false];
	$scheduledTimestamp = wp_next_scheduled($cron_name, $args);
	if($scheduledTimestamp) {
		wp_unschedule_event($scheduledTimestamp, $cron_name);
	}

}

function wp_headless_trigger_call_webhook ($force = false) {

  $options = get_option( 'wp_headless_trigger_settings' );
  $crontime = $options["wp_headless_trigger_crontime"];
  $webhook = $options['wp_headless_trigger_webhook_url'];

  if(empty($webhook)) return;
  
  $last_trigger_time = (int) get_option( 'wp_headless_trigger_last_trigger', -1);
  $now = time();
  $crontime = empty($crontime) ? 2 : (int) $crontime; /// time in minutes
  
  if($last_trigger_time === -1) $should_fire_trigger = false;
  else $should_fire_trigger = ($now - $last_trigger_time) > $crontime * 60;
  
  update_option( 'wp_headless_trigger_last_trigger', -1 );
  
  if($should_fire_trigger || $force) {
    $webhook_url = esc_url( $webhook );
    $response = wp_remote_post( $webhook_url );
    return $response;
  } else {
    return null;
  }

}

function wp_headless_trigger_init() {
  add_filter( 'cron_schedules', 'wp_headless_trigger_add_cron_interval' );
  register_activation_hook (__FILE__, 'wp_headless_trigger_activate');
  register_deactivation_hook(__FILE__, 'wp_headless_trigger_deactivate');
  
  add_action(wp_headless_trigger_get_config()['name'], 'wp_headless_trigger_call_webhook', 10, 1);
  
  add_action( 'admin_init', 'wp_headless_trigger_settings_init' );
}

wp_headless_trigger_init();