<?php
/*
 * Plugin Name:          Customer Reviews
 * Plugin URI:           https://wordpress.org/plugins/customer-reviews/
 * Description:          Manage and display customer-submitted reviews for products and services. A shortcode can be added to any page, post, or custom post type.
 * Version:              1.1.0
 * Author:               Artios Media
 * Author URI:           https://www.artiosmedia.com
 * Developer:            Arafat Rahman
 * Copyright:            © 2019-2025 Artios Media (email : contact@artiosmedia.com).
 * License:              GPLv3 or later
 * License URI:          https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:          customer-reviews
 * Domain Path:          /languages
 * Tested up to:         6.8.1
 * Requires at least:    5.0
 * Requires PHP:         7.4
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main Customer Reviews Plugin Class.
 *
 * A final class to ensure it cannot be extended, implementing a singleton pattern
 * to make sure that the plugin is only loaded once.
 *
 * @since 1.1.0
 */
final class Customer_Reviews_Plugin {

    /**
     * The single instance of the class.
     *
     * @var Customer_Reviews_Plugin|null
     */
    private static ?Customer_Reviews_Plugin $instance = null;

    /**
     * Get the single instance of the class.
     *
     * @return Customer_Reviews_Plugin
     */
    public static function get_instance(): Customer_Reviews_Plugin {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Private constructor to prevent direct instantiation.
     */
    private function __construct() {
        $this->define_constants();
        $this->includes();
        $this->init_hooks();
    }

    /**
     * Define plugin constants.
     */
    private function define_constants(): void {
        define('CTRW_PLUGIN_VERSION', '1.1.0');
        define('CTRW_PLUGIN_PATH', plugin_dir_path(__FILE__));
        define('CTRW_PLUGIN_URL', plugin_dir_url(__FILE__));
        define('CTRW_PLUGIN_ASSETS', CTRW_PLUGIN_URL . 'assets/');
        define('CTRW_BASE_NAME', plugin_basename(__FILE__));
        define('CTRW_TEXT_DOMAIN', 'customer-reviews');
    }

    /**
     * Include required plugin files.
     */
    private function includes(): void {
        // Core MVC files
        include_once CTRW_PLUGIN_PATH . 'includes/ctrw-model.php';
        include_once CTRW_PLUGIN_PATH . 'includes/ctrw-view.php';
        include_once CTRW_PLUGIN_PATH . 'includes/ctrw-controller.php';
    }

    /**
     * Initialize WordPress hooks.
     */
    private function init_hooks(): void {
        // Activation, deactivation, and uninstall hooks must be static
        register_activation_hook(__FILE__, ['CTRW_Controller', 'activate']);
        register_uninstall_hook(__FILE__, ['CTRW_Controller', 'uninstall']);

        // Instantiate the controller to get all other hooks and filters running
        new CTRW_Controller();
    }
}

// Initialize the plugin
Customer_Reviews_Plugin::get_instance();