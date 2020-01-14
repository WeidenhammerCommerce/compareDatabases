<?php
/**
 * @category    Hammer
 * @copyright   Copyright (c) 2019 Weidenhammer Systems Corporation
 */

namespace Console\App\Commands;

use Illuminate\Database\Capsule\Manager as Capsule;

class Settings
{
    const SSH1_FORWARD_HOST_REMOTE = 'someHostRemote',
        SSH1_FORWARD_PORT_LOCAL = 'somePortLocal',
        SSH1_FORWARD_PORT_REMOTE = 'somePortRemote',
        SSH1_PRIVATE_KEY_FILENAME = 'somePathPrivateKey',
        SSH1_HOSTNAME = 'someHostName',
        SSH1_PORT = 'somePort',
        SSH1_USERNAME = 'someUsername';

    const SSH2_FORWARD_HOST_REMOTE = 'someHostRemote',
        SSH2_FORWARD_PORT_LOCAL = 'somePortLocal',
        SSH2_FORWARD_PORT_REMOTE = 'somePortRemote',
        SSH2_PRIVATE_KEY_FILENAME = 'somePathPrivateKey',
        SSH2_HOSTNAME = 'someHostName',
        SSH2_PORT = 'somePort',
        SSH2_USERNAME = 'someUsername';

    const DB1_SERVER = 'someServer1',
        DB1_PORT = 'somePort',
        DB1_NAME = 'someDatabase1',
        DB1_USERNAME = 'someUser1',
        DB1_PASSWORD = 'somePassword1';

    const DB2_SERVER = 'someServer2',
        DB2_PORT = 'somePort',
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
                'port'      => self::DB1_PORT,
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
                'port'      => self::DB2_PORT,
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
