<?php
/*
Plugin Name: Weather Plugin
Plugin URI: https://54.168.195.181
Description: Display the current temperature on your website.
Version: 1.0
Author: Abhijith Santhosh
Author URI: https://54.168.195.181
*/

// Enqueue scripts and styles
function weather_plugin_enqueue_scripts() {
  wp_enqueue_style( 'weather-plugin-style', plugin_dir_url( __FILE__ ) . 'style.css' );
  wp_enqueue_script( 'weather-plugin-script', plugin_dir_url( __FILE__ ) . 'script.js', array( 'jquery' ), '1.0', true );
}
add_action( 'wp_enqueue_scripts', 'weather_plugin_enqueue_scripts' );

// Shortcode callback function
function weather_plugin_display_temperature( $atts ) {
  // Get the saved location from the settings page
  $location = get_option( 'weather_plugin_location', 'Thrisure' );

  // Extract shortcode attributes
  $atts = shortcode_atts( array(
    'api_key' => 'be3b3ade64864cafb3954526230606',
  ), $atts );

  $api_key = $atts['api_key'];

  // Make API request to WeatherAPI
  $api_url = "https://api.weatherapi.com/v1/current.json?key={$api_key}&q={$location}";
  $response = wp_remote_get( $api_url );

  if ( is_wp_error( $response ) ) {
    return 'Error retrieving weather data.';
  }

  $body = wp_remote_retrieve_body( $response );
  $data = json_decode( $body, true );

  if ( ! isset( $data['current']['temp_c'] ) ) {
    return 'Temperature data not available.';
  }

  $temperature = $data['current']['temp_c'];

  // Output the temperature
  ob_start();
  ?>
  <div class="weather-plugin-temperature">
    Your Current Temperature in  <?php echo $location; ?>: <?php echo $temperature; ?>°C
  </div>
  <?php
  return ob_get_clean();
}
add_shortcode( 'weather', 'weather_plugin_display_temperature' );

// Add custom settings page
function weather_plugin_settings_page() {
  add_options_page(
    'Weather Plugin Settings',
    'Weather Plugin',
    'manage_options',
    'weather-plugin-settings',
    'weather_plugin_render_settings_page'
  );
}
add_action( 'admin_menu', 'weather_plugin_settings_page' );

// Render the settings page
function weather_plugin_render_settings_page() {
  // Check if user has permission to access the settings page
  if ( ! current_user_can( 'manage_options' ) ) {
    return;
  }

  // Save the settings
  if ( isset( $_POST['weather_plugin_save_settings'] ) ) {
    $location = sanitize_text_field( $_POST['weather_plugin_location'] );
    update_option( 'weather_plugin_location', $location );
    echo '<div class="notice notice-success"><p>Settings saved successfully.</p></div>';
  }

  // Retrieve the saved location
  $location = get_option( 'weather_plugin_location', 'kochi' );
  ?>
  <div class="wrap">
    <h1>Weather Plugin Settings</h1>

    <form method="post" action="">
      <table class="form-table">
        <tr>
          <th scope="row"><label for="weather_plugin_location">Location</label></th>
          <td>
            <input type="text" name="weather_plugin_location" id="weather_plugin_location" value="<?php echo esc_attr( $location ); ?>" class="regular-text">
            <p class="description">Enter the location for displaying the temperature. (e.g., Kochi)</p>
          </td>
        </tr>
      </table>

      <p class="submit">
        <input type="submit" name="weather_plugin_save_settings" id="weather_plugin_save_settings" class="button button-primary" value="Save Changes">
      </p>
    </form>
  </div>
  <?php
}
