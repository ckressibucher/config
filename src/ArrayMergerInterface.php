<?php


namespace Ckr\Config;


interface ArrayMergerInterface {

    /**
     * Merge arrays recursively. The later arrays take precedence
     * over the former arrays
     *
     * @param ...array A list of arrays
     * @return array
     */
    public function mergeRecursively();
} 