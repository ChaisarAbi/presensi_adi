<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Report extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'parameters',
        'record_count',
        'generated_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'parameters' => 'array',
        'generated_at' => 'datetime',
    ];

    /**
     * Get the user that generated the report.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Create a new report record.
     */
    public static function logReport(string $type, string $title, array $parameters, int $recordCount = 0): self
    {
        return self::create([
            'user_id' => Auth::id(),
            'type' => $type,
            'title' => $title,
            'parameters' => $parameters,
            'record_count' => $recordCount,
            'generated_at' => now(),
        ]);
    }

    /**
     * Get the latest reports for the current user.
     */
    public static function getLatestReports(int $limit = 5): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('user_id', Auth::id())
            ->orderBy('generated_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get formatted parameters for display.
     */
    public function getFormattedParametersAttribute(): string
    {
        $params = $this->parameters;
        
        if ($this->type === 'class') {
            return sprintf(
                'Kelas: %s | Periode: %s - %s',
                $params['kelas'] ?? 'N/A',
                $params['start_date'] ?? 'N/A',
                $params['end_date'] ?? 'N/A'
            );
        } elseif ($this->type === 'student') {
            return sprintf(
                'Siswa: %s | Periode: %s - %s',
                $params['student_name'] ?? 'N/A',
                $params['start_date'] ?? 'N/A',
                $params['end_date'] ?? 'N/A'
            );
        }
        
        return json_encode($params, JSON_PRETTY_PRINT);
    }

    /**
     * Get the report type in Indonesian.
     */
    public function getTypeIndonesianAttribute(): string
    {
        return match($this->type) {
            'class' => 'Laporan Kelas',
            'student' => 'Laporan Siswa',
            default => $this->type,
        };
    }

    /**
     * Get the time elapsed since report generation.
     */
    public function getTimeElapsedAttribute(): string
    {
        $now = now();
        $generatedAt = $this->generated_at;
        
        $diffInMinutes = $generatedAt->diffInMinutes($now);
        $diffInHours = $generatedAt->diffInHours($now);
        $diffInDays = $generatedAt->diffInDays($now);
        
        if ($diffInMinutes < 60) {
            return $diffInMinutes . ' menit yang lalu';
        } elseif ($diffInHours < 24) {
            return $diffInHours . ' jam yang lalu';
        } else {
            return $diffInDays . ' hari yang lalu';
        }
    }
}