<?php

namespace App\Services;

use Laravel\Pennant\Feature;
use App\Models\Image;
use App\Models\Tweet;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TweetService
{
    /**
     * Summary of getTweets
     * @return Collection<int, Tweet>
     */
    public function getTweets(): Collection
    {
        return Tweet::with('images')->orderBy('created_at', 'desc')->get();
    }

    public function checkOwnTweet(int $userId, int $tweetId): bool
    {
        $tweet = Tweet::query()->where('id', $tweetId)->first();
        if (!$tweet) {
            return false;
        }

        return $tweet->user_id === $userId;
    }

    public function countYesterdayTweets(): int
    {
        return Tweet::whereDate('created_at', '>=', Carbon::yesterday()
            ->toDateTimeString())
            ->whereDate('created_at', '<', Carbon::today()->toDateTimeString())
            ->count();
    }

    public function saveTweet(
        int $userId,
        string $content,
        array $images
    ): void {
        DB::transaction(function () use ($userId, $content, $images) {
            $tweet = new Tweet;
            $tweet->user_id = $userId;
            $tweet->content = $content;
            $tweet->save();

            if (!Feature::active('image.upload')) {
                return;
            }
            /** @var UploadedFile $image */
            foreach ($images as $image) {
                Storage::disk('public')->putFile('images', $image);
                $imageModel = new Image;
                $imageModel->name = $image->hashName();
                $imageModel->save();
                $tweet->images()->attach($imageModel->id);
            }
        });
    }

    public function deleteTweet(int $tweetId): void
    {
        DB::transaction(function () use ($tweetId) {
            $tweet = Tweet::query()->where('id', $tweetId)->firstOrFail();
            $tweet->images()->each(function ($image) use ($tweet) {
                $filePath = 'images/' . $image->name;
                $storage = Storage::disk('public');
                if ($storage->exists($filePath)) {
                    $storage->delete($filePath);
                }
                $tweet->images()->detach($image->id);
                $image->delete();
            });

            $tweet->delete();
        });
    }
}
