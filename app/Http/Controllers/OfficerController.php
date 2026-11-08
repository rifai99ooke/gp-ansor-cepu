<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\PostFilterRequest;
use App\Models\Officer;
use Illuminate\Contracts\View\View;

class OfficerController extends Controller
{
    public function index(PostFilterRequest $request): View
    {
        $searchQuery = $request->searchQuery();

        $officers = Officer::query()
            ->ordered()
            ->search($searchQuery)
            ->get();

        return view('officers.index', [
            'officers' => $officers,
            'searchQuery' => $searchQuery,
        ]);
    }

    public function show(string $slug): View
    {
        $officer = Officer::query()
            ->where('slug', $slug)
            ->firstOrFail();

        $otherOfficers = Officer::query()
            ->where('id', '!=', $officer->id)
            ->ordered()
            ->get();

        return view('officers.show', [
            'officer' => $officer,
            'otherOfficers' => $otherOfficers,
        ]);
    }
}
