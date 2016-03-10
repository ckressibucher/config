<?php

namespace Ckr\Config;

class Config
{

    /**
     * @var array
     */
    private $data;

    /**
     * Wraps a -- potentially multidimensional -- array
     * in a `Config` object
     *
     * @param array $config The actual config data
     */
    public function __construct(array $config = [])
    {
        $this->data = $config;
    }

    /**
     * Get complete configuration data as array.
     *
     * If the object has not been modified, this is
     * the same data that was initially given
     * to the constructor.
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->data;
    }

    /**
     * Get a value specified by path
     *
     * TODO allow escaping slash in path parts
     *
     * @param string     $path Segmented by '/' (to fetch deeper configuration values)
     * @param mixed      $default This value is returned if the given path is not found
     *
     * @return mixed
     */
    public function getConfigValue($path, $default = null)
    {
        $path = strval($path);
        if (empty($path)) {
            throw new \InvalidArgumentException('given path is empty (after type cast to string)');
        }
        $pathParts = \explode('/', $path);
        $config = $this->getConfig();
        while (\count($pathParts) > 1) {
            $key = array_shift($pathParts);
            if (!isset($config[$key])) {
                return $default;
            }
            $config = & $config[$key];
        }
        $key = \current($pathParts);
        return isset($config[$key]) ? $config[$key] : $default;
    }

    /**
     * @param string $path
     * @param bool   $tolerant If true, and `$path` doesn't exist, return an
     *                         empty `Config` instead of throwing an Exception
     *
     * @throws InvalidStructureException
     * @throws \InvalidArgumentException
     * @throws NotFoundException
     * @return Config
     */
    public function getChildConfig($path, $tolerant = false)
    {
        $notFoundEx = new NotFoundException(sprintf('The path "%s" doesn\'t exist', $path));
        $cfgValue = $this->getConfigValue($path, $notFoundEx);

        if (is_array($cfgValue)) {
            return new Config($cfgValue);
        } elseif ($notFoundEx === $cfgValue) {
            if ($tolerant) {
                return new Config();
            }
            throw $notFoundEx;
        } else {
            $msg = \sprintf('The given path "%s" contains a scalar but is expected to contain an array', $path);
            throw new InvalidStructureException($msg);
        }
    }

    /**
     * Sets a value to a given path.
     * The value may be a scalar, an array, or another Config instance
     *
     * @param string $path
     * @param mixed  $value
     */
    public function setConfigValue($path, $value)
    {
        $pathParts = explode('/', $path);
        $arr = & $this->data;
        while ($key = array_shift($pathParts)) {
            if (!isset($arr[$key]) || count($pathParts) === 0) {
                array_unshift($pathParts, $key);
                break;
            }
            $arr = & $arr[$key];
        }
        while ($key = array_shift($pathParts)) {
            if (count($pathParts) === 0) {
                if ($value instanceof Config) {
                    $value = $value->getConfig();
                }
                $arr[$key] = $value;
            } else {
                $arr[$key] = array();
                $arr = & $arr[$key];
            }
        }
    }
}
