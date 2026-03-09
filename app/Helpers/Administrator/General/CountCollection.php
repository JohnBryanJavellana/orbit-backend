<?php

namespace App\Helpers\Administrator\General;

class CountCollection
{
    /**
     * Summary of startCount
     * @param mixed $collection
     * @param string returns number or string (99+)
     */
    public static function startCount($collection)
    {
        $count = $collection->count();
        return $count > 99 ? '99+' : $count;
    }
}
