<?php


namespace Ckr\Config;

use Ckr\Util\ArrayMerger;

class DefaultArrayMerger implements ArrayMergerInterface
{

    /**
     * @param ...array A list of arrays. The later arrays have higher priority
     * @return array
     */
    public function mergeRecursively()
    {
        $arrays = func_get_args();
        $current = [];
        while(null !== ($next = \array_shift($arrays))) {
            // first, check if one array is empty; if not, do the merge operation
            if (empty($current)) {
                $current = $next;
            } elseif (empty($next)) {
                // nothing to do
            } else {
                $merge = new ArrayMerger($current, $next);
                $current = $merge->overwriteNumericKey(true)->mergeData();
            }
        }
        return $current;
    }

    /**
     * Static wrapper around `mergeRecursively`
     * @param ...array A list of arrays
     * @return mixed
     */
    public static function mergeAllRecursively()
    {
        $merger = new static();
        return call_user_func_array([$merger, 'mergeRecursively'], func_get_args());
    }
}