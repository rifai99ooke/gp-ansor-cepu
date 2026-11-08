<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\PostFilterRequest;
use App\Models\Gallery;
use Illuminate\Contracts\View\View;

class GalleryController extends Controller
{
    public function index(PostFilterRequest $request): View
    {
        $searchQuery = $request->searchQuery();

        $galleries = Gallery::query()
            ->search($searchQuery)
            ->ordered()
            ->paginate(9)
            ->withQueryString();

        return view('galleries.index', [
            'galleries' => $galleries,
            'searchQuery' => $searchQuery,
            'canonical' => $request->canonicalUrl(),
        ]);
    }
}
