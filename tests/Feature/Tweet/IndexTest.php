<?php

namespace Tests\Feature\Tweet;

use App\Models\Image;
use App\Models\Tweet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    private const FLAG = 'image.upload';

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_グローバルフラグが有効ならゲストも画像を閲覧できる(): void
    {
        $this->prepareFlags();
        $image = $this->prepareTweetWithImage();
        Feature::activate(self::FLAG);
        $response = $this->get(route('tweet.index'));

        $response->assertStatus(200)
            ->assertSee('storage/images/' . $image->name);
    }

    public function test_グローバルフラグが無効ならゲストには画像が表示されない(): void
    {
        $this->prepareFlags();
        $image = $this->prepareTweetWithImage();
        Feature::deactivate(self::FLAG);
        $response = $this->get(route('tweet.index'));
        $response->assertStatus(200)
            ->assertDontSee('storage/images/' . $image->name);
    }

    private function prepareTweetWithImage(): Image
    {
        $user = User::factory()->create();
        $tweet = Tweet::factory()->create(['user_id' => $user->id]);
        $image = Image::factory()->create();
        $tweet->images()->attach($image->id);

        return $image;
    }

    private function prepareFlags(): void
    {
        Feature::purge(self::FLAG);
        Feature::flushCache();
    }
}