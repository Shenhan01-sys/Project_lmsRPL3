<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class StudentRegistration extends Model
{
    protected $fillable = [
        'user_id',
        'tanggal_lahir',
        'tempat_lahir',
        'jenis_kelamin',
        'nama_orang_tua',
        'phone_orang_tua',
        'alamat_orang_tua',
        'ktp_orang_tua_path',
        'ijazah_path',
        'foto_siswa_path',
        'bukti_pembayaran_path',
        'registration_status',
        'submitted_at',
        'approved_at',
        'approval_notes',
        'approved_by',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     */
    protected $appends = [
        'ktp_orang_tua_url',
        'ijazah_url',
        'foto_siswa_url',
        'bukti_pembayaran_url',
        'is_complete',
        'is_pending_approval',
    ];

    /**
     * Get the user that owns the registration
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who approved/rejected this registration
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the full URL for KTP orang tua document
     */
    public function getKtpOrangTuaUrlAttribute(): ?string
    {
        return $this->ktp_orang_tua_path ? Storage::url($this->ktp_orang_tua_path) : null;
    }

    /**
     * Get the full URL for ijazah document
     */
    public function getIjazahUrlAttribute(): ?string
    {
        return $this->ijazah_path ? Storage::url($this->ijazah_path) : null;
    }

    /**
     * Get the full URL for foto siswa document
     */
    public function getFotoSiswaUrlAttribute(): ?string
    {
        return $this->foto_siswa_path ? Storage::url($this->foto_siswa_path) : null;
    }

    /**
     * Get the full URL for bukti pembayaran document
     */
    public function getBuktiPembayaranUrlAttribute(): ?string
    {
        return $this->bukti_pembayaran_path ? Storage::url($this->bukti_pembayaran_path) : null;
    }

    /**
     * Check if registration is complete (all documents uploaded)
     */
    public function getIsCompleteAttribute(): bool
    {
        return !empty($this->ktp_orang_tua_path) &&
               !empty($this->ijazah_path) &&
               !empty($this->foto_siswa_path) &&
               !empty($this->bukti_pembayaran_path);
    }

    /**
     * Check if registration is pending approval
     */
    public function getIsPendingApprovalAttribute(): bool
    {
        return $this->registration_status === 'pending_approval';
    }

    /**
     * Check if registration is approved
     */
    public function getIsApprovedAttribute(): bool
    {
        return $this->registration_status === 'approved';
    }

    /**
     * Check if registration is rejected
     */
    public function getIsRejectedAttribute(): bool
    {
        return $this->registration_status === 'rejected';
    }
}
