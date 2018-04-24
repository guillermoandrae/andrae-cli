<?php

namespace App\Db\Helpers;

class QueryHelper
{
    const KEY_TYPE_HASH = 'HASH';

    const KEY_TYPE_RANGE = 'RANGE';

    const ATTRIBUTE_TYPE_STRING = 'S';

    const ATTRIBUTE_TYPE_NUMBER = 'N';

    public static function where(array $where): array
    {
        /*
        'field' => 'name',
        'operator' => 'EQ',
        'type' => 'S',
        'value' => 10
         */
    }
}
