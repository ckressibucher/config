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
        // TODO should we complain if $config is an indexed array?
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
    public function getAll()
    {
        return $this->data;
    }

    /**
     * @deprecated use `getAll` instead
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->getAll();
    }

    /**
     * @deprecated use `get` instead
     *
     * @param $path
     * @param null $default
     * @return mixed
     */
    public function getConfigValue($path, $default = null)
    {
        return $this->get($path, $default);
    }

    /**
     * Get a value specified by path
     *
     * The path contains parts separated by '/'. Each part describes one array dimension in
     * the configuration data. If a array key contains a '/', the given $path can escape
     * the slash with a preceding backslash:
     *
     * `getConfigValue('some\/path')`
     *
     * This would return the value 'x' in the array
     *
     * `['some/path' => 'x']`
     *
     * @param string     $path Segmented by '/' (to fetch deeper configuration values)
     * @param mixed      $default This value is returned if the given path is not found
     *
     * @return mixed
     */
    public function get($path, $default = null)
    {
        $path = strval($path);
        if (empty($path)) {
            throw new \InvalidArgumentException('given path is empty (after type cast to string)');
        }
        $pathParts = $this->explodePathParts($path);
        $config = $this->getAll();
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
     * Splits the given path in parts, separated by slashes (but allow escaping slashes with
     * a backslash)
     *
     * @param string $path
     * @return array
     */
    protected function explodePathParts($path)
    {
        $parts = [];
        $searchOffset = 0;
        $lastPos = 0;

        $fnRemoveEscapeChars = function($str) {
            return str_replace('\\/', '/', $str);
        };

        do {
            $i = strpos($path, '/', $searchOffset);
            if (false === $i) {
                // not found
                break;
            }
            if ($i > 0 && $path[$i-1] === '\\') {
                // escaped
                $searchOffset = $i + 1;
                continue;
            }
            $p = substr($path, $lastPos, $i-$lastPos);
            $parts[] = $fnRemoveEscapeChars($p);

            $lastPos = $searchOffset = $i + 1;
        } while ($searchOffset <= strlen($path));

        // add last part
        $parts[] = $fnRemoveEscapeChars(substr($path, $lastPos));
        return $parts;
    }

    /**
     * @deprecated use `child` instead
     *
     * @param string $path
     * @param bool $tolerant
     * @return Config
     * @throws InvalidStructureException
     */
    public function getChildConfig($path, $tolerant = false)
    {
        return $this->child($path, $tolerant);
    }

    /**
     * Get a child `Config`
     *
     * @param string $path
     * @param bool   $tolerant If true, and `$path` doesn't exist, return an
     *                         empty `Config` instead of throwing an Exception
     *
     * @throws InvalidStructureException
     * @throws \InvalidArgumentException
     * @throws NotFoundException
     * @return Config
     */
    public function child($path, $tolerant = false)
    {
        $notFoundEx = new NotFoundException(sprintf('The path "%s" doesn\'t exist', $path));
        $cfgValue = $this->get($path, $notFoundEx);

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
     * @deprecated use `set` instead
     *
     * @param string $path
     * @param mixed $value
     */
    public function setConfigValue($path, $value)
    {
        $this->set($path, $value);
    }

    /**
     * Sets a value to a given path.
     * The value may be a scalar, an array, or another Config instance
     *
     * @param string $path
     * @param mixed  $value
     */
    public function set($path, $value)
    {
        $pathParts = $this->explodePathParts($path);
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
                    $value = $value->getAll();
                }
                $arr[$key] = $value;
            } else {
                $arr[$key] = array();
                $arr = & $arr[$key];
            }
        }
    }
}
