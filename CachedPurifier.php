<?php

/**
 *
 * @package yii2_cached_purifier
 * @author Linus Kohl <linus@munichresearch.com>
 * @copyright Copyright &copy; Linus Kohl, 2018
 * @version 1.0.0
 */
namespace munichresearch\yii2_cached_purifier;

use Yii;
use yii\caching\Cache;
use yii\base\Component;
use yii\base\InvalidConfigException;

/**
 * Class CachedPurifier
 * @package munichresearch\yii2_cached_purifier
 */
class CachedPurifier extends Component
{

    const DEF_CACHE_DIR = "/cache/htmlpurifier";

    /** @var \yii\caching\Cache $cache */
    public  $cache;

    /** @var int $cache_duration Duration to store the secured strings. Set it to 0 to disable expiration */
    public  $cache_duration = 0;

    /** @var  \HTMLPurifier_Config $config */
    public  $config;

    /** @var string $cache_path Path of HTMLPurifiers cache*/
    public  $cache_path;

    /** @var int $cache_perm Permissions of the cache folder */
    public  $cache_perm = 0755;

    /** @var string $key_prefix Prefix for the cache keys */
    public  $key_prefix = "secured_strings::";

    /** @var string $key_hash Hash used to create key */
    public $key_hash = "sha512";


    /**
     * Init component
     *
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();

        // setup cache
        if($this->cache) {
            if(is_string($this->cache)) {
                // set cache to cache component if string is submitted
                $this->cache = \Yii::$app->{$this->cache};
            }
            // throw exception if cache interface is unavailable
            if(!$this->cache || !$this->cache instanceof yii\caching\CacheInterface) {
                throw new InvalidConfigException();
            }
        }

        // setup HTMLPurifier

        // create default config
        $this->config = \HTMLPurifier_Config::create($this->config instanceof \HTMLPurifier_Config ? $this->config : null);
        $this->config->autoFinalize = false;

        // setup path of HTMLPurifier's cache
        if(!$this->cache_path) {
            $this->cache_path = \Yii::$app->getRuntimePath() . self::DEF_CACHE_DIR;
        }

        // check if cache path is writable and try to create if it does not exist
        if (!is_writable($this->cache_path) && is_dir($this->cache_path)) {
            throw new InvalidConfigException(sprintf("Unable to write to directory (%s).", $this->cache_path));
        } elseif (!is_dir($this->cache_path)) {
            $umask_tmp = umask(0);
            if (false === @mkdir($this->cache_path, $this->cache_perm, true) && !is_dir($this->cache_path)) {
                throw new InvalidConfigException(sprintf("Unable to create the directory (%s).", $this->cache_path));
            }
            umask($umask_tmp);
        }

        // set cache path
        $this->config->set('Cache.SerializerPath', $this->cache_path);
        $this->config->set('Cache.SerializerPermissions', $this->cache_perm);
    }

    /**
     * Update HTMLPurifier config
     *
     * @param \HTMLPurifier_Config $config
     * @return boolean
     */
    public function setConfig($config)
    {
        if($config && $config instanceof \HTMLPurifier_Config) {
            $this->config = $config;
            return true;
        }
        return false;
    }

    /**
     * Purify string
     *
     * @param  string                    $string String that needs to be purified
     * @param  null|\HTMLPurifier_Config $config
     * @return string Purified string
     */
    public function purify($string, $config = null)
    {
        // create HTMLPurifier
        $purifier = \HTMLPurifier::instance(($config && $config instanceof \HTMLPurifier_Config )? $config : $this->config);

        // build key from hash
        $key = $this->key_prefix . hash($this->key_hash, $string);

        // try to get from cache
        $secured = $this->cache->get($key);

        if ($secured != false) {
            return $secured;
        }

        // secure the string
        $secured = $purifier->purify($string);

        // add secured string to cache
        $this->cache->add($key, $secured, $this->cache_duration);

        return $secured;
    }

}
