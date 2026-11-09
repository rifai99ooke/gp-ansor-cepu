<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\Rantings\Pages\CreateRanting;
use App\Filament\Resources\Rantings\Pages\EditRanting;
use App\Models\Ranting;
use App\Models\RantingMember;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AdminRantingMembersTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_ranting_members_with_photos_in_the_submitted_order(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());

        Livewire::test(CreateRanting::class)->fillForm([
            'name' => 'Ranting Cepu',
            'members' => [
                ['name' => 'Anggota Pertama', 'photo' => [UploadedFile::fake()->createWithContent('anggota.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a5e8AAAAASUVORK5CYII='))]],
                ['name' => 'Anggota Kedua'],
            ],
        ])->call('create')->assertHasNoFormErrors()->assertNotified();

        $ranting = Ranting::query()->sole();
        $members = $ranting->members;
        $this->assertSame(['Anggota Pertama', 'Anggota Kedua'], $members->pluck('name')->all());
        $this->assertSame([1, 2], $members->pluck('sort_order')->all());
        Storage::disk('public')->assertExists($members->first()->photo);
        $this->get('/ranting/'.$ranting->slug)->assertOk()->assertSeeInOrder(['Anggota Pertama', 'Anggota Kedua']);
    }

    public function test_edits_reorders_adds_and_removes_members_without_affecting_another_ranting(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());
        $ranting = Ranting::factory()->create();
        $first = RantingMember::factory()->for($ranting)->create(['name' => 'Anggota Pertama', 'sort_order' => 1]);
        $second = RantingMember::factory()->for($ranting)->create(['name' => 'Anggota Kedua', 'sort_order' => 2]);
        $removed = RantingMember::factory()->for($ranting)->create(['sort_order' => 3]);
        $otherRanting = Ranting::factory()->create();
        $otherMember = RantingMember::factory()->for($otherRanting)->create(['name' => 'Anggota Ranting Lain']);

        Livewire::test(EditRanting::class, ['record' => $ranting->id])->set('data.members', [
            'record-'.$second->id => ['name' => 'Anggota Kedua Diperbarui', 'photo' => []],
            'record-'.$first->id => ['name' => 'Anggota Pertama', 'photo' => []],
            'new-member' => ['name' => 'Anggota Baru', 'photo' => []],
        ])->call('save')->assertHasNoFormErrors();

        $this->assertSame(['Anggota Kedua Diperbarui', 'Anggota Pertama', 'Anggota Baru'], $ranting->members()->pluck('name')->all());
        $this->assertSame([1, 2, 3], $ranting->members()->pluck('sort_order')->all());
        $this->assertModelMissing($removed);
        $this->assertSame('Anggota Ranting Lain', $otherMember->fresh()->name);
        $this->get('/ranting/'.$ranting->slug)->assertOk()->assertSeeInOrder(['Anggota Kedua Diperbarui', 'Anggota Pertama', 'Anggota Baru']);
    }

    public function test_requires_a_name_for_every_member_before_saving(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(CreateRanting::class)->fillForm(['name' => 'Ranting Cepu', 'members' => ['incomplete' => ['name' => '']]])
            ->call('create')->assertHasFormErrors(['members.incomplete.name' => 'required']);

        $this->assertDatabaseCount('rantings', 0);
        $this->assertDatabaseCount('ranting_members', 0);
    }

    public function test_add_and_delete_member_buttons_update_the_repeater_before_saving(): void
    {
        $this->actingAs(User::factory()->create());
        $ranting = Ranting::factory()->create();
        $member = RantingMember::factory()->for($ranting)->create();
        $page = Livewire::test(EditRanting::class, ['record' => $ranting->id]);

        $page->callAction(TestAction::make('add')->schemaComponent('members'));

        $this->assertCount(2, $page->get('data.members'));
        $page->callAction(TestAction::make('delete')->schemaComponent('members'), arguments: ['item' => 'record-'.$member->id]);
        $this->assertCount(1, $page->get('data.members'));
        $this->assertModelExists($member);
    }
}
