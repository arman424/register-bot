<?php

namespace App\Console\Commands;

use App\Contracts\ModulesContract;
use Illuminate\Console\Command;

class RegisterBot extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:register-bot';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'The command passes 3 step registration automatically';

    /**
     * Execute the console command.
     */
    public function handle(ModulesContract $blackScaleModule): void
    {
        $this->info($blackScaleModule->register());
    }
}
