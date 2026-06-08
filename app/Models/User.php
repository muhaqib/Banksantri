<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'nis',
        'nip',
        'jabatan',
        'pin',
        'saldo',
        'rfid_code',
        'foto',
        'no_hp',
        'alamat',
        'tempat_lahir',
        'tanggal_lahir',
        'nama_wali',
        'no_hp_wali',
        'asal_sekolah',
        'kelas',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'pin',
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
            'saldo' => 'decimal:0',
        ];
    }

    /**
     * Get all transactions for this user (as santri).
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'santri_id');
    }

    /**
     * Get all transactions processed by this user (as petugas).
     */
    public function processedTransactions()
    {
        return $this->hasMany(Transaction::class, 'petugas_id');
    }

    /**
     * Get all withdrawal requests for this user (as petugas).
     */
    public function withdrawalRequests()
    {
        return $this->hasMany(WithdrawalRequest::class, 'petugas_id');
    }

    /**
     * Get all top-up requests for this user (as santri).
     */
    public function topUpRequests()
    {
        return $this->hasMany(TopUpRequest::class, 'santri_id');
    }

    /**
     * Get all top-up requests verified by this user (as admin).
     */
    public function verifiedTopUpRequests()
    {
        return $this->hasMany(TopUpRequest::class, 'admin_id');
    }

    /**
     * Get all prestasi for this user (as santri).
     */
    public function prestasi()
    {
        return $this->hasMany(PrestasiSantri::class, 'santri_id');
    }

    public function prestasiBimbingan()
    {
        return $this->hasMany(PrestasiSantri::class, 'pembimbing_id');
    }

    /**
     * Get the kamar assignment for this user (as santri).
     */
    public function kamarSantri()
    {
        return $this->hasOne(KamarSantri::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'santri_id');
    }

    public function santriPermissions()
    {
        return $this->hasMany(SantriPermission::class, 'santri_id');
    }

    public function verifyPin(string $pin): bool
    {
        if (! $this->pin) {
            return false;
        }

        if ($this->pin === $pin) {
            $this->forceFill(['pin' => Hash::make($pin)])->save();

            return true;
        }

        return Hash::check($pin, $this->pin);
    }
}
