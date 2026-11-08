<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

#[Fillable(['name', 'username', 'email', 'password', 'sort_order'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected static function booted(): void
    {
        static::saving(function (User $user): void {
            if (blank($user->username) && filled($user->name)) {
                $baseUsername = Str::slug($user->name, '_') ?: 'user';
                $username = $baseUsername;
                $counter = 2;
                while (static::query()->where('username', $username)->when($user->exists, fn ($q) => $q->where('id', '!=', $user->id))->exists()) {
                    $username = "{$baseUsername}_{$counter}";
                    $counter++;
                }
                $user->username = $username;
            }

            if (blank($user->email) && filled($user->username)) {
                $baseEmail = $user->username.'@ansor.id';
                $email = $baseEmail;
                $counter = 2;
                while (static::query()->where('email', $email)->when($user->exists, fn ($q) => $q->where('id', '!=', $user->id))->exists()) {
                    $email = "{$user->username}_{$counter}@ansor.id";
                    $counter++;
                }
                $user->email = $email;
            }

            if (! $user->exists && blank($user->sort_order)) {
                $user->sort_order = ((int) static::query()->max('sort_order')) + 1;
            }
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'sort_order' => 'integer',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    /**
     * @return HasMany<Post, $this>
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'author_id');
    }
}
