<?php

namespace Tests\Feature\Tweet;

use App\Models\Tweet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Pennant\Feature;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use RefreshDatabase;

    private const FLAG = 'image.upload';

    public function test_画像アップロードが有効なユーザーはファイルを投稿できる(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        Feature::activate(self::FLAG);

        $response = $this->actingAs($user)->post(route('tweet.create'), [
            'tweet' => '画像付き投稿です',
            'images' => [
                UploadedFile::fake()->image('example.jpg'),
            ],
        ]);

        $response->assertRedirect(route('tweet.index'));

        $tweet = Tweet::with('images')->latest()->first();

        $this->assertNotNull($tweet);
        $this->assertCount(1, $tweet->images);

        $imageName = $tweet->images->first()->name;

        Storage::disk('public')->assertExists('images/' . $imageName);
    }

    public function test_フラグが無効な場合は画像が保存されない(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        Feature::deactivate(self::FLAG);
        $response = $this->actingAs($user)->post(route('tweet.create'), [
            'tweet' => 'テキストのみの投稿です',
            'images' => [
                UploadedFile::fake()->image('should-not-save.jpg'),
            ],
        ]);

        $response->assertRedirect(route('tweet.index'));

        $tweet = Tweet::with('images')->latest()->first();

        $this->assertNotNull($tweet);
        $this->assertCount(0, $tweet->images);

        $this->assertEmpty(Storage::disk('public')->allFiles());
    }
}