<?php

namespace App\Modules\User\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Storage;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasUuids, HasRoles, SoftDeletes;

    const TABLE = 'users';

    protected $table = self::TABLE;
    protected $primaryKey = self::ID; // Or your UUID column name
    public $incrementing = false;
    protected $keyType = 'string'; // UUIDs are strings

    const ID = 'id';
    const EMAIL = 'email';
    const NAME = 'name';
    const SURNAME = 'surname';
    const EMAIL_VERIFIED_AT = 'email_verified_at';
    const PASSWORD = 'password';
    const REMEMBER_TOKEN = 'remember_token';
    const AVATAR = 'avatar';
    const PHONE = 'phone';
    const TYPE_DOCUMENT = 'type_document';
    const N_DOCUMENT = 'n_document';
    const BIRTH_DATE = 'birth_date';
    const DESIGNATION = 'designation';
    const GENDER = 'gender';
    const IS_SUPERADMIN = 'is_superadmin';

    const TYPE_DOCUMENT_DNI_VALUE = 'dni';
    const TYPE_DOCUMENT_NIE_VALUE = 'nie';
    const TYPE_DOCUMENT_PASSPORT_VALUE = 'passport';
    const TYPE_DOCUMENT_VALUES = [
        self::TYPE_DOCUMENT_DNI_VALUE,
        self::TYPE_DOCUMENT_NIE_VALUE,
        self::TYPE_DOCUMENT_PASSPORT_VALUE,
    ];

    const GENDER_MALE_VALUE = 'male';
    const GENDER_FEMALE_VALUE = 'female';
    const GENDER_OTHER_VALUE = 'other';
    const GENDER_VALUES = [
        self::GENDER_MALE_VALUE,
        self::GENDER_FEMALE_VALUE,
        self::GENDER_OTHER_VALUE,
    ];

    const PATH_FOLDER_AVATARS = 'user/avatars';


    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        self::EMAIL,
        self::NAME,
        self::SURNAME,
        self::PASSWORD,
        self::AVATAR,
        self::PHONE,
        self::TYPE_DOCUMENT,
        self::N_DOCUMENT,
        self::BIRTH_DATE,
        self::DESIGNATION,
        self::GENDER,
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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
        ];
    }

    protected static function newFactory()
    {
        return UserFactory::new();
    }

    public function scopeWithoutSuperAdmin($query)
    {
        return $query->where(self::IS_SUPERADMIN, false);
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    function getUrlAvatarAttribute(): null|string
    {
        if ($this->{self::AVATAR} && Storage::disk('public')->exists($this->{self::AVATAR})) {
            return Storage::disk('public')->url($this->{self::AVATAR});
        }

        return null;  // Storage::disk('public')->url(self::PATH_FOLDER_AVATARS . '/default.jpg');
    }
}
