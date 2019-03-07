<?php
/**
 * Plugin Name: Pressjitsu Redis Object Cache
 * Author:      Pressjitsu, Inc., Eric Mann & Erick Hitter
 * Version:     1.0
 */

// Check if Redis class is installed
if ( ! class_exists( 'Redis' ) ) {
	return;
}

/**
 * Adds a value to cache.
 *
 * If the specified key already exists, the value is not stored and the function
 * returns false.
 *
 * @param string $key        The key under which to store the value.
 * @param mixed  $value      The value to store.
 * @param string $group      The group value appended to the $key.
 * @param int    $expiration The expiration time, defaults to 0.
 *
 * @global WP_Object_Cache $wp_object_cache
 *
 * @return bool              Returns TRUE on success or FALSE on failure.
 */
function wp_cache_add( $key, $value, $group = 'default', $expiration = 0 ) {
	global $wp_object_cache;
	return $wp_object_cache->add( $key, $value, $group, $expiration );
}

/**
 * Closes the cache.
 *
 * This function has ceased to do anything since WordPress 2.5. The
 * functionality was removed along with the rest of the persistent cache. This
 * does not mean that plugins can't implement this function when they need to
 * make sure that the cache is cleaned up after WordPress no longer needs it.
 *
 * @return  bool    Always returns True
 */
function wp_cache_close() {
	return true;
}

/**
 * Decrement a numeric item's value.
 *
 * @param string $key    The key under which to store the value.
 * @param int    $offset The amount by which to decrement the item's value.
 * @param string $group  The group value appended to the $key.
 *
 * @global WP_Object_Cache $wp_object_cache
 *
 * @return int|bool      Returns item's new value on success or FALSE on failure.
 */
function wp_cache_decr( $key, $offset = 1, $group = 'default' ) {
	global $wp_object_cache;
	return $wp_object_cache->decr( $key, $offset, $group );
}

/**
 * Remove the item from the cache.
 *
 * @param string $key    The key under which to store the value.
 * @param string $group  The group value appended to the $key.
 * @param int    $time   The amount of time the server will wait to delete the item in seconds.
 *
 * @global WP_Object_Cache $wp_object_cache
 *
 * @return bool           Returns TRUE on success or FALSE on failure.
 */
function wp_cache_delete( $key, $group = 'default', $time = 0 ) {
	global $wp_object_cache;
	return $wp_object_cache->delete( $key, $group, $time );
}

/**
 * Invalidate all items in the cache.
 *
 * @param int $delay  Number of seconds to wait before invalidating the items.
 *
 * @global WP_Object_Cache $wp_object_cache
 *
 * @return bool             Returns TRUE on success or FALSE on failure.
 */
function wp_cache_flush( $delay = 0 ) {
	global $wp_object_cache;
	return $wp_object_cache->flush( $delay );
}

/**
 * Retrieve object from cache.
 *
 * Gets an object from cache based on $key and $group.
 *
 * @param string      $key        The key under which to store the value.
 * @param string      $group      The group value appended to the $key.
 *
 * @global WP_Object_Cache $wp_object_cache
 *
 * @return bool|mixed             Cached object value.
 */
function wp_cache_get( $key, $group = 'default', $force = false ) {
	global $wp_object_cache;
	return $wp_object_cache->get( $key, $group, $force );
}

/**
 * Retrieve multiple values from cache.
 *
 * Gets multiple values from cache, including across multiple groups
 *
 * Usage: array( 'group0' => array( 'key0', 'key1', 'key2', ), 'group1' => array( 'key0' ) )
 *
 * Mirrors the Memcached Object Cache plugin's argument and return-value formats
 *
 * @param   array       $groups  Array of groups and keys to retrieve
 *
 * @global WP_Object_Cache $wp_object_cache
 *
 * @return  bool|mixed           Array of cached values, keys in the format $group:$key. Non-existent keys false
 */
function wp_cache_get_multi( $groups, $unserialize = true ) {
	global $wp_object_cache;
	return $wp_object_cache->get_multi( $groups, $unserialize );
}

/**
 * Increment a numeric item's value.
 *
 * @param string $key    The key under which to store the value.
 * @param int    $offset The amount by which to increment the item's value.
 * @param string $group  The group value appended to the $key.
 *
 * @global WP_Object_Cache $wp_object_cache
 *
 * @return int|bool      Returns item's new value on success or FALSE on failure.
 */
function wp_cache_incr( $key, $offset = 1, $group = 'default' ) {
	global $wp_object_cache;
	return $wp_object_cache->incr( $key, $offset, $group );
}

/**
 * Sets up Object Cache Global and assigns it.
 *
 * @global  WP_Object_Cache $wp_object_cache    WordPress Object Cache
 *
 * @return  void
 */
function wp_cache_init() {
	global $wp_object_cache;
	$wp_object_cache = new WP_Object_Cache();
}

/**
 * Replaces a value in cache.
 *
 * This method is similar to "add"; however, is does not successfully set a value if
 * the object's key is not already set in cache.
 *
 * @param string $key        The key under which to store the value.
 * @param mixed  $value      The value to store.
 * @param string $group      The group value appended to the $key.
 * @param int    $expiration The expiration time, defaults to 0.
 *
 * @global WP_Object_Cache $wp_object_cache
 *
 * @return bool              Returns TRUE on success or FALSE on failure.
 */
function wp_cache_replace( $key, $value, $group = 'default', $expiration = 0 ) {
	global $wp_object_cache;
	return $wp_object_cache->replace( $key, $value, $group, $expiration );
}

/**
 * Sets a value in cache.
 *
 * The value is set whether or not this key already exists in Redis.
 *
 * @param string $key        The key under which to store the value.
 * @param mixed  $value      The value to store.
 * @param string $group      The group value appended to the $key.
 * @param int    $expiration The expiration time, defaults to 0.
 *
 * @global WP_Object_Cache $wp_object_cache
 *
 * @return bool              Returns TRUE on success or FALSE on failure.
 */
function wp_cache_set( $key, $value, $group = 'default', $expiration = 0 ) {
	global $wp_object_cache;
	return $wp_object_cache->set( $key, $value, $group, $expiration );
}

/**
 * Switch the interal blog id.
 *
 * This changes the blog id used to create keys in blog specific groups.
 *
 * @param  int $_blog_id Blog ID
 *
 * @global WP_Object_Cache $wp_object_cache
 *
 * @return bool
 */
function wp_cache_switch_to_blog( $_blog_id ) {
	global $wp_object_cache;
	return $wp_object_cache->switch_to_blog( $_blog_id );
}

/**
 * Adds a group or set of groups to the list of Redis groups.
 *
 * @param   string|array $groups     A group or an array of groups to add.
 *
 * @global WP_Object_Cache $wp_object_cache
 *
 * @return  void
 */
function wp_cache_add_global_groups( $groups ) {
	global $wp_object_cache;
	$wp_object_cache->add_global_groups( $groups );
}

/**
 * Adds a group or set of groups to the list of non-Redis groups.
 *
 * @param   string|array $groups     A group or an array of groups to add.
 *
 * @global WP_Object_Cache $wp_object_cache
 *
 * @return  void
 */
function wp_cache_add_non_persistent_groups( $groups ) {
	global $wp_object_cache;
	$wp_object_cache->add_non_persistent_groups( $groups );
}

class WP_Object_Cache {

	/**
	 * Holds the Redis client.
	 *
	 * @var Redis
	 */
	private $redis;

	/**
	 * Track if Redis is available
	 *
	 * @var bool
	 */
	private $redis_connected = false;

	/**
	 * Local cache
	 *
	 * @var array
	 */
	public $cache = array();

	private $to_unserialize = array();

	public $to_preload = array();

	/**
	 * List of global groups.
	 *
	 * @var array
	 */
	public $global_groups = array( 'users', 'userlogins', 'usermeta', 'site-options', 'site-lookup', 'blog-lookup', 'blog-details', 'rss' );

	private $_global_groups;

	/**
	 * List of groups not saved to Redis.
	 *
	 * @var array
	 */
	public $no_redis_groups = array( 'comment', 'counts' );

	/**
	 * Prefix used for global groups.
	 *
	 * @var string
	 */
	public $global_prefix = '';

	/**
	 * Prefix used for non-global groups.
	 *
	 * @var string
	 */
	public $blog_prefix = '';

	/**
	 * Track how many requests were found in cache
	 *
	 * @var int
	 */
	public $cache_hits = 0;

	/**
	 * Track how may requests were not cached
	 *
	 * @var int
	 */
	public $cache_misses = 0;

	private $multisite;

	public $stats = array();

	/**
	 * Instantiate the Redis class.
	 *
	 * Instantiates the Redis class.
	 *
	 * @param   null $persistent_id      To create an instance that persists between requests, use persistent_id to specify a unique ID for the instance.
	 */
	public function __construct( $redis_instance = null ) {
		// General Redis settings
		$redis = array(
			'host' => '127.0.0.1',
			'port' => 6379,
		);

		if ( defined( 'WP_REDIS_BACKEND_HOST' ) && WP_REDIS_BACKEND_HOST ) {
			$redis['host'] = WP_REDIS_BACKEND_HOST;
		}
		if ( defined( 'WP_REDIS_BACKEND_PORT' ) && WP_REDIS_BACKEND_PORT ) {
			$redis['port'] = WP_REDIS_BACKEND_PORT;
		}
		if ( defined( 'WP_REDIS_BACKEND_AUTH' ) && WP_REDIS_BACKEND_AUTH ) {
			$redis['auth'] = WP_REDIS_BACKEND_AUTH;
		}
		if ( defined( 'WP_REDIS_BACKEND_DB' ) && WP_REDIS_BACKEND_DB ) {
			$redis['database'] = WP_REDIS_BACKEND_DB;
		}

		// Use Redis PECL library.
		try {
			if ( is_null( $redis_instance ) ) {
				$redis_instance = new Redis();
			}
			$this->redis = $redis_instance;
			$this->redis->connect( $redis['host'], $redis['port'] );
			$this->redis->setOption( Redis::OPT_SERIALIZER, Redis::SERIALIZER_NONE );

			if ( isset( $redis['auth'] ) ) {
				$this->redis->auth( $redis['auth'] );
			}

			if ( isset( $redis['database'] ) ) {
				$this->redis->select( $redis['database'] );
			}

			$this->redis_connected = true;
		} catch ( RedisException $e ) {
			// When Redis is unavailable, fall back to the internal back by forcing all groups to be "no redis" groups
			$this->no_redis_groups = array_unique( array_merge( $this->no_redis_groups, $this->global_groups ) );
			$this->redis_connected = false;
		}

		/**
		 * This approach is borrowed from Sivel and Boren. Use the salt for easy cache invalidation and for
		 * multi single WP installs on the same server.
		 */
		if ( ! defined( 'WP_CACHE_KEY_SALT' ) ) {
			define( 'WP_CACHE_KEY_SALT', '' );
		}

		$this->multisite = is_multisite();
		$this->blog_prefix = $this->multisite ? get_current_blog_id() . ':' : '';
		$this->_global_groups = array_flip( $this->global_groups );

		$this->maybe_preload();
	}

	public function maybe_preload() {
		if ( ! $this->can_redis() || empty( $_SERVER['REQUEST_URI'] ) ) {
			return;
		}

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			return;
		}

		$request_hash = md5( json_encode( array(
			$_SERVER['HTTP_HOST'],
			$_SERVER['REQUEST_URI'],
		) ) );

		$this->preload( $request_hash );

		if ( defined( 'DOING_TESTS' ) && DOING_TESTS ) {
			return $request_hash;
		}

		register_shutdown_function( array( $this, 'save_preloads' ), $request_hash );
	}

	public function preload( $hash ) {
		$keys = $this->get( $hash, 'pj-preload' );
		if ( is_array( $keys ) ) {
			$this->get_multi( $keys, false );
		}
	}

	public function save_preloads( $hash ) {
		$keys = array();

		foreach ( $this->to_preload as $group => $_keys ) {
			if ( $group === 'pj-preload' ) {
				continue;
			}

			if ( in_array( $group, $this->no_redis_groups ) ) {
				continue;
			}

			$_keys = array_keys( $_keys );
			$keys[ $group ] = $_keys;
		}

		$this->set( $hash, $keys, 'pj-preload' );
	}

	/**
	 * Is Redis available?
	 *
	 * @return bool
	 */
	protected function can_redis() {
		return $this->redis_connected;
	}

	/**
	 * Adds a value to cache.
	 *
	 * If the specified key already exists, the value is not stored and the function
	 * returns false.
	 *
	 * @param   string $key            The key under which to store the value.
	 * @param   mixed  $value          The value to store.
	 * @param   string $group          The group value appended to the $key.
	 * @param   int    $expiration     The expiration time, defaults to 0.
	 * @return  bool                   Returns TRUE on success or FALSE on failure.
	 */
	public function add( $_key, $value, $group, $expiration = 0 ) {
		if ( wp_suspend_cache_addition() ) {
			return false;
		}

		list( $key, $redis_key ) = $this->build_key( $_key, $group );

		if ( isset( $this->cache[ $group ], $this->cache[ $group ][ $key ] ) && false !== $this->cache[ $group ][ $key ] ) {
			return false;
		}

		return $this->set( $_key, $value, $group, $expiration );
	}

	/**
	 * Replace a value in the cache.
	 *
	 * If the specified key doesn't exist, the value is not stored and the function
	 * returns false.
	 *
	 * @param   string $key            The key under which to store the value.
	 * @param   mixed  $value          The value to store.
	 * @param   string $group          The group value appended to the $key.
	 * @param   int    $expiration     The expiration time, defaults to 0.
	 * @return  bool                   Returns TRUE on success or FALSE on failure.
	 */
	public function replace( $_key, $value, $group, $expiration = 0 ) {
		list( $key, $redis_key ) = $this->build_key( $_key, $group );

		// If group is a non-Redis group, save to internal cache, not Redis
		if ( in_array( $group, $this->no_redis_groups ) || ! $this->can_redis() ) {
			if ( ! isset( $this->cache[ $group ], $this->cache[ $group ][ $key ] ) ) {
				return false;
			}
		} else {
			if ( ! $this->redis->exists( $redis_key ) ) {
				return false;
			}
		}

		return $this->set( $_key, $value, $group, $expiration );
	}

	/**
	 * Remove the item from the cache.
	 *
	 * @param   string $key        The key under which to store the value.
	 * @param   string $group      The group value appended to the $key.
	 * @return  bool               Returns TRUE on success or FALSE on failure.
	 */
	public function delete( $_key, $group ) {
		list( $key, $redis_key ) = $this->build_key( $_key, $group );

		if ( in_array( $group, $this->no_redis_groups ) || ! $this->can_redis() ) {
			if ( ! isset( $this->cache[ $group ], $this->cache[ $group ][ $key ] ) ) {
				return false;
			}

			unset( $this->cache[ $group ][ $key ] );
			unset( $this->to_preload[ $group ][ $key ] );
			unset( $this->to_unserialize[ $redis_key ] );
			return true;
		}

		unset( $this->cache[ $group ][ $key ] );
		unset( $this->to_preload[ $group ][ $key ] );
		unset( $this->to_unserialize[ $redis_key ] );

		return (bool) $this->redis->delete( $redis_key );
	}

	/**
	 * Invalidate all items in the cache.
	 *
	 * @return  bool
	 */
	public function flush() {
		$this->cache = array();
		$this->to_preload = array();
		$this->to_unserialize = array();

		if ( $this->can_redis() ) {
			$this->redis->flushDb();
		}

		return true;
	}

	/**
	 * Retrieve object from cache.
	 *
	 * Gets an object from cache based on $key and $group.
	 *
	 * @param   string        $key        The key under which to store the value.
	 * @param   string        $group      The group value appended to the $key.
	 * @return  bool|mixed                Cached object value.
	 */
	public function get( $_key, $group = 'default', $force = false ) {
		list( $key, $redis_key ) = $this->build_key( $_key, $group );

		$this->to_preload[ $group ][ $_key ] = true;

		if ( ! $force && isset( $this->cache[ $group ][ $key ] ) ) {
			$value = $this->cache[ $group ][ $key ];

			if ( isset( $this->to_unserialize[ $redis_key ] ) ) {
				unset( $this->to_unserialize[ $redis_key ] );
				$value = unserialize( $value );
				$this->cache[ $group ][ $key ] = $value;
			}

			return is_object( $value ) ? clone $value : $value;
		}

		if ( in_array( $group, $this->no_redis_groups ) || ! $this->can_redis() ) {
			return false;
		}

		// Fetch from Redis
		$value = $this->redis->get( $redis_key );

		if ( ! is_string( $value ) ) {
			$this->cache[ $group ][ $key ] = false;
			return false;
		}

		$value = is_numeric( $value ) ? $value : unserialize( $value );
		$this->cache[ $group ][ $key ] = $value;
		return $value;
	}

	/**
	 * Retrieve multiple values from cache.
	 *
	 * Gets multiple values from cache, including across multiple groups
	 *
	 * Usage: array( 'group0' => array( 'key0', 'key1', 'key2', ), 'group1' => array( 'key0' ) )
	 *
	 * @param   array                           $groups  Array of groups and keys to retrieve
	 * @return  bool|mixed                               Array of cached values, keys in the format $group:$key. Non-existent keys null.
	 */
	public function get_multi( $groups, $unserialize = true ) {
		if ( empty( $groups ) || ! is_array( $groups ) ) {
			return false;
		}

		// Retrieve requested caches and reformat results to mimic Memcached Object Cache's output
		$cache = array();
		$fetch_keys = array();
		$map = array();

		foreach ( $groups as $group => $keys ) {
			if ( in_array( $group, $this->no_redis_groups ) || ! $this->can_redis() ) {
				foreach ( $keys as $_key ) {
					list( $key, $redis_key ) = $this->build_key( $_key, $group );
					$cache[ $group ][ $key ] = $this->get( $_key, $group );
				}

				continue;
			}

			if ( empty( $cache[ $group ] ) ) {
				$cache[ $group ] = array();
			}

			foreach ( $keys as $_key ) {
				list( $key, $redis_key ) = $this->build_key( $_key, $group );

				if ( isset( $this->cache[ $group ][ $key ] ) ) {
					$cache[ $group ][ $key ] = $this->cache[ $group ][ $key ];
					continue;
				}

				// Fetch these from Redis
				$map[ $redis_key ] = array( $group, $key );
				$fetch_keys[] = $redis_key;
			}
		}

		// Nothing else to fetch
		if ( empty( $fetch_keys ) ) {
			return $cache;
		}

		$results = $this->redis->mget( $fetch_keys );
		foreach( array_combine( $fetch_keys, $results ) as $redis_key => $value ) {
			list( $group, $key ) = $map[ $redis_key ];

			if ( is_string( $value ) ) {
				if ( ! $unserialize && ! is_numeric( $value ) ) {
					$this->to_unserialize[ $redis_key ] = true;
				} elseif ( $unserialize ) {
					$this->to_preload[ $group ][ $key ] = true;
					$value = is_numeric( $value ) ? $value : unserialize( $value );
				}
			} else {
				$value = false;
			}

			$this->cache[ $group ][ $key ] = $cache[ $group ][ $key ] = $value;
		}

		return $cache;
	}

	/**
	 * Sets a value in cache.
	 *
	 * The value is set whether or not this key already exists in Redis.
	 *
	 * @param   string $key        The key under which to store the value.
	 * @param   mixed  $value      The value to store.
	 * @param   string $group      The group value appended to the $key.
	 * @param   int    $expiration The expiration time, defaults to 0.
	 * @return  bool               Returns TRUE on success or FALSE on failure.
	 */
	public function set( $_key, $value, $group = 'default', $expiration = 0 ) {
		list( $key, $redis_key ) = $this->build_key( $_key, $group );

		if ( is_object( $value ) ) {
			$value = clone $value;
		}

		$this->cache[ $group ][ $key ] = $value;

		if ( in_array( $group, $this->no_redis_groups ) || ! $this->can_redis() ) {
			return true;
		}
		
		$value = is_numeric( $value ) ? $value : serialize( $value );

		// Save to Redis
		if ( $expiration ) {
			$this->redis->setex( $redis_key, $expiration, $value );
		} else {
			$this->redis->set( $redis_key, $value );
		}

		return true;
	}

	/**
	 * Increment a Redis counter by the amount specified
	 *
	 * @param  string $key
	 * @param  int    $offset
	 * @param  string $group
	 * @return bool
	 */
	public function incr( $_key, $offset = 1, $group ) {
		list( $key, $redis_key ) = $this->build_key( $_key, $group );

		if ( in_array( $group, $this->no_redis_groups ) || ! $this->can_redis() ) {
			// Consistent with the Redis behavior (start from 0 if not exists)
			if ( ! isset( $this->cache[ $group ][ $key ] ) ) {
				$this->cache[ $group ][ $key ] = 0;
			}

			$this->cache[ $group ][ $key ] += $offset;
			return true;
		}

		// Save to Redis
		$value = $this->redis->incrBy( $redis_key, $offset );
		$this->cache[ $group ][ $key ] = $value;
		return $value;
	}

	/**
	 * Decrement a Redis counter by the amount specified
	 *
	 * @param  string $key
	 * @param  int    $offset
	 * @param  string $group
	 * @return bool
	 */
	public function decr( $key, $offset = 1, $group = 'default' ) {
		return $this->incr( $key, $offset * -1, $group );
	}

	/**
	 * Builds a key for the cached object using the blog_id, key, and group values.
	 *
	 * @author  Ryan Boren   This function is inspired by the original WP Memcached Object cache.
	 * @link    http://wordpress.org/extend/plugins/memcached/
	 *
	 * @param   string $key        The key under which to store the value.
	 * @param   string $group      The group value appended to the $key.
	 *
	 * @return  array
	 */
	public function build_key( $key, $group = 'default' ) {
		$prefix = '';
		if ( ! isset( $this->_global_groups[ $group ] ) ) {
			$prefix = $this->blog_prefix;
		}

		$local_key = $prefix . $key;
		return array( $local_key, WP_CACHE_KEY_SALT . "$prefix$group:$key" );
	}

	/**
	 * In multisite, switch blog prefix when switching blogs
	 *
	 * @param int $_blog_id
	 * @return bool
	 */
	public function switch_to_blog( $blog_id ) {
		$this->blog_prefix = $this->multisite ? $blog_id . ':' : '';
	}

	/**
	 * Sets the list of global groups.
	 *
	 * @param array $groups List of groups that are global.
	 */
	public function add_global_groups( $groups ) {
		$groups = (array) $groups;

		if ( $this->can_redis() ) {
			$this->global_groups = array_unique( array_merge( $this->global_groups, $groups ) );
		} else {
			$this->no_redis_groups = array_unique( array_merge( $this->no_redis_groups, $groups ) );
		}

		$this->_global_groups = array_flip( $this->global_groups );
	}

	/**
	 * Sets the list of groups not to be cached by Redis.
	 *
	 * @param array $groups List of groups that are to be ignored.
	 */
	public function add_non_persistent_groups( $groups ) {
		$groups = (array) $groups;

		$this->no_redis_groups = array_unique( array_merge( $this->no_redis_groups, $groups ) );
	}
}
