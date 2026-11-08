<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Ranting;
use Illuminate\Contracts\View\View;

class RantingController extends Controller
{
    public function index(): View
    {
        $rantings = Ranting::query()
            ->withCount('members')
            ->ordered()
            ->get();

        return view('rantings.index', [
            'rantings' => $rantings,
            'rantingCount' => $rantings->count(),
        ]);
    }

    public function show(string $slug): View
    {
        $ranting = Ranting::query()
            ->with(['members' => fn ($q) => $q->ordered()])
            ->where('slug', $slug)
            ->firstOrFail();

        $otherRantings = Ranting::query()
            ->where('id', '!=', $ranting->id)
            ->ordered()
            ->get();

        return view('rantings.show', [
            'ranting' => $ranting,
            'otherRantings' => $otherRantings,
        ]);
    }
}
