<?php
require_once __DIR__ . "/../lib/phpfastcache/src/autoload.php";
require_once __DIR__ . "/../config/config.php";

use phpFastCache\CacheManager;
use phpFastCache\Util\Languages;

class CacheUtils {

    private $devMode;
    private $cacheInstance;
    private $cacheItem;
    private $key;

    public function __construct($key) {
        // If devMode is set, the cache will be invalidated immediately
        $this->devMode = defined("DEV_MODE") || getenv("DEV_MODE") || file_exists(__DIR__ . "/dev_mode");

        if(!is_string($key))
            throw new InvalidArgumentException("Key must be a string");

        global $config;
        if(isset($config["general"]["timezone"])) {
            date_default_timezone_set($config["general"]["timezone"]);
        }

        $this->cacheInstance = CacheManager::getInstance('Files', ["path" => __DIR__ . '/../cache']);
        Languages::setEncoding();
        $this->cacheItem = $this->cacheInstance->getItem($key);
        $this->key = $key;
    }

    public function getCacheInstance() {
        return $this->cacheInstance;
    }

    public function getCacheItem() {
        return $this->cacheItem;
    }

    public function getValue() {
        return $this->cacheItem->get();
    }

    public function setValue($value, $expireTime) {
        if($this->devMode)
            $expireTime = 1;

        $this->cacheItem = $this->cacheItem->set($value)->expiresAfter($expireTime);
        $this->cacheInstance->save($this->cacheItem);
    }

    public function isExpired() {
        return $this->devMode || !$this->cacheItem->isHit();
    }

    public function remove() {
        $this->cacheInstance->deleteItem($this->key);
    }

}
