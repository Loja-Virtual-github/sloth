<?php

namespace LojaVirtual\Sloth;

use Psr\SimpleCache\CacheInterface;
use LojaVirtual\Sloth\Providers;

use SplFileInfo;

class Cache implements CacheInterface
{
    /**
     * Adapter that will be responsible for creating and managing the cache
     *
     * @var ProviderInterface
     */
    private $provider;

    /**
     * State variable to identify in log file
     *
     * @var string
     */
    private $state = null;

    /**
     * Constructor
     *
     * @param ProviderInterface $provider
     */
    public function __construct(Providers $provider, $state = null)
    {
        $this->provider = $provider;
        $this->state = $state;

        if (method_exists($this->provider, 'setState')) {
            $this->provider->setState($this->state);
        }
    }

    public function __call($method, $arguments)
    {
        if (method_exists($this->provider, $method) && !empty($arguments[0])) {
            return call_user_method($method, $this->provider, $arguments[0]);
        }
    }

    /**
     * Fetches a value from the cache
     *
     * @param string $key       The unique key of this item in the cache
     * @param mixed $default    Default value to return if the key does not exist
     * 
     * @return mixed            The value of the item from the cache, or $default in case of cache miss
     * 
     * @throws \Psr\SimpleCache\InvalidArgumentException    thrown if the $key string is not a legal value
     */
    public function get($key, $default = null)
    {
        return $this
            ->provider
            ->get($key, $default);
    }

    /**
     * Persists data in the cache, uniquely referenced by a key with an optional expiration TTL time
     *
     * @param string                $filename       The key of the item to store
     * @param mixed                 $filepath       The value of the item to store. Must be serializable
     * @param null|int|DateInterval $ttl            Optional. The TTL value of this item. If no value is sent and
     *                                      the driver supports TTL then the library may set a default value
     *                                      for it or let the driver take care of that.
     * 
     * @return bool                         True on success and false on failure
     * 
     * @throws \Psr\SimpleCache\InvalidArgumentException    thrown if the $filename string is not a legal value
     */
    public function set($filename, $filepath, $ttl = null)
    {
        return $this
            ->provider
            ->set($filename, $filepath, $ttl);
    }

    /**
     * Delete an item from the cache by its unique key
     *
     * @param string $key   The unique cache key of the item to delete
     * 
     * @return bool         True if the item was successfully removed. False if there was an error
     * 
     * @throws \Psr\SimpleCache\InvalidArgumentException    thrown if the $key string is not a legal value
     */
    public function delete($key)
    {
        return $this
            ->provider
            ->delete($key);
    }

    /**
     * Wipes clean the entire cache's keys
     *
     * @return bool True on success and false on failure
     */
    public function clear()
    {
        return $this
            ->provider
            ->clear();
    }

    /**
     * Obtains multiple cache items by their unique keys
     *
     * @param iterable  $keys
     * @param mixed     $default
     * 
     * @return iterable A list of key => value pairs. Cache keys that do not exist or are stale will have $default as value.
     * 
     * @throws \Psr\SimpleCache\InvalidArgumentException    thrown if $keys is neither an array nor a Traversable,
     *   or if any of the $keys are not a legal value
     */
    public function getMultiple($keys, $default = null, $fromCache = false)
    {
        return $this
            ->provider
            ->getMultiple($keys, $default, $fromCache);
    }

    /**
     * Persists a set of key => value pairs in the cache, with an optional TTL.
     *
     * @param iterable                  $values
     * @param null|int|\DateInterval    $ttl
     * 
     * @return bool True on success and false on failure
     * 
     * @throws \Psr\SimpleCache\InvalidArgumentException    thrown if $values is neither an array nor a Traversable,
     *   or if any of the $values are not a legal value.
     */
    public function setMultiple($values, $ttl = null)
    {
        return $this->provider->setMultiple($values, $ttl);
    }

    /**
     * Deletes multiple cache items in a single operation
     *
     * @param iterable $keys    A list of string-based keys to be deleted
     * 
     * @return bool True if the items were successfully removed. False if there was an error
     * 
     * @throws \Psr\SimpleCache\InvalidArgumentException    thrown if $keys is neither an array nor a Traversable,
     *   or if any of the $keys are not a legal value
     */
    public function deleteMultiple($keys)
    {
        return $this->provider->deleteMultiple($keys);
    }

    /**
     * Determines whether an item is present in the cache
     *
     * @param string $key
     * 
     * @return boolean
     * 
     * @throws \Psr\SimpleCache\InvalidArgumentException    thrown if the $key string is not a legal value
     */
    public function has($key)
    {
        return $this
            ->provider
            ->has($key);
    }

    public function process($file)
    {
        return $this
            ->provider
            ->process($file);
    }

    public function getBuildCacheName($keys = null)
    {
        return $this
            ->provider
            ->getBuildCacheName($keys);
    }

    public function setCacheAvailable($available)
    {
        $this
            ->provider
            ->setCacheAvailable($available);
    }
}