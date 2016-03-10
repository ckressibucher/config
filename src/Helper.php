<?php


namespace Ckr\Config;

class Helper
{

    /**
     * If null, a default merger is used
     *
     * @var ArrayMergerInterface|null
     */
    protected $arrayMerger;

    /**
     * If null, the default array merger is used.
     * @see DefaultArrayMerger
     *
     * @param ArrayMergerInterface|null $arrMerger
     */
    public function __construct(ArrayMergerInterface $arrMerger = null)
    {
        $this->arrayMerger = $arrMerger;
    }

    /**
     * Merges the given arrays. The later arrays take
     * precedence over the former arrays.
     *
     * @param ...array A list of arrays
     * @return array
     */
    public function mergeArrays()
    {
        $arrays = func_get_args();
        return ($this->arrayMerger)
            ? call_user_func_array([$this->arrayMerger, 'mergeRecursively'], $arrays)
            : DefaultArrayMerger::mergeAllRecursively($arrays);
    }

    /**
     * Merge `Config` objects. The later objects take precedence over
     * the former objects.
     *
     * @param ...Config A list of Config objects. The later have higher priority
     * @return Config
     */
    public function mergeConfig()
    {
        $configs = func_get_args();
        $arrays = array_map(function (Config $cfg) {
            return $cfg->getAll();
        }, $configs);
        return new Config(call_user_func_array([$this, 'mergeArrays'], $arrays));
    }
}