<?php


namespace Ckr\Config;


class Helper
{

    protected $arrayMerger;

    public function __construct(ArrayMergerInterface $arrMerger)
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
        return call_user_func_array([$this->arrayMerger, 'mergeRecursively'], $arrays);
    }

    /**
     * Merge `Config` objects. The later objects take precedence over
     * the former objects.
     *
     * @param ...Config A list of Config objects
     * @return Config
     */
    public function mergeConfig()
    {
        $configs = func_get_args();
        $arrays = array_map(function ($cfg) { return $cfg->getConfig(); }, $configs);
        $merged = call_user_func_array([$this->arrayMerger, 'mergeRecursively'], $arrays);
        return new Config($merged);
    }
}