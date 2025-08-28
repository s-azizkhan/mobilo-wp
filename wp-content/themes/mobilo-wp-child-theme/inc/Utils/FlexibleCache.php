<?php

namespace Mobilo\WpTheme\Utils;

defined('ABSPATH') || exit;

// Cache type constants
const CACHE_TYPE_TRANSIENT = 'transient';
const CACHE_TYPE_OBJECT    = 'object';

/**
 * Trait FlexibleCache
 * Provides flexible caching capabilities using either transients or object cache.
 *
 * @since 0.0.1
 * @version 1.0.0
 * @package Mobilo\WpTheme\Utils
 * @author S.Aziz Khan <hi@justaziz.com>
 */
trait FlexibleCache
{
    // Default cache type
    private $cache_type = CACHE_TYPE_TRANSIENT;

    /**
     * Set the cache type.
     * @param string $cache_type The type of cache to use (transient or object).
     */
    public function set_cache_type($cache_type = CACHE_TYPE_TRANSIENT)
    {
        $this->cache_type = in_array($cache_type, [CACHE_TYPE_TRANSIENT, CACHE_TYPE_OBJECT]) ? $cache_type : CACHE_TYPE_TRANSIENT;
    }

    /**
     * Set a cache item.
     * @param string $key The cache key.
     * @param mixed $value The value to cache.
     * @param int $expiration The expiration time in seconds.
     * @return bool True on success, false on failure.
     */
    public function set_cache($key, $value, $expiration = 0, $group = '')
    {
        if ($this->cache_type === CACHE_TYPE_TRANSIENT) {
            return set_transient($key, $value, $expiration);
        } else {
            return wp_cache_set($key, $value, $group, $expiration);
        }
    }

    /**
     * Get a cache item.
     * @param string $key The cache key.
     * @return mixed The cached value, or false if not found.
     */
    public function get_cache($key, $group = '')
    {
        //mobilo_log(__METHOD__, "key: $key, group: $group", 'info');
        if ($this->cache_type === CACHE_TYPE_TRANSIENT) {
            return get_transient($key);
        } else {
            return wp_cache_get($key, $group);
        }
    }

    /**
     * Forget (delete) a cache item.
     * @param string $key The cache key.
     * @return bool True on success, false on failure.
     */
    public function forget_cache($key)
    {
        if ($this->cache_type === CACHE_TYPE_TRANSIENT) {
            return delete_transient($key);
        } else {
            return wp_cache_delete($key);
        }
    }
}
