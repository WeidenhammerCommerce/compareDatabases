<?php
/**
 * @category    Hammer
 * @copyright   Copyright (c) 2019 Weidenhammer Systems Corporation
 */

namespace Console\App\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Question\Question;

class CompareCommand extends Command
{
    protected function configure()
    {
        $this->setName('compare')
            ->setDescription('Compare contents of a database table for two Magento instances');
        // ->addArgument('username', InputArgument::REQUIRED, 'Pass some argument.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Database comparison');
        $io->comment('Connecting');

        // Open SSH Tunnels
        try {
            $sshTunnels = new Tunnel();
            /** @var Tunnel $openedTunnels */
            $openedTunnels = $sshTunnels->openTunnels();
        } catch (\Exception $e) {
            $io->error($e->getMessage());

            return;
        }

        // Error occurred when opening SSH tunnels
        if ($openedTunnels->getHasError()) {
            $io->error($openedTunnels->getErrorMessage());

            return;
        }

        // Connect to DB
        Settings::connectToDb();

        // Test connection
        try {
            if (!$this->testConnection($io)) {
                $openedTunnels->closeTunnels();
            }
        } catch (\Exception $e) {
            $openedTunnels->closeTunnels();
            $io->error($e->getMessage());

            return;
        }

        // Show Compare Options
        $this->comparisionTool($openedTunnels, $io, $input, $output);
    }


    /**
     * Comparision tool
     * @param $openedTunnels
     * @param $io
     * @param $input
     * @param $output
     */
    protected function comparisionTool($openedTunnels, $io, $input, $output)
    {
        // DB config
        $dbConfig = Settings::getYamlConfig('databases');

        // Select mode
        $selectedMode = $io->choice('Select tool', [
            'Config by path (core_config_data)',
            'Exit'
        ]);

        // Process selection
        switch ($selectedMode) :
            case 'Config by path (core_config_data)' :

                // Set table headers
                $headers = ['config_id', 'scope', 'scope_id', $dbConfig['db1']['alias'], $dbConfig['db2']['alias']];

                // Select path
                $helper = $this->getHelper('question');
                $question = new Question('Enter path: ', 'web/unsecure/base_url');
                $path = $helper->ask($input, $output, $question);
                if (!$path) return;

                // Get values
                $dataDb1 = $this->getConfigByDb($dbConfig['db1']['database'], $path);
                $dataDb2 = $this->getConfigByDb($dbConfig['db2']['database'], $path);

                // Render table
                if(!count($dataDb1) && !count($dataDb2)) {
                    $io->note('No records found with path "'.$path.'"');
                } else {
                    $configsAll = $this->populateConfigTable($dataDb1, $dataDb2);
                    $this->renderConfigTable($headers, $configsAll, $output, $io);
                }
                break;

            case 'Exit' :
                $this->closeConnection($openedTunnels, $io);
                exit;
        endswitch;

        // Show Compare Options
        $this->comparisionTool($openedTunnels, $io, $input, $output);
    }



    
    /**
     * Test connection by getting Base URL
     */
    private function testConnection($io)
    {
        $dbConfig = Settings::getYamlConfig('databases');

        $config1 = CoreConfigData::on($dbConfig['db1']['database'])
            ->where('path', 'web/unsecure/base_url')
            ->get()
            ->first();

        $config2 = CoreConfigData::on($dbConfig['db2']['database'])
            ->where('path', 'web/unsecure/base_url')
            ->get()
            ->first();

        if (isset($config1->path) && isset($config2->path)) {
            $io->success('Connection established successfully');
            $io->newLine();
        } else {
            $io->error('Couldn\'t establish a connection');

            return false;
        }

        return true;
    }


    /**
     * Close SSH Tunnels
     * @param $openedTunnels
     * @param $io
     */
    protected function closeConnection($openedTunnels, $io)
    {
        $io->newLine();
        try {
            $openedTunnels->closeTunnels();
            $io->comment('Connection closed');
        } catch (\Exception $e) {
            $io->error($e->getMessage());

            return;
        }
    }


    /**
     * Get config value by database/path
     * @param $database
     * @param $path
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    protected function getConfigByDb($database, $path)
    {
        return CoreConfigData::on($database)
            ->select('config_id', 'scope', 'scope_id', 'path', 'value')
            ->where('path', '=', $path)
            ->get();
    }


    /**
     * Populate table with core_config_data values
     * @param $dataDb1
     * @param $dataDb2
     * @return array
     */
    protected function populateConfigTable($dataDb1, $dataDb2)
    {
        $configsAll = [];
        $configs2 = [];

        if(count($dataDb1)) {
            foreach ($dataDb1 as $key => $config1) {
                $configsAll[ $key ][] = $config1->config_id;
                $configsAll[ $key ][] = $config1->scope;
                $configsAll[ $key ][] = $config1->scope_id;
                $configsAll[ $key ][] = $this->formatValue($config1->value);
                foreach ($dataDb2 as $key => $config2) {
                    if ($config1->scope == $config2->scope && $config1->scope_id == $config2->scope_id) {
                        $configs2[] = $config2->config_id;
                        $configsAll[ $key ][] = $this->formatValue($config2->value);
                    }
                }
            }
        }

        if(count($dataDb2)) {
            foreach ($dataDb2 as $key => $config2) {
                if (!in_array($config2->config_id, $configs2)) {
                    $configsAll[ $key ][] = $config2->config_id;
                    $configsAll[ $key ][] = $config2->scope;
                    $configsAll[ $key ][] = $config2->scope_id;
                    $configsAll[ $key ][] = '';
                    $configsAll[ $key ][] = $this->formatValue($config2->value);
                }
            }
        }

        return $configsAll;
    }


    /**
     * Render table with core_config_data values
     * @param $headers
     * @param $configsAll
     * @param $output
     * @param $io
     */
    protected function renderConfigTable($headers, $configsAll, $output, $io)
    {
        $table = new Table($output);
        $table->setHeaders($headers)
            ->setRows($configsAll);
        $table->render();
        $io->newLine();
    }


    /**
     * Format value
     * @param $value
     * @param int $len
     * @return bool|string
     */
    protected static function formatValue($value, $len = Settings::MAX_VALUE_LENGTH)
    {
        return strlen($value) > $len ?
            substr($value, 0, $len) . '...' :
            $value;
    }
}