<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\PostStatus;
use App\Models\Body;
use App\Models\Gallery;
use App\Models\Officer;
use App\Models\Page;
use App\Models\Post;
use App\Models\RegistrationForm;
use App\Models\Setting;
use App\Models\User;
use App\Services\PlaceholderAssetGenerator;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'app.admin_name' => 'Bootstrap Administrator',
            'app.admin_username' => 'bootstrap_admin',
            'app.admin_email' => 'bootstrap-admin@example.test',
        ]);
    }

    public function test_creates_admin_with_an_explicit_strong_password(): void
    {
        $this->fakeSeedAssets();
        config(['app.admin_password' => 'Testing-Only-Initial-Secret-42!']);

        $this->seed(DatabaseSeeder::class);

        $admin = User::query()->where('email', 'bootstrap-admin@example.test')->firstOrFail();
        $this->assertDatabaseCount('users', 1);
        $this->assertSame('bootstrap_admin', $admin->username);
        $this->assertTrue(Hash::check('Testing-Only-Initial-Secret-42!', $admin->password));
    }

    #[DataProvider('invalidInitialPasswords')]
    public function test_rejects_missing_or_weak_initial_passwords_without_seeding_data(?string $password): void
    {
        $this->fakeSeedAssets();
        config(['app.admin_password' => $password]);

        try {
            $this->seed(DatabaseSeeder::class);
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('ADMIN_PASSWORD', $exception->errors());
            $this->assertDatabaseCount('users', 0);
            $this->assertDatabaseCount('settings', 0);

            return;
        }

        $this->fail('Seeding must reject a missing or weak ADMIN_PASSWORD.');
    }

    /**
     * @return array<string, array{0: ?string}>
     */
    public static function invalidInitialPasswords(): array
    {
        return [
            'missing' => [null],
            'empty' => [''],
            'old default' => ['password'],
            'too short' => ['Short1!'],
            'missing uppercase' => ['lowercase123!'],
            'missing lowercase' => ['UPPERCASE123!'],
            'missing numbers' => ['MissingNumbers!!'],
            'missing symbols' => ['MissingSymbols123'],
        ];
    }

    #[DataProvider('changedBootstrapPasswords')]
    public function test_reseeding_preserves_existing_admin_credentials(?string $password): void
    {
        $this->fakeSeedAssets();
        $admin = User::factory()->create([
            'name' => 'Updated Administrator',
            'username' => 'updated_admin',
            'email' => 'bootstrap-admin@example.test',
            'password' => 'Testing-Only-Updated-Secret-99!',
        ]);
        $originalAttributes = $admin->fresh()->getAttributes();
        config(['app.admin_password' => $password]);

        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('users', 1);
        $this->assertSame($originalAttributes, $admin->fresh()->getAttributes());
    }

    /**
     * @return array<string, array{0: ?string}>
     */
    public static function changedBootstrapPasswords(): array
    {
        return [
            'removed' => [null],
            'old default' => ['password'],
            'different strong password' => ['Testing-Only-Replacement-Secret-17!'],
        ];
    }

    #[DataProvider('editedContent')]
    public function test_reseeding_preserves_edited_content(string $modelClass, array $changes): void
    {
        $this->fakeSeedAssets();
        $this->seed(DatabaseSeeder::class);
        $record = $modelClass::query()->firstOrFail();
        $record->update($changes);
        $originalAttributes = $record->fresh()->getAttributes();
        $originalCount = $modelClass::query()->count();

        $this->seed(DatabaseSeeder::class);

        $this->assertSame($originalAttributes, $record->fresh()->getAttributes());
        $this->assertDatabaseCount($record->getTable(), $originalCount);
    }

    /**
     * @return array<string, array{0: class-string<Model>, 1: array<string, mixed>}>
     */
    public static function editedContent(): array
    {
        return [
            'settings' => [Setting::class, ['value' => 'Pengaturan organisasi terbaru']],
            'posts' => [Post::class, ['title' => 'Berita Diperbarui', 'content' => '<p>Isi terbaru.</p>', 'status' => PostStatus::Draft, 'views_count' => 9876]],
            'officers' => [Officer::class, ['name' => 'Pengurus Baru', 'position' => 'Jabatan Baru', 'sort_order' => 20]],
            'bodies' => [Body::class, ['name' => 'Lembaga Baru', 'description' => '<p>Deskripsi terbaru.</p>']],
            'pages' => [Page::class, ['title' => 'Halaman Diperbarui', 'content' => '<p>Konten terbaru.</p>']],
            'registration' => [RegistrationForm::class, ['slug' => 'kampanye-baru', 'form_url' => 'https://docs.google.com/forms/d/e/UpdatedCampaign/viewform', 'is_active' => false]],
            'galleries' => [Gallery::class, ['title' => 'Dokumentasi Baru', 'description' => 'Keterangan terbaru', 'sort_order' => 20]],
        ];
    }

    private function fakeSeedAssets(): void
    {
        Http::preventStrayRequests();
        Storage::fake('public');
        $this->mock(PlaceholderAssetGenerator::class, function (MockInterface $mock): void {
            $mock->shouldReceive('generateAll')->andReturnNull();
        });
    }
}
