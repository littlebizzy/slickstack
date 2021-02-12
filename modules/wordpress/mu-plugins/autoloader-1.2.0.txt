<?php

/*
Plugin Name: Autoloader
Plugin URI: https://www.littlebizzy.com/plugins/autoloader
Description: Enables standard WordPress plugins contained in a folder to be placed in the mu-plugins directory and loaded prior to others (forked from Bedrock).
Version: 1.2.0
Author: LittleBizzy
Author URI: https://www.littlebizzy.com
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Prefix: ATOLDR
*/

declare(strict_types=1);

// Plugin namespace
namespace LittleBizzy\Autoloader;

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) )
	exit;

// Check WP installation
if ( ! is_blog_installed() )
	return;

/**
 * Autoloader class
 *
 * Forked from Bedrock Autoloader
 * https://github.com/roots/bedrock/blob/master/web/app/mu-plugins/bedrock-autoloader.php
 */
class Autoloader {

	/** @var array Store Autoloader cache and site option */
	private static $cache;

	/** @var array Autoloaded plugins */
	private static $auto_plugins;

	/** @var array Autoloaded mu-plugins */
	private static $mu_plugins;

	/** @var int Number of plugins */
	private static $count;

	/** @var array Newly activated plugins */
	private static $activated;

	/** @var string Relative path to the mu-plugins dir */
	private static $relative_path;

	/** @var static Singleton instance */
	private static $_single;

	/**
	 * Create singleton, populate vars, and set WordPress hooks
	 */
    public function __construct() {

		if ( isset (self::$_single ) )
			return;

		self::$_single = $this;
		self::$relative_path = '/../' . basename(__DIR__);

		if ( is_admin() )
			add_filter('show_advanced_plugins', [$this, 'showInAdmin'], 0, 2);

		$this->loadPlugins();
    }

	/**
	 * Run some checks then autoload our plugins.
	 */
    public function loadPlugins(): void {

		$this->checkCache();
		$this->validatePlugins();
		$this->countPlugins();

		array_map( static function () {
			include_once(WPMU_PLUGIN_DIR . '/' . func_get_args()[0]);
		}, self::$cache['plugins'] );

		$this->pluginHooks();
    }

	/**
	 * Filter show_advanced_plugins to display the autoloaded plugins.
	 * @param $bool bool Whether to show the advanced plugins for the specified plugin type.
	 * @param $type string The plugin type, i.e., `mustuse` or `dropins`
	 * @return bool We return `false` to prevent WordPress from overriding our work
	 * {@internal We add the plugin details ourselves, so we return false to disable the filter.}
	 */
    public function showInAdmin( bool $show, string $type): bool {

		$screen = get_current_screen();
		$current = is_multisite() ? 'plugins-network' : 'plugins';

		if ( $screen->{'base'} != $current || $type != 'mustuse' || !current_user_can('activate_plugins') ) {
			return $show;
		}

		$this->updateCache();

		self::$auto_plugins = array_map(function ($auto_plugin) {
			$auto_plugin['Name'] .= ' *';
			return $auto_plugin;
		}, self::$auto_plugins);

		$GLOBALS['plugins']['mustuse'] = array_unique(array_merge(self::$auto_plugins, self::$mu_plugins), SORT_REGULAR);

		return false;
	}

	/**
	 * This sets the cache or calls for an update
	 */
	private function checkCache(): void {

		$cache = get_site_option('littlebizzy_autoloader');
		if ($cache === false) {
			$this->updateCache();
			return;
		}

		self::$cache = $cache;
	}

	/**
	 * Get the plugins and mu-plugins from the mu-plugin path and remove duplicates.
	 * Check cache against current plugins for newly activated plugins.
	 * After that, we can update the cache.
	 */
	private function updateCache(): void {

		require_once(ABSPATH . 'wp-admin/includes/plugin.php');

		self::$auto_plugins = get_plugins(self::$relative_path);
		self::$mu_plugins   = get_mu_plugins();
		$plugins            = array_diff_key(self::$auto_plugins, self::$mu_plugins);
		$rebuild            = !is_array(self::$cache['plugins']);
		self::$activated    = ($rebuild) ? $plugins : array_diff(array_keys($plugins), self::$cache['plugins']);
		self::$cache        = array('plugins' => array_keys($plugins), 'count' => $this->countPlugins(false));

		update_site_option('littlebizzy_autoloader', self::$cache);
	}

	/**
	 * This accounts for the plugin hooks that would run if the plugins were
	 * loaded as usual. Plugins are removed by deletion, so there's no way
	 * to deactivate or uninstall.
	 */
	private function pluginHooks(): void {

		if (!is_array(self::$activated)) {
			return;
		}

		foreach (self::$activated as $plugin_file) {
			do_action('activate_' . $plugin_file);
		}
	}

	/**
	 * Check that the plugin file exists, if it doesn't update the cache.
	 */
	private function validatePlugins(): void {

		foreach (self::$cache['plugins'] as $plugin_file) {
			if (!file_exists(WPMU_PLUGIN_DIR . '/' . $plugin_file)) {
				$this->updateCache();
				break;
			}
		}
	}

	/**
	 * Count the number of autoloaded plugins.
	 *
	 * Count our plugins (but only once) by counting the top level folders in the
	 * mu-plugins dir. If it's more or less than last time, update the cache.
	 *
	 * @return int Number of autoloaded plugins.
	 */
	private function countPlugins( bool $updateCache = true ): string  {

		if (!isset(self::$count)) {
			self::$count = count(glob(WPMU_PLUGIN_DIR . '/*/', GLOB_ONLYDIR | GLOB_NOSORT));
		}

		if ($updateCache && (!isset(self::$cache['count']) || self::$count != self::$cache['count'])) {
			$this->updateCache();
		}

		return self::$count;
	}
}

// Autostart
new Autoloader;
