<?php
/**
 * @category    Hammer
 * @copyright   Copyright (c) 2019 Weidenhammer Systems Corporation
 */

namespace Console\App\Commands;

use Console\App\Commands\Settings;
use Console\App\Commands\CoreConfigData;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
// use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Helper\Table;

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

        // Select mode
        $selectedMode = $io->choice('Select mode to compare', [
            'Configurations (core_config_data)',
            'Another table'
        ]);

        // DB config
        $dbConfig = Settings::getYamlConfig('databases');

        // Process selection
        switch ($selectedMode) :
            case 'Configurations (core_config_data)' :

                try {
                    $dataDb1 = CoreConfigData::on($dbConfig['db1']['database'])
                        ->select('config_id', 'scope_id', 'path', 'value')
                        ->get();

                    $dataDb2 = CoreConfigData::on($dbConfig['db2']['database'])
                        ->select('config_id', 'scope_id', 'path', 'value')
                        ->get();
                } catch (\Exception $e) {
                    $io->error($e->getMessage());
                    return;
                }

                // Select direction
                $selectedDirection = $io->choice('Select direction to compare', [
                    $dbConfig['db1']['database'] . ' => ' . $dbConfig['db2']['database'],
                    $dbConfig['db2']['database'] . ' => ' . $dbConfig['db1']['database']
                ]);

                // Process data
                if ($selectedDirection == ($dbConfig['db1']['database'] . ' => ' . $dbConfig['db2']['database'])) {
                    $primaryDb = $dbConfig['db1']['database'];
                    $secondaryDb = $dbConfig['db2']['database'];
                    $headers = ['path', 'scope_id', $dbConfig['db1']['database'], $dbConfig['db2']['database']];
                    $finalArray = CoreConfigData::compareConfigurations($dataDb1, $dataDb2);
                } else {
                    $primaryDb = $dbConfig['db2']['database'];
                    $secondaryDb = $dbConfig['db1']['database'];
                    $headers = ['path', 'scope_id', $dbConfig['db2']['database'], $dbConfig['db1']['database']];
                    $finalArray = CoreConfigData::compareConfigurations($dataDb2, $dataDb1);
                }

                $io->listing([
                    'Showing values that are different',
                    'Showing values that are present in [' . $primaryDb . '] but not in [' . $secondaryDb . ']',
                    'Values are truncated if its length is greater than ' . Settings::MAX_VALUE_LENGTH
                ]);

                // Show table
                $table = new Table($output);
                $table->setHeaders($headers)
                    ->setRows($finalArray);
                $table->render();
                break;

            case 'Another table' :
                $io->text('TO-DO');
                $io->newLine();
                break;
        endswitch;

        // Close SSH Tunnel
        try {
            $openedTunnels->closeTunnels();
        } catch (\Exception $e) {
            $io->error($e->getMessage());
            return;
        }
    }


    /**
     * Test connection by getting Base URL
     */
    private function testConnection($io)
    {
        $dbConfig = Settings::getYamlConfig('databases');

        $config = CoreConfigData::on($dbConfig['db1']['database'])
            ->where('path', 'web/unsecure/base_url')
            ->get()
            ->first();

        if (isset($config->path)) {
            $io->success('Connection established successfully');
            $io->text($config->path . ' => ' . $config->value);
            $io->newLine();
        } else {
            $io->error('Couldn\'t establish a connection');
            return false;
        }

        return true;
    }
}