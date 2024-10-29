<?php

/**
 * Plugin Name:       Alice Chatbot
 * Plugin URI:        https://app.getalice.ai/
 * Description:       Alice is a Multi-Channel customer service platform for your e-commerce store or online business that centralises all customer interactions and helps to manage and automate customer support.
 * Version:           1.0.0
 * Author:            Shuvo Rahman
 * Author URI:        https://myalice.ai/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       alice-chatbot
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    die;
}

/**
 * Check if WooCommerce is active
 **/
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

    class WC_Settings_Tab_Alice {

        /**
         * Bootstraps the class and hooks required actions & filters.
         *
         */
        public static function init() {
            add_filter( 'woocommerce_settings_tabs_array', __CLASS__ . '::add_settings_tab', 50 );
            add_action( 'woocommerce_settings_tabs_settings_tab_alice', __CLASS__ . '::settings_tab' );
            add_action( 'woocommerce_update_options_settings_tab_alice', __CLASS__ . '::update_settings' );
        }


        /**
         * Add a new settings tab to the WooCommerce settings tabs array.
         *
         * @param array $settings_tabs Array of WooCommerce setting tabs & their labels, excluding the Subscription tab.
         * @return array $settings_tabs Array of WooCommerce setting tabs & their labels, including the Subscription tab.
         */
        public static function add_settings_tab( $settings_tabs ) {
            $settings_tabs['settings_tab_alice'] = __( 'Alice Chatbot', 'alice-chatbot' );
            return $settings_tabs;
        }


        /**
         * Uses the WooCommerce admin fields API to output settings via the @see woocommerce_admin_fields() function.
         *
         * @uses woocommerce_admin_fields()
         * @uses self::get_settings()
         */
        public static function settings_tab() {
            woocommerce_admin_fields( self::get_settings() );
        }


        /**
         * Uses the WooCommerce options API to save settings via the @see woocommerce_update_options() function.
         *
         * @uses woocommerce_update_options()
         * @uses self::get_settings()
         */
        public static function update_settings() {
            woocommerce_update_options( self::get_settings() );
        }


        /**
         * Get all the settings for this plugin for @see woocommerce_admin_fields() function.
         *
         * @return array Array of settings for @see woocommerce_admin_fields() function.
         */
        public static function get_settings() {

            $settings = array(
                'section_title' => array(
                    'name'     => __( 'Alice Chatbot Key', 'alice-chatbot' ),
                    'type'     => 'title',
                    'desc'     => '',
                    'id'       => 'xl_settings_tab_alice_section_title'
                ),
                'title' => array(
                    'name' => __( 'Plugin Key', 'alice-chatbot' ),
                    'type' => 'text',
                    'desc' => __( 'Please enter the plugin key to verify the plugin and your website.', 'alice-chatbot' ),
                    'id'   => 'xl_settings_tab_alice_plugin_key'
                ),
                'section_end' => array(
                    'type' => 'sectionend',
                    'id' => 'xl_settings_tab_alice_section_end'
                )
            );

            return apply_filters( 'wc_settings_tab_alice_settings', $settings );
        }

    }

    WC_Settings_Tab_Alice::init();

    //    Insert JS code in Footer
    function xl_alice_javascript_footer() {

        $xl_settings_tab_alice_plugin_key = get_option( 'xl_settings_tab_alice_plugin_key' );
        $xl_api_url = 'https://live-v3.getalice.ai/api/ecommerce/plugins/connect-ecommerce-plugin?api_token='.$xl_settings_tab_alice_plugin_key.'';

        $response = wp_remote_post( $xl_api_url, array(
                'method'      => 'POST',
                'timeout'     => 45,
                'cookies'     => array()
            )
        );

        if ( is_wp_error( $response ) ) {
            $error_message = $response->get_error_message();
            echo "Something went wrong: $error_message";
        } else {
            $alice_admin_data = json_decode($response['body'], true);
        }

        ?>
        <script type="text/javascript">

            (function () {
                var div = document.createElement('div');
                div.id = 'icWebChat';
                var script = document.createElement('script');
                script.type = 'text/javascript';
                script.async = true;
                script.src = 'https://webchat.getalice.ai/index.js';
                var el = document.body.getElementsByTagName('script')[0];
                el.parentNode.insertBefore(div, el);
                el.parentNode.insertBefore(script, el);
                script.addEventListener('load', function () {
                    ICWebChat.init({ selector: '#icWebChat',
                        platformId: '<?php echo $alice_admin_data['platform_id']; ?>',
                        primaryId: '<?php echo $alice_admin_data['primary_id']; ?>',
                        token: '<?php echo $xl_settings_tab_alice_plugin_key; ?>' });
                });
            })();

        </script>

        <?php
    }
    add_action('wp_footer', 'xl_alice_javascript_footer');

} else {
    echo '<div id="message" class="error woocommerce-message"><p>To active Alice Chatbot, Please install and active Woocommerce.</p></div>';
}