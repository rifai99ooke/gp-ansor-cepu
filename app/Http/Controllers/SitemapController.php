<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\PostType;
use App\Models\Body;
use App\Models\Officer;
use App\Models\Post;
use App\Models\Ranting;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $posts = Post::query()
            ->select(['id', 'slug', 'published_at', 'updated_at'])
            ->published()
            ->ofType(PostType::Berita)
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->get();
        $officers = Officer::query()
            ->select(['id', 'slug', 'updated_at', 'sort_order'])
            ->ordered()
            ->get();
        $bodies = Body::query()
            ->select(['id', 'slug', 'updated_at', 'sort_order'])
            ->ordered()
            ->get();
        $rantings = Ranting::query()
            ->select(['id', 'slug', 'updated_at', 'sort_order'])
            ->ordered()
            ->get();

        $content = view('sitemap', [
            'posts' => $posts,
            'officers' => $officers,
            'bodies' => $bodies,
            'rantings' => $rantings,
        ])->render();

        return response($content, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }
}
