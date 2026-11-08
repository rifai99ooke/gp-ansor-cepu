<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\PostType;
use App\Http\Requests\PostFilterRequest;
use App\Models\Post;
use App\Models\Ranting;
use App\Models\RegistrationForm;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function index(PostFilterRequest $request): View
    {
        $heroSlides = Post::heroSlides(10);

        $searchQuery = $request->searchQuery();

        $latestPosts = Post::query()
            ->published()
            ->ofType(PostType::Berita)
            ->forCard()
            ->search($searchQuery)
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(6)
            ->withQueryString();

        $popularPosts = Post::query()
            ->published()
            ->ofType(PostType::Berita)
            ->forCard()
            ->popular()
            ->limit(6)
            ->get();

        $activeForm = RegistrationForm::query()->ordered()->first();
        $hasActiveRegistration = $activeForm !== null && $activeForm->isOpen();

        $rantings = Ranting::query()
            ->withCount('members')
            ->ordered()
            ->limit(3)
            ->get();

        return view('home', [
            'heroSlides' => $heroSlides,
            'latestPosts' => $latestPosts,
            'popularPosts' => $popularPosts,
            'searchQuery' => $searchQuery,
            'canonical' => $request->canonicalUrl(),
            'hasActiveRegistration' => $hasActiveRegistration,
            'rantings' => $rantings,
            'rantingCount' => Ranting::query()->count(),
        ]);
    }
}
