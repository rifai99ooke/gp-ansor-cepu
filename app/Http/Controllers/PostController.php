<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\PostType;
use App\Http\Requests\PostFilterRequest;
use App\Models\Post;
use Illuminate\Contracts\View\View;

class PostController extends Controller
{
    public function beritaIndex(PostFilterRequest $request): View
    {
        $heroSlides = Post::heroSlides(10);

        $popularPosts = Post::query()
            ->published()
            ->ofType(PostType::Berita)
            ->forCard()
            ->popular()
            ->limit(6)
            ->get();

        $searchQuery = $request->searchQuery();

        $latestPosts = Post::query()
            ->published()
            ->ofType(PostType::Berita)
            ->forCard()
            ->search($searchQuery)
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(9)
            ->withQueryString();

        return view('posts.berita-index', [
            'heroSlides' => $heroSlides,
            'popularPosts' => $popularPosts,
            'latestPosts' => $latestPosts,
            'searchQuery' => $searchQuery,
        ]);
    }

    public function beritaShow(string $slug): View
    {
        $post = Post::query()
            ->with('author')
            ->published()
            ->ofType(PostType::Berita)
            ->where('slug', $slug)
            ->firstOrFail();

        $this->recordUniqueSessionView($post);

        $relatedPosts = Post::query()
            ->published()
            ->ofType(PostType::Berita)
            ->forCard()
            ->where('id', '!=', $post->id)
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit(3)
            ->get();

        return view('posts.show', [
            'post' => $post,
            'relatedPosts' => $relatedPosts,
        ]);
    }

    private function recordUniqueSessionView(Post $post): void
    {
        $sessionKey = "viewed_post_{$post->id}";
        if (! session()->has($sessionKey)) {
            $post->increment('views_count');
            session()->put($sessionKey, true);
        }
    }
}
