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
}
