<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Laravel\Pennant\Feature;

#[Signature('feature:toggle {name : フィーチャーフラグの名前} {--on : フラグを有効化します} {--off : フラグを無効化します}')]
#[Description('フィーチャーフラグを切り替えます。')]
class FeatureToggleCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = $this->argument('name');
        $turnOn = $this->option('on');
        $turnOff = $this->option('off');
        if (($turnOn && $turnOff) || (!$turnOn && !$turnOff)) {
            $this->error('オプションには --on か --off のいずれか一方を指定してください。');
            return self::FAILURE;
        }

        $allFeatures = Feature::all();
        $featureExists = in_array($name, $allFeatures, true) || array_key_exists($name, $allFeatures);
        if (!$featureExists) {
            $this->error("Feature [{$name}] は定義されていません。");
            return self::FAILURE;
        }

        $messageSuffix = $turnOn ? 'on' : 'off';

        if ($turnOn) {
            Feature::activate($name);
            Feature::activateForEveryone($name);
        } else {
            Feature::deactivateForEveryone($name);
            Feature::deactivate($name);
        }

        // フラグはキャッシュされるので、ここで揮発させる
        Feature::flushCache();

        $this->info("Feature [{$name}] を {$messageSuffix} に切り替えました。");

        return self::SUCCESS;
    }
}