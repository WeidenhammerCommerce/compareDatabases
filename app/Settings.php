<?php
/**
 * @category    Hammer
 * @copyright   Copyright (c) 2019 Weidenhammer Systems Corporation
 */

namespace Console\App\Commands;

use Illuminate\Database\Capsule\Manager as Capsule;
use Symfony\Component\Yaml\Yaml;

class Settings
{
    const MAX_VALUE_LENGTH = 75;

    public static function connectToDb()
    {
        // Get YAML config
        $yamlConfig = self::getYamlConfig('databases');

        $capsule = new Capsule;

        foreach ($yamlConfig as $database) {
            $capsule->addConnection(
                [
                    'driver'    => 'mysql',
                    'host'      => $database['host'],
                    'port'      => $database['port'],
                    'database'  => $database['database'],
                    'username'  => $database['username'],
                    'password'  => $database['password'],
                    'charset'   => 'utf8',
                    'collation' => 'utf8_unicode_ci',
                    'prefix'    => '',
                ],
                $database['database']
            );
        }

        $capsule->setAsGlobal();
        $capsule->bootEloquent();
    }

    /**
     * @param string $section
     * @return mixed
     */
    public static function getYamlConfig($section = '')
    {
        $config = Yaml::parseFile(__DIR__.'/../config/settings.yaml');

        if (!empty($section) && isset($config[$section])) {
            return $config[$section];
        }

        return $config;
    }
}
