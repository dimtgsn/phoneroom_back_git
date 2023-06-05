<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DeleteIndexCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'index:delete-data {model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete index name';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $command = 'php ../artisan scout:flush '.$this->argument('model');
        $res = exec($command);
        if (trim($res) === 'Illuminate\Container\BoundMethod::Illuminate\Container\{closure}()'){
            return false;
        }
        else{
            return true;
        }
    }
}
