<?php

namespace App\Commands;

use Illuminate\Support\Facades\Process;
use LaravelZero\Framework\Commands\Command;

class Package extends Command
{
    protected $signature = 'package';

    protected $description = 'Bundle PHP with release for each operating system.';

    protected array $packagers = [
        'macos' => '/packagers/macos-micro-cli.sfx',
        'linux' => '/packagers/linux-micro-cli.sfx',
    ];

    public function handle()
    {
        $build = base_path().'/builds/p3ol';

        collect($this->packagers)->each(function (string $packager, string $os) use ($build) {
            Process::run('cat '.base_path().$packager.' '.$build.' > '.base_path().'/builds/'.$os.'/p3ol');
        });
    }
}
