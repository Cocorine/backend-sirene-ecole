<?php

namespace App\Models;

use App\Traits\HasUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasUlid, SoftDeletes, HasApiTokens;

    protected $fillable = [
        'nom_utilisateur',
        'identifiant',
        'mot_de_passe',
        'doit_changer_mot_de_passe',
        'mot_de_passe_change',
        'type',
        'user_account_type_id',
        'user_account_type_type',
        'role_id',
        'actif',
        'statut',
    ];

    protected $hidden = [
        'mot_de_passe',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'actif' => 'boolean',
            'doit_changer_mot_de_passe' => 'boolean',
            'mot_de_passe_change' => 'boolean',
            'statut' => 'integer',
        ];
    }

    // Polymorphic relationship to user account (Ecole, Technicien, Admin)
    public function userAccount()
    {
        return $this->morphTo('user_account_type');
    }

    // Role relationship
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    // User info relationship
    public function userInfo()
    {
        return $this->hasOne(UserInfo::class);
    }

    // OTP codes relationship
    public function otpCodes()
    {
        return $this->hasMany(OtpCode::class);
    }

    // Notifications morphed
    public function notifications()
    {
        return $this->morphMany(Notification::class, 'notifiable');
    }
}
