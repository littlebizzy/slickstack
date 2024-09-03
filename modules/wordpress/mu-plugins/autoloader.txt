<?php

/*
Plugin Name: Autoloader
Plugin URI: https://www.littlebizzy.com/plugins/autoloader
Description: Loads folder WordPress MU plugins
Version: 2.0.0
Author: LittleBizzy
Author URI: https://www.littlebizzy.com
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Prefix: ATOLDR
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Autoloader class to load plugins from mu-plugins directories.
 */
class Autoloader {

    /** @var array Autoloaded plugins and their metadata */
    private static $auto_plugins = [];

    /** @var array Cache storage */
    private static $cache = [];

    /** @var static Singleton instance */
    private static $instance = null;

    /**
     * Get the singleton instance of the class.
     *
     * @return static
     */
    public static function get_instance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor to initialize plugin loading and admin hooks.
     */
    private function __construct() {
        // Load cache from site options
        self::$cache = get_site_option('autoloader_cache', []);

        // Load plugins
        $this->load_plugins();

        // Admin display hooks
        if (is_admin()) {
            add_filter('show_advanced_plugins', [$this, 'show_in_admin'], 0, 2);
        }
    }

    /**
     * Load plugins defined in the mu-plugins subdirectories.
     */
    private function load_plugins(): void {
        $this->validate_cache();
        $this->include_plugins();
        $this->run_activation_hooks();
    }

    /**
     * Validate and update the cache if necessary.
     */
    private function validate_cache(): void {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';

        // Get all MU plugins and standard plugins within mu-plugins subdirectories
        $mu_plugins = get_mu_plugins();
        $auto_plugins = get_plugins('/../' . basename(__DIR__));
        $plugins = array_diff_key($auto_plugins, $mu_plugins); // Exclude any plugins already loaded as MU plugins

        // Determine if cache needs updating
        $needs_update = !isset(self::$cache['plugins']) || array_keys($plugins) !== self::$cache['plugins'];
        if ($needs_update) {
            self::$cache = [
                'plugins' => array_keys($plugins),
                'count' => count($plugins)
            ];
            update_site_option('autoloader_cache', self::$cache);
        }

        self::$auto_plugins = $plugins;
    }

    /**
     * Include each plugin file in the autoloaded plugins.
     */
    private function include_plugins(): void {
        foreach (self::$cache['plugins'] as $plugin_file) {
            if (file_exists(WPMU_PLUGIN_DIR . '/' . $plugin_file)) {
                include_once WPMU_PLUGIN_DIR . '/' . $plugin_file;
            }
        }
    }

    /**
     * Run activation hooks for any newly activated plugins.
     */
    private function run_activation_hooks(): void {
        if (!isset(self::$cache['plugins'])) {
            return;
        }

        // Find plugins that are newly activated
        $activated_plugins = array_diff(self::$cache['plugins'], array_keys(self::$auto_plugins));
        foreach ($activated_plugins as $plugin_file) {
            do_action('activate_' . $plugin_file);
        }
    }

    /**
     * Show autoloaded plugins in the WordPress admin.
     *
     * @param bool $show Whether to show the advanced plugins.
     * @param string $type The plugin type.
     * @return bool False to prevent WordPress from overriding the display.
     */
    public function show_in_admin(bool $show, string $type): bool {
        if ($type !== 'mustuse') {
            return $show;
        }

        // Ensure each plugin entry is valid and append an asterisk to its name
        foreach (self::$auto_plugins as $plugin_path => $plugin) {
            if (is_array($plugin) && isset($plugin['Name']) && is_string($plugin['Name'])) {
                self::$auto_plugins[$plugin_path]['Name'] .= ' *';
            }
        }

        // Merge autoloaded plugins into the MU plugins list, but do not override existing entries
        $GLOBALS['plugins']['mustuse'] = array_merge(
            get_mu_plugins(),
            self::$auto_plugins
        );

        return false;
    }
}

// Initialize the autoloader.
Autoloader::get_instance();

// Ref: ChatGPT
// Ref: Roots Bedrock
