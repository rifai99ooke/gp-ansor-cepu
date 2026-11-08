<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Contracts\View\View;

class PageController extends Controller
{
    public function visiMisi(): View
    {
        return $this->renderPage('visi-misi');
    }

    public function program(): View
    {
        return $this->renderPage('program');
    }

    public function dokumen(): View
    {
        return $this->renderPage('dokumen');
    }

    private function renderPage(string $slug): View
    {
        $page = Page::query()
            ->where('slug', $slug)
            ->firstOrFail();

        return view('pages.show', [
            'page' => $page,
        ]);
    }
}
