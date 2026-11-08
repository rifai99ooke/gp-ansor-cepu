<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\Rantings\Pages\ListRantings;
use App\Models\Ranting;
use App\Models\User;
use DOMDocument;
use DOMXPath;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RantingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_loads_three_ordered_rantings_while_counting_all_of_them(): void
    {
        $first = Ranting::factory()->hasMembers(2)->create(['name' => 'Ranting Pertama', 'sort_order' => 1]);
        $second = Ranting::factory()->create(['name' => 'Ranting Kedua', 'sort_order' => 2]);
        $third = Ranting::factory()->create(['name' => 'Ranting Ketiga', 'sort_order' => 3]);
        Ranting::factory()->count(2)->create(['sort_order' => 4]);

        $this->get('/')->assertOk()
            ->assertViewHas('rantingCount', 5)
            ->assertViewHas('rantings', fn (Collection $rantings): bool => $rantings->modelKeys() === [$first->id, $second->id, $third->id]
                && $rantings->first()->members_count === 2);
    }

    public function test_admin_can_reorder_rantings_with_the_visible_drag_handles(): void
    {
        $this->actingAs(User::factory()->create());
        $first = Ranting::factory()->create(['name' => 'Ranting Pertama', 'sort_order' => 1]);
        $second = Ranting::factory()->create(['name' => 'Ranting Kedua', 'sort_order' => 2]);
        $page = Livewire::test(ListRantings::class);
        $document = new DOMDocument;
        $document->loadHTML($page->html(), LIBXML_NOERROR | LIBXML_NOWARNING);
        $handles = (new DOMXPath($document))->query('//*[@data-sortable-key]');
        $this->assertSame(2, $handles->length);

        $page->call('reorderTable', [(string) $second->id, (string) $first->id]);

        $this->assertSame(1, $second->fresh()->sort_order);
        $this->assertSame(2, $first->fresh()->sort_order);
        $this->get('/ranting')->assertOk()->assertSeeInOrder(['Ranting Kedua', 'Ranting Pertama']);
    }
}
