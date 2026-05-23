<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'roles',
        'class',
        'phone',
        'address',
        'photo',
        'status',
        'is_verified',
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
        'is_verified' => 'boolean',
        'roles' => 'array',
    ];

    /**
     * Cek apakah user punya role tertentu
     */
    public function hasRole(string $r): bool
    {
        $requestedRole = strtolower(trim($r));

        $storedRoles = $this->roles ?? ($this->role !== null ? [$this->role] : []);
        if (!is_array($storedRoles)) {
            $storedRoles = [$storedRoles];
        }

        foreach ($storedRoles as $storedRole) {
            if (strtolower(trim((string) $storedRole)) === $requestedRole) {
                return true;
            }
        }

        return false;
    }

    /**
     * Daftar sebagai buyer (tambah role buyer jika belum ada)
     */
    public function addBuyerRole(): void
    {
        $roles = $this->roles ?? [$this->role];
        if (!in_array('buyer', $roles)) {
            $roles[] = 'buyer';
            $this->roles = $roles;
            $this->save();
        }
    }

    /**
     * Relasi: User memiliki satu bisnis (jika seller)
     */
    public function business()
    {
        return $this->hasOne(Business::class);
    }

    /**
     * Relasi: User (melalui bisnis) memiliki banyak produk
     */
    public function products()
    {
        return $this->hasManyThrough(Product::class, Business::class);
    }

    /**
     * Relasi: User memiliki satu wallet (untuk seller)
     */
    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }


    /**
     * Relasi: User (sebagai pembeli) memiliki keranjang
     */
    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Relasi: User memiliki banyak notifikasi
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Relasi: User mengirim banyak pesan
     */
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Relasi: User menerima banyak pesan
     */
    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    /**
     * Relasi: User menjadi anggota tim di banyak bisnis (jika premium)
     */
    public function teamMemberships()
    {
        return $this->hasMany(TeamMember::class);
    }

    /**
     * Relasi: User membuat banyak laporan issue
     */
    public function issueReports()
    {
        return $this->hasMany(IssueReport::class, 'buyer_id');
    }
}