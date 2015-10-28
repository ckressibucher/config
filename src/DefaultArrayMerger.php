<?php


namespace Ckr\Config;

use Ckr\Util\ArrayMerger;

class DefaultArrayMerger implements ArrayMergerInterface
{

    /**
     * @param ...array A list of arrays
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
}