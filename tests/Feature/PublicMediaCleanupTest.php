<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Pages\RegistrationPage;
use App\Models\Body;
use App\Models\Gallery;
use App\Models\Officer;
use App\Models\Page;
use App\Models\Post;
use App\Models\Ranting;
use App\Models\RantingMember;
use App\Models\RegistrationForm;
use App\Models\Setting;
use App\Models\User;
use App\Services\ImageService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PublicMediaCleanupTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('mediaAttributes')]
    public function test_replacing_media_removes_the_old_image_family(string $modelClass, string $attribute, string $directory): void
    {
        $this->fakeMedia();
        $oldFiles = $this->storeFamily($directory.'/old.png');
        $newFiles = $this->storeFamily($directory.'/new.png');
        $record = $this->createMediaRecord($modelClass, $attribute, $oldFiles[0]);

        $record->update([$attribute => $newFiles[0]]);

        Storage::disk('public')->assertMissing($oldFiles);
        Storage::disk('public')->assertExists($newFiles);
        $this->assertSame($newFiles[0], $record->fresh()->getAttribute($attribute));
    }

    #[DataProvider('mediaAttributes')]
    public function test_deleting_a_record_removes_its_image_family(string $modelClass, string $attribute, string $directory): void
    {
        $this->fakeMedia();
        $files = $this->storeFamily($directory.'/deleted.png');
        $record = $this->createMediaRecord($modelClass, $attribute, $files[0]);

        $record->delete();

        $this->assertModelMissing($record);
        Storage::disk('public')->assertMissing($files);
    }

    #[DataProvider('sharedReferences')]
    public function test_preserves_image_families_still_referenced_elsewhere(string $modelClass, string $attribute, string $reference): void
    {
        $this->fakeMedia();
        $files = $this->storeFamily('posts/shared.png');
        $post = Post::factory()->create(['cover_image' => $files[0]]);
        $this->createMediaRecord($modelClass, $attribute, $reference);

        $post->delete();

        Storage::disk('public')->assertExists($files);
    }

    public function test_deleting_a_ranting_cleans_up_cascaded_member_photos(): void
    {
        $this->fakeMedia();
        $coverFiles = $this->storeFamily('rantings/cover.png');
        $photoFiles = $this->storeFamily('ranting-members/member.png');
        $ranting = Ranting::factory()->create(['cover' => $coverFiles[0]]);
        $member = RantingMember::factory()->for($ranting)->create(['photo' => $photoFiles[0]]);

        $ranting->delete();

        $this->assertModelMissing($member);
        Storage::disk('public')->assertMissing([...$coverFiles, ...$photoFiles]);
    }

    public function test_saving_the_registration_page_cleans_up_redundant_form_media(): void
    {
        $this->fakeMedia();
        $this->actingAs(User::factory()->create());
        $activeFiles = $this->storeFamily('forms/active.png');
        $redundantFiles = $this->storeFamily('forms/redundant.png');
        $active = RegistrationForm::factory()->create(['cover_image' => $activeFiles[0], 'sort_order' => 1]);
        $redundant = RegistrationForm::factory()->create(['cover_image' => $redundantFiles[0], 'sort_order' => 2]);

        Livewire::test(RegistrationPage::class)->call('save')->assertHasNoFormErrors();

        $this->assertDatabaseCount('registration_forms', 1);
        $this->assertModelMissing($redundant);
        $this->assertSame($activeFiles[0], $active->fresh()->cover_image);
        Storage::disk('public')->assertExists($activeFiles);
        Storage::disk('public')->assertMissing($redundantFiles);
    }

    public function test_preserves_branding_shared_by_logo_and_favicon(): void
    {
        $this->fakeMedia();
        $files = $this->storeFamily('branding/shared.png');
        $logo = Setting::setValue('site_logo', $files[0]);
        Setting::setValue('site_favicon', $files[0]);

        $logo->delete();

        Storage::disk('public')->assertExists($files);
    }

    public function test_waits_for_transaction_commit_before_removing_old_media(): void
    {
        $this->fakeMedia();
        $files = $this->storeFamily('posts/committed.png');
        $post = Post::factory()->create(['cover_image' => $files[0]]);
        DB::beginTransaction();

        try {
            $post->update(['cover_image' => null]);

            Storage::disk('public')->assertExists($files);
        } finally {
            DB::commit();
        }
        Storage::disk('public')->assertMissing($files);
        $this->assertNull($post->fresh()->cover_image);
    }

    public function test_rollback_preserves_media_and_the_original_database_reference(): void
    {
        $this->fakeMedia();
        $files = $this->storeFamily('posts/rolled-back.png');
        $post = Post::factory()->create(['cover_image' => $files[0]]);
        DB::beginTransaction();

        $post->delete();
        DB::rollBack();

        Storage::disk('public')->assertExists($files);
        $this->assertSame($files[0], Post::query()->findOrFail($post->id)->cover_image);
    }

    public function test_preserves_a_media_reference_restored_before_commit(): void
    {
        $this->fakeMedia();
        $originalFiles = $this->storeFamily('posts/original.png');
        $interimFiles = $this->storeFamily('posts/interim.png');
        $post = Post::factory()->create(['cover_image' => $originalFiles[0]]);

        DB::transaction(function () use ($post, $originalFiles, $interimFiles): void {
            $post->update(['cover_image' => $interimFiles[0]]);
            $post->update(['cover_image' => $originalFiles[0]]);
        });

        Storage::disk('public')->assertExists($originalFiles);
        Storage::disk('public')->assertMissing($interimFiles);
    }

    #[DataProvider('unsafePaths')]
    public function test_does_not_delete_files_from_unsafe_stored_paths(string $path): void
    {
        $this->fakeMedia();
        $files = $this->storeFamily('posts/protected.png');
        $path = str_replace('{root}', Storage::disk('public')->path(''), $path);
        $post = Post::factory()->create(['cover_image' => null]);
        DB::table('posts')->where('id', $post->id)->update(['cover_image' => $path]);

        $post->refresh()->delete();

        Storage::disk('public')->assertExists($files);
    }

    public function test_does_not_follow_media_symlinks_outside_the_public_root(): void
    {
        $this->fakeMedia();
        $outside = tempnam(sys_get_temp_dir(), 'media-cleanup-');
        file_put_contents($outside, 'protected outside file');
        Storage::disk('public')->makeDirectory('posts');
        $link = Storage::disk('public')->path('posts/link.png');
        symlink($outside, $link);
        $post = Post::factory()->create(['cover_image' => null]);
        DB::table('posts')->where('id', $post->id)->update(['cover_image' => 'posts/link.png']);

        try {
            $post->refresh()->delete();

            $this->assertFileExists($outside);
            $this->assertTrue(is_link($link));
        } finally {
            if (is_link($link)) {
                unlink($link);
            }
            unlink($outside);
        }
    }

    public function test_preserves_bundled_svg_and_placeholder_images(): void
    {
        $this->fakeMedia();
        Storage::disk('public')->put('branding/logo-ansor.svg', '<svg/>');
        Storage::disk('public')->put('placeholders/news-default.webp', 'placeholder');
        $logo = Setting::setValue('site_logo', 'branding/logo-ansor.svg');
        $post = Post::factory()->create(['cover_image' => 'placeholders/news-default.webp']);

        $logo->delete();
        $post->delete();

        Storage::disk('public')->assertExists(['branding/logo-ansor.svg', 'placeholders/news-default.webp']);
    }

    /** @return array<string, array{class-string<Model>, string, string}> */
    public static function mediaAttributes(): array
    {
        return [
            'post' => [Post::class, 'cover_image', 'posts'],
            'officer' => [Officer::class, 'photo', 'officers'],
            'body' => [Body::class, 'logo', 'bodies'],
            'gallery' => [Gallery::class, 'image_path', 'galleries'],
            'registration' => [RegistrationForm::class, 'cover_image', 'forms'],
            'ranting' => [Ranting::class, 'cover', 'rantings'],
            'member' => [RantingMember::class, 'photo', 'ranting-members'],
            'setting' => [Setting::class, 'value', 'branding'],
        ];
    }

    /** @return array<string, array{class-string<Model>, string, string}> */
    public static function sharedReferences(): array
    {
        return [
            'another post' => [Post::class, 'cover_image', 'posts/shared.png'],
            'converted primary' => [Post::class, 'cover_image', 'posts/shared.webp'],
            'responsive variant' => [Post::class, 'cover_image', 'posts/shared-480w.webp'],
            'uppercase extension' => [Post::class, 'cover_image', 'posts/shared.PNG'],
            'another model' => [Officer::class, 'photo', 'posts/shared.png'],
            'site branding' => [Setting::class, 'value', 'posts/shared.png'],
            'richtext variant' => [Page::class, 'content', '<p><img src="/storage/posts/shared-800w.webp"></p>'],
            'richtext normalized URL' => [Page::class, 'content', '<p><img src="/unused/../storage/posts/shared.png"></p>'],
            'encoded absolute richtext URL' => [Page::class, 'content', '<p><img src="https://example.test/storage/posts/shared%2D800w.webp"></p>'],
        ];
    }

    /** @return array<string, array{string}> */
    public static function unsafePaths(): array
    {
        return [
            'traversal' => ['posts/../posts/protected.png'],
            'absolute filesystem path' => ['{root}/posts/protected.png'],
            'remote URL' => ['https://example.test/storage/posts/protected.png'],
        ];
    }

    private function fakeMedia(): void
    {
        Storage::fake('public');
        $this->mock(ImageService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('generateWebpVariants')->andReturnUsing(fn (string $path): string => $path);
        });
    }

    /** @param class-string<Model> $modelClass */
    private function createMediaRecord(string $modelClass, string $attribute, string $path): Model
    {
        if ($modelClass === Setting::class) {
            return Setting::setValue('site_logo', $path);
        }

        $attributes = [$attribute => $path];
        if ($modelClass === RantingMember::class) {
            $attributes['ranting_id'] = Ranting::factory()->create()->id;
        }

        return $modelClass::factory()->create($attributes);
    }

    /** @return array<int, string> */
    private function storeFamily(string $path): array
    {
        $stem = substr($path, 0, strrpos($path, '.'));
        $files = [$path, $stem.'.webp', $stem.'-480w.webp', $stem.'-800w.webp', $stem.'-1280w.webp'];
        foreach ($files as $file) {
            Storage::disk('public')->put($file, 'image fixture');
        }

        return $files;
    }
}
