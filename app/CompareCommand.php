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
        // $input->getArgument('username')

        // Start
        Settings::connectToDb();
        $io = new SymfonyStyle($input, $output);
        $io->title('Database comparison');

        // Test connection
        // $this->testConnection($io);

        // Select mode
        $selectedMode = $io->choice('Select mode to compare', [
            'Configurations (core_config_data)',
            'Another table'
        ]);

        // Process selection
        switch ($selectedMode) :
            case 'Configurations (core_config_data)' :

                $dataDb1 = CoreConfigData::on(Settings::DB1_NAME)
                    ->select('config_id', 'scope_id', 'path', 'value')
                    ->get();

                $dataDb2 = CoreConfigData::on(Settings::DB2_NAME)
                    ->select('config_id', 'scope_id', 'path', 'value')
                    ->get();

                // Select direction
                $selectedDirection = $io->choice('Select direction to compare', [
                    Settings::DB1_NAME . ' => ' . Settings::DB2_NAME,
                    Settings::DB2_NAME . ' => ' . Settings::DB1_NAME
                ]);

                // Process data
                if ($selectedDirection == (Settings::DB1_NAME . ' => ' . Settings::DB2_NAME)) {
                    $primaryDb = Settings::DB1_NAME;
                    $secondaryDb = Settings::DB2_NAME;
                    $headers = ['path', 'scope_id', Settings::DB1_NAME, Settings::DB2_NAME];
                    $finalArray = CoreConfigData::compareConfigurations($dataDb1, $dataDb2);
                } else {
                    $primaryDb = Settings::DB2_NAME;
                    $secondaryDb = Settings::DB1_NAME;
                    $headers = ['path', 'scope_id', Settings::DB2_NAME, Settings::DB1_NAME];
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
    }


    /**
     * Test connection by getting Base URL
     */
    private function testConnection($io)
    {
        $config = CoreConfigData::on(Settings::DB1_NAME)
            ->where('path', 'web/unsecure/base_url')
            ->get()
            ->first();

        if (isset($config->path)) {
            $io->success('Connection establish a successfully');
            $io->text($config->path . ' => ' . $config->value);
            $io->newLine();
        } else {
            $io->error('Couldn\'t establish a connection');
        }

        exit;
    }
}