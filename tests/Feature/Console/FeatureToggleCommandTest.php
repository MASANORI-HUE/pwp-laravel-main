<?php

namespace Tests\Feature\Console;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Tests\TestCase;

class FeatureToggleCommandTest extends TestCase
{
    use RefreshDatabase;

    private const FLAG = 'image.upload';

    public function test_全ユーザーを対象に有効化と無効化が行える(): void
    {
        Feature::purge(self::FLAG);

        $this->artisan('feature:toggle ' . self::FLAG . ' --on')
            ->expectsOutput('Feature [' . self::FLAG . '] を on に切り替えました。')
            ->assertExitCode(0);

        $this->assertTrue(Feature::active(self::FLAG));

        $this->artisan('feature:toggle ' . self::FLAG . ' --off')
            ->expectsOutput('Feature [' . self::FLAG . '] を off に切り替えました。')
            ->assertExitCode(0);

        $this->assertFalse(Feature::active(self::FLAG));
    }

    public function test_onとoffを同時に指定するとエラーになる(): void
    {
        $this->artisan('feature:toggle ' . self::FLAG . ' --on --off')
            ->expectsOutput('オプションには --on か --off のいずれか一方を指定してください。')
            ->assertExitCode(1);
    }

    public function test_onかoffを指定しない場合エラーになる(): void
    {
        $this->artisan('feature:toggle ' . self::FLAG)
            ->expectsOutput('オプションには --on か --off のいずれか一方を指定してください。')
            ->assertExitCode(1);
    }
}