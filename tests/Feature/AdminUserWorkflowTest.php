<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Auth\Login;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\Post;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminUserWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_signs_in_with_username_and_password(): void
    {
        $admin = User::factory()->create(['username' => 'redaksi', 'password' => 'Password-Admin-1934!']);

        Livewire::test(Login::class)->fillForm(['username' => 'redaksi', 'password' => 'Password-Admin-1934!', 'remember' => true])
            ->call('authenticate')->assertHasNoFormErrors()->assertRedirect();

        $this->assertAuthenticatedAs($admin);
        $this->get('/admin')->assertOk();
    }

    #[DataProvider('incorrectCredentials')]
    public function test_rejects_incorrect_credentials(string $username, string $password): void
    {
        User::factory()->create(['username' => 'redaksi', 'email' => 'redaksi@example.test', 'password' => 'Password-Admin-1934!']);

        Livewire::test(Login::class)->fillForm(['username' => $username, 'password' => $password])
            ->call('authenticate')->assertHasFormErrors(['username']);

        $this->assertGuest();
    }

    public function test_requires_both_login_fields(): void
    {
        Livewire::test(Login::class)->fillForm(['username' => '', 'password' => ''])
            ->call('authenticate')->assertHasFormErrors(['username' => 'required', 'password' => 'required']);

        $this->assertGuest();
    }

    public function test_throttles_repeated_failed_sign_in_attempts(): void
    {
        User::factory()->create(['username' => 'redaksi', 'password' => 'Password-Admin-1934!']);
        $page = Livewire::test(Login::class)->fillForm(['username' => 'redaksi', 'password' => 'Wrong-Password!']);
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $page->call('authenticate')->assertHasFormErrors(['username']);
        }

        $page->fillForm(['password' => 'Password-Admin-1934!'])->call('authenticate')->assertNotified();

        $this->assertGuest();
    }

    public function test_signs_out_and_protects_the_dashboard_again(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('filament.admin.auth.logout'))->assertRedirect();

        $this->assertGuest();
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_creates_an_admin_account_with_working_hashed_credentials(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(CreateUser::class)->fillForm(['name' => 'Editor Cepu', 'username' => 'editor_cepu', 'password' => 'New-Password-1934!'])
            ->call('create')->assertHasNoFormErrors()->assertNotified();

        $created = User::query()->where('username', 'editor_cepu')->sole();
        $this->assertSame('Editor Cepu', $created->name);
        $this->assertTrue(Hash::check('New-Password-1934!', $created->password));
        Auth::logout();
        Livewire::test(Login::class)->fillForm(['username' => 'editor_cepu', 'password' => 'New-Password-1934!'])
            ->call('authenticate')->assertHasNoFormErrors();
        $this->assertAuthenticatedAs($created);
    }

    public function test_editing_an_account_with_a_blank_password_preserves_its_password(): void
    {
        $this->actingAs(User::factory()->create());
        $user = User::factory()->create(['username' => 'editor_asli', 'password' => 'Original-Password-1934!']);
        $originalHash = $user->password;

        Livewire::test(EditUser::class, ['record' => $user->id])
            ->fillForm(['name' => 'Nama Baru', 'username' => 'nama_baru', 'password' => ''])
            ->call('save')->assertHasNoFormErrors()->assertNotified();

        $user->refresh();
        $this->assertSame('nama_baru', $user->username);
        $this->assertSame('Nama Baru', $user->name);
        $this->assertSame($originalHash, $user->password);
    }

    public function test_changes_an_account_password_and_rejects_its_old_password(): void
    {
        $this->actingAs(User::factory()->create());
        $user = User::factory()->create(['username' => 'editor_asli', 'password' => 'Original-Password-1934!']);

        Livewire::test(EditUser::class, ['record' => $user->id])->fillForm(['password' => 'Changed-Password-1934!'])
            ->call('save')->assertHasNoFormErrors();

        $this->assertTrue(Hash::check('Changed-Password-1934!', $user->fresh()->password));
        $this->assertFalse(Hash::check('Original-Password-1934!', $user->fresh()->password));
    }

    #[DataProvider('invalidAccountValues')]
    public function test_rejects_invalid_account_data(string $field, string $value, string $rule): void
    {
        $this->actingAs(User::factory()->create(['username' => 'existing_admin']));

        Livewire::test(CreateUser::class)
            ->fillForm(['name' => 'Editor', 'username' => 'new_editor', 'password' => 'New-Password-1934!', $field => $value])
            ->call('create')->assertHasFormErrors([$field => $rule]);

        $this->assertDatabaseCount('users', 1);
    }

    public function test_keeps_own_account_undeletable_from_edit_and_list_actions(): void
    {
        $admin = User::factory()->create();
        User::factory()->create();
        $this->actingAs($admin);

        Livewire::test(EditUser::class, ['record' => $admin->id])->assertActionHidden(DeleteAction::class);
        Livewire::test(ListUsers::class)->assertActionHidden(TestAction::make(DeleteAction::class)->table($admin));

        $this->assertModelExists($admin);
    }

    public function test_deletes_another_admin_without_deleting_their_articles(): void
    {
        $this->actingAs(User::factory()->create());
        $author = User::factory()->create(['name' => 'Penulis Sebelumnya']);
        $post = Post::factory()->for($author, 'author')->create(['cover_image' => null, 'author_name' => $author->name]);

        Livewire::test(EditUser::class, ['record' => $author->id])->callAction(DeleteAction::class)->assertNotified();

        $this->assertModelMissing($author);
        $this->assertModelExists($post);
        $this->assertNull($post->fresh()->author_id);
        $this->get($post->url())->assertOk()->assertSee('Penulis Sebelumnya');
    }

    public function test_reorders_admin_accounts(): void
    {
        $admin = User::factory()->create(['sort_order' => 1]);
        $second = User::factory()->create(['sort_order' => 2]);
        $this->actingAs($admin);

        Livewire::test(ListUsers::class)->call('reorderTable', [(string) $second->id, (string) $admin->id]);

        $this->assertSame(1, $second->fresh()->sort_order);
        $this->assertSame(2, $admin->fresh()->sort_order);
    }

    /** @return array<string, array{string, string}> */
    public static function incorrectCredentials(): array
    {
        return [
            'incorrect password' => ['redaksi', 'Wrong-Password!'],
            'unknown username' => ['unknown', 'Password-Admin-1934!'],
            'email is not a username' => ['redaksi@example.test', 'Password-Admin-1934!'],
        ];
    }

    /** @return array<string, array{string, string, string}> */
    public static function invalidAccountValues(): array
    {
        return [
            'missing name' => ['name', '', 'required'],
            'missing username' => ['username', '', 'required'],
            'duplicate username' => ['username', 'existing_admin', 'unique'],
            'invalid username characters' => ['username', 'invalid username!', 'alpha_dash'],
            'short password' => ['password', '1234567', 'min'],
            'missing password' => ['password', '', 'required'],
        ];
    }
}
