<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ImportAndUpdateIndexCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'index:change {model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import and update index';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $command = 'php ../artisan scout:import '.$this->argument('model');
        $res = exec($command);
        if (trim($res) === 'Illuminate\Container\BoundMethod::Illuminate\Container\{closure}()'){
            return false;
        }
        else{
            return true;
        }
    }
}
