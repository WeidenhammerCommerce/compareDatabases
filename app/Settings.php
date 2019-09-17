<?php
/**
 * @category    Hammer
 * @copyright   Copyright (c) 2019 Weidenhammer Systems Corporation
 */

namespace Console\App\Commands;

use Illuminate\Database\Capsule\Manager as Capsule;

class Settings
{
    const DB1_SERVER = 'localhost',
        DB1_NAME = 'magento22',
        DB1_USERNAME = 'magento',
        DB1_PASSWORD = 'xebucow512';

    const DB2_SERVER = 'localhost',
        DB2_NAME = 'magento23',
        DB2_USERNAME = 'magento',
        DB2_PASSWORD = 'xebucow512';

    const MAX_VALUE_LENGTH = 75;

    public static function connectToDb()
    {
        $capsule = new Capsule;

        $capsule->addConnection(
            [
                'driver'    => 'mysql',
                'host'      => self::DB1_SERVER,
                'database'  => self::DB1_NAME,
                'username'  => self::DB1_USERNAME,
                'password'  => self::DB1_PASSWORD,
                'charset'   => 'utf8',
                'collation' => 'utf8_unicode_ci',
                'prefix'    => '',
            ],
            self::DB1_NAME
        );

        $capsule->addConnection(
            [
                'driver'    => 'mysql',
                'host'      => self::DB2_SERVER,
                'database'  => self::DB2_NAME,
                'username'  => self::DB2_USERNAME,
                'password'  => self::DB2_PASSWORD,
                'charset'   => 'utf8',
                'collation' => 'utf8_unicode_ci',
                'prefix'    => '',
            ],
            self::DB2_NAME
        );

        $capsule->setAsGlobal();
        $capsule->bootEloquent();
    }
}
