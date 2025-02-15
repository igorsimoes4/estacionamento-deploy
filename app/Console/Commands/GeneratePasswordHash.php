<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class GeneratePasswordHash extends Command
{
    protected $signature = 'make:hash {password}';
    protected $description = 'Gera um hash de senha e exibe no terminal';

    public function handle()
    {
        $password = $this->argument('password');
        $hashedPassword = Hash::make($password);

        $this->info("Hash gerado: " . $hashedPassword);
    }
}
