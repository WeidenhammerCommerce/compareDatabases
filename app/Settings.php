<?php
/**
 * @category    Hammer
 * @copyright   Copyright (c) 2019 Weidenhammer Systems Corporation
 */

namespace Console\App\Commands;

use Illuminate\Database\Capsule\Manager as Capsule;

class Settings
{
    const DB1_SERVER = 'someServer1',
        DB1_NAME = 'someDatabase1',
        DB1_USERNAME = 'someUser1',
        DB1_PASSWORD = 'somePassword1';

    const DB2_SERVER = 'someServer2',
        DB2_NAME = 'someDatabase2',
        DB2_USERNAME = 'someUser2',
        DB2_PASSWORD = 'somePassword2';

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
