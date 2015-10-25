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
     * @param array[] $arrays
     * @return array
     */
    public function mergeArrays(...$arrays)
    {
        return $this->arrayMerger->mergeRecursively(...$arrays);
    }

    /**
     * Merge `Config` objects. The later objects take precedence over
     * the former objects.
     *
     * @param Config[] $configs
     * @return Config
     */
    public function mergeConfig(...$configs)
    {
        $arrays = array_map(function ($cfg) { return $cfg->getConfig(); }, $configs);
        $merged = $this->arrayMerger->mergeRecursively($arrays);
        return new Config($merged);
    }
}