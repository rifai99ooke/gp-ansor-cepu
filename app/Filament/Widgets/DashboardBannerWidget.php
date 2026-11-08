<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\PostType;
use App\Filament\Pages\RegistrationPage;
use App\Filament\Pages\SettingsPage;
use App\Filament\Resources\Bodies\BodyResource;
use App\Filament\Resources\Galleries\GalleryResource;
use App\Filament\Resources\Officers\OfficerResource;
use App\Filament\Resources\Posts\PostResource;
use App\Models\Body;
use App\Models\Gallery;
use App\Models\Officer;
use App\Models\Post;
use App\Models\RegistrationForm;
use Filament\Widgets\Widget;

class DashboardBannerWidget extends Widget
{
    protected static ?int $sort = 0;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.dashboard-banner-widget';

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $beritaCount = Post::query()->published()->ofType(PostType::Berita)->count();
        $draftCount = Post::query()->where('status', '!=', 'published')->count();
        $officersCount = Officer::query()->count();
        $bodiesCount = Body::query()->count();
        $galleriesCount = Gallery::query()->count();

        $activeForm = RegistrationForm::query()->ordered()->first();
        $formOpen = $activeForm !== null && $activeForm->isOpen();
        $formStatusLabel = $activeForm ? $activeForm->status()->label() : 'Ditutup';
        $formTitle = $activeForm?->title ?? 'Formulir Pendaftaran';

        return [
            'beritaCount' => $beritaCount,
            'draftCount' => $draftCount,
            'officersCount' => $officersCount,
            'bodiesCount' => $bodiesCount,
            'galleriesCount' => $galleriesCount,
            'formOpen' => $formOpen,
            'formStatusLabel' => $formStatusLabel,
            'formTitle' => $formTitle,
            'urls' => [
                'posts' => PostResource::getUrl('index'),
                'create_post' => PostResource::getUrl('create'),
                'officers' => OfficerResource::getUrl('index'),
                'bodies' => BodyResource::getUrl('index'),
                'galleries' => GalleryResource::getUrl('index'),
                'create_gallery' => GalleryResource::getUrl('create'),
                'registration' => RegistrationPage::getUrl(),
                'settings' => SettingsPage::getUrl(),
            ],
        ];
    }
}
