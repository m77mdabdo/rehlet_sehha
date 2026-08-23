<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthentication;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthenticationRecovery;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Spatie\Permission\Traits\HasRoles;

/**
 * A staff member: the doctor, the receptionist, or an administrator. There is
 * no patient login — patients are represented by the Patient model and book
 * through a public form, so every User row is clinic staff.
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $app_authentication_secret
 * @property array<int, string>|null $app_authentication_recovery_codes
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Appointment> $appointments
 * @property-read Collection<int, WorkingHour> $workingHours
 * @property-read Collection<int, BlockedSlot> $blockedSlots
 *
 * @method static UserFactory factory($count = null, $state = [])
 */
class User extends Authenticatable implements FilamentUser, HasAppAuthentication, HasAppAuthenticationRecovery
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use HasRoles;
    use Notifiable;

    /** @var list<string> */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /** @var list<string> */
    protected $hidden = [
        'password',
        'remember_token',
        // The second factor. Hidden so it cannot ride out in a JSON response
        // or a log line that serialises the model.
        'app_authentication_secret',
        'app_authentication_recovery_codes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            /*
             * Encrypted, not hashed: unlike a password these have to be read
             * back to verify a code, so they are reversible by design — which
             * is exactly why they must not sit in the table in the clear. A
             * leaked TOTP secret defeats 2FA silently and permanently.
             */
            'app_authentication_secret' => 'encrypted',
            'app_authentication_recovery_codes' => 'encrypted:array',
        ];
    }

    /**
     * May this user open the admin panel at all?
     *
     * Every User row is clinic staff — there is no patient login — but a row
     * with no role is not yet anybody, and an account whose roles were revoked
     * must lose the panel rather than land on an empty dashboard. Checked here
     * as well as by the policies, because this is the door: the policies decide
     * what someone sees once inside.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasAnyRole(['admin', 'doctor', 'receptionist']);
    }

    /*
    |--------------------------------------------------------------------------
    | Two-factor authentication (TOTP)
    |--------------------------------------------------------------------------
    |
    | Required for administrators, offered to everyone. See AdminPanelProvider
    | for where that distinction is enforced.
    |
    */

    public function getAppAuthenticationSecret(): ?string
    {
        return $this->app_authentication_secret;
    }

    public function saveAppAuthenticationSecret(?string $secret): void
    {
        $this->app_authentication_secret = $secret;
        $this->save();
    }

    /**
     * What the authenticator app shows beside the code.
     *
     * The email rather than the name: a phone may hold codes for several
     * systems, and "رنا سالم" does not say which account this belongs to.
     */
    public function getAppAuthenticationHolderName(): string
    {
        return $this->email;
    }

    /**
     * @return array<int, string>
     */
    public function getAppAuthenticationRecoveryCodes(): ?array
    {
        return $this->app_authentication_recovery_codes;
    }

    /**
     * @param  array<int, string>|null  $codes
     */
    public function saveAppAuthenticationRecoveryCodes(?array $codes): void
    {
        $this->app_authentication_recovery_codes = $codes;
        $this->save();
    }

    /**
     * Staff who can appear on the booking calendar.
     *
     * @param  Builder<self>  $query
     */
    public function scopeBookable(Builder $query): void
    {
        $query->whereHas('roles', fn (Builder $roles) => $roles->whereIn('name', ['admin', 'doctor']));
    }

    /**
     * Appointments this user is the assigned practitioner for.
     *
     * @return HasMany<Appointment, $this>
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'staff_id');
    }

    /**
     * @return HasMany<WorkingHour, $this>
     */
    public function workingHours(): HasMany
    {
        return $this->hasMany(WorkingHour::class, 'staff_id');
    }

    /**
     * @return HasMany<BlockedSlot, $this>
     */
    public function blockedSlots(): HasMany
    {
        return $this->hasMany(BlockedSlot::class, 'staff_id');
    }
}
