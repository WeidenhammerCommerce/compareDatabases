<?php
/**
 * @category    Hammer
 * @copyright   Copyright (c) 2019 Weidenhammer Systems Corporation
 */

namespace Console\App\Commands;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Style\SymfonyStyle;

class CoreConfigData extends \Illuminate\Database\Eloquent\Model
{
    protected $connection;
    protected $table = 'core_config_data';
    protected $primaryKey = 'config_id';

    public static function compareConfigurations($array1, $array2)
    {
        $array3 = [];

        // Add values from db1
        foreach ($array1 as $a1) {
            $array3[] = [
                'path'      => $a1['path'],
                'scope_id'  => $a1['scope_id'],
                'db1_value' => self::setValue($a1['value']),
                'db2_value' => ''
            ];
        }

        // Update values with db2
        foreach ($array3 as $key => $row) {
            foreach ($array2 as $a2) {
                if ($row['path'] == $a2['path'] && $row['scope_id'] == $a2['scope_id']) {
                    $array3[$key]['db2_value'] = self::setValue($a2['value']);
                }
            }
        }

        // Remove equal values, if any
        foreach ($array3 as $key => $row) {
            if ($row['db1_value'] == $row['db2_value']) {
                unset($array3[$key]);
            }
        }

        // Prepare data to print table
        $finalArray = [];
        foreach ($array3 as $a3) {
            $finalArray[] = array_values($a3);
        }

        return $finalArray;
    }


    /**
     * @param $value
     * @param int $len
     * @return bool|string
     */
    protected static function setValue($value, $len = Settings::MAX_VALUE_LENGTH)
    {
        return strlen($value) > $len ?
            substr($value, 0, $len) . '...' :
            $value;
    }
}
