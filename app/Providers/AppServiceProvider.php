<?php

declare(strict_types=1);

namespace App\Providers;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\RichEditor;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! $this->app->isProduction());

        Blade::component('components.icon', 'icon');

        Carbon::setLocale(config('app.locale', 'id'));
        CarbonImmutable::setLocale(config('app.locale', 'id'));

        CreateRecord::disableCreateAnother();
        CreateAction::configureUsing(fn (CreateAction $action) => $action->createAnother(false));
        RichEditor::configureUsing(fn (RichEditor $editor) => $editor->disableToolbarButtons(['codeBlock']));

        if (config('database.default') === 'sqlite') {
            try {
                DB::statement('PRAGMA foreign_keys = ON;');
                DB::statement('PRAGMA busy_timeout = 5000;');
                $dbPath = (string) config('database.connections.sqlite.database');
                if ($dbPath !== ':memory:' && file_exists($dbPath)) {
                    DB::statement('PRAGMA journal_mode = WAL;');
                }
            } catch (Throwable) {
                // Ignore pragma errors if database file is not initialized yet
            }
        }
    }
}
