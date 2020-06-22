<?php
/**
 * Trigger the Webhook each time a Post or Page is updated
 */
function wp_headless_trigger_trigger_webhook_on_save_post( $post_id ) {
  update_option( 'wp_headless_trigger_last_trigger', time() );
  $options = get_option( 'wp_headless_trigger_settings' );
  $crontime = $options["wp_headless_trigger_crontime"];
  
  if (
      ! array_key_exists( 'wp_headless_trigger_webhook_url', $options ) ||
      empty( $options['wp_headless_trigger_webhook_url'] )
  ) {
      return false;
  }
  
  if(empty($crontime) || (int) $crontime === 0) {
    $response = wp_headless_trigger_call_webhook(true);
    if ( is_wp_error( $response ) ) {
      $error_message = $response->get_error_message();
      error_log( "WP Headless Trigger: " . $error_message, 0 );
    }
  }

}
add_action( 'save_post', 'wp_headless_trigger_trigger_webhook_on_save_post' );