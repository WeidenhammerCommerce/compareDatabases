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
    const MAX_VALUE_LENGTH = 50;

    public static function connectToDb($project)
    {
        // Get YAML config
        $yamlConfig = self::getYamlConfig($project, 'databases');

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
     * @param string $project
     * @param string $section
     * @return mixed
     * @throws \Exception
     */
    public static function getYamlConfig($project = '', $section = '')
    {
        $yamlPath = __DIR__.'/../config/'.$project.'/settings.yaml';

        if (!file_exists($yamlPath)) {
            throw new \Exception('Configuration file is missing for project: '.$project);
        }

        $config = Yaml::parseFile($yamlPath);

        if (!empty($section) && isset($config[$section])) {
            return $config[$section];
        }

        return $config;
    }
}
