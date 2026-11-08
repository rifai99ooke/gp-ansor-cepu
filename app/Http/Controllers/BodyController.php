<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\PostFilterRequest;
use App\Models\Body;
use Illuminate\Contracts\View\View;

class BodyController extends Controller
{
    public function index(PostFilterRequest $request): View
    {
        $searchQuery = $request->searchQuery();

        $bodies = Body::query()
            ->ordered()
            ->search($searchQuery)
            ->get();

        return view('bodies.index', [
            'bodies' => $bodies,
            'searchQuery' => $searchQuery,
        ]);
    }

    public function show(string $slug): View
    {
        $body = Body::query()
            ->where('slug', $slug)
            ->firstOrFail();

        $otherBodies = Body::query()
            ->where('id', '!=', $body->id)
            ->ordered()
            ->get();

        return view('bodies.show', [
            'body' => $body,
            'otherBodies' => $otherBodies,
        ]);
    }
}
