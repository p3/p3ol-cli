<?php

namespace App\Updaters;

use LaravelZero\Framework\Components\Updater\Strategy\StrategyInterface;
use Phar;

class GithubStrategy extends \Humbug\SelfUpdate\Strategy\GithubStrategy implements StrategyInterface
{
    protected function getDownloadUrl(array $package): string
    {
        $downloadUrl = parent::getDownloadUrl($package);

        $downloadUrl = str_replace('releases/download', 'raw', $downloadUrl);

        $os = match (true) {
            str_contains(php_uname(), 'Darwin') => 'macos',
            default => 'linux'
        };

        return $downloadUrl.'/builds/'.$os.'/'.basename(Phar::running());
    }
}
