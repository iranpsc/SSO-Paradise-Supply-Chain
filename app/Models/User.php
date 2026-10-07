<?php

namespace App\Models;

use App\Notifications\CustomResetPasswordNotification;
use App\Notifications\CustomVerifyEmailNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\Contracts\OAuthenticatable;
use Laravel\Passport\HasApiTokens;
use RuntimeException;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class User extends Authenticatable implements MustVerifyEmail, OAuthenticatable, HasMedia
{
    use HasApiTokens, HasFactory, Notifiable, InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'code',
        'mobile',
        'referral',
        'wallet_address',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get the user's full name.
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        return $this->personalInfo->first_name . ' ' . $this->personalInfo->last_name;
    }

    /**
     * Get the user's personal info.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function personalInfo()
    {
        return $this->hasOne(PersonalInfo::class);
    }

    /**
     * Send a password reset notification to the user.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new CustomResetPasswordNotification($token));
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new CustomVerifyEmailNotification);
    }

    /**
     * Persist the next member code, retrying if a concurrent request claimed it.
     */
    public function assignMemberCode(int $maxAttempts = 5): static
    {
        $originalCode = $this->code;

        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            $this->code = static::allocateMemberCode();

            try {
                $this->save();

                return $this;
            } catch (UniqueConstraintViolationException) {
                $this->code = $originalCode;
            }
        }

        throw new RuntimeException('Unable to allocate a unique member code.');
    }

    /**
     * Next hm- code, based on the highest numeric suffix already stored.
     */
    public static function allocateMemberCode(): string
    {
        $allocate = function (): string {
            $max = static::query()
                ->whereNotNull('code')
                ->lockForUpdate()
                ->pluck('code')
                ->map(fn ($code) => (int) substr((string) $code, 3))
                ->max();

            return 'hm-'.($max === null ? 2000000 : ((int) $max) + 1);
        };

        if (DB::transactionLevel() > 0) {
            return $allocate();
        }

        return DB::transaction($allocate);
    }
}
