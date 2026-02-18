<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SyncProgress extends Model
{
    protected $table = 'sync_progress';
    
    protected $fillable = [
        'session_id',
        'total',
        'processed',
        'successful',
        'failed',
        'is_complete',
        'error_message',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'is_complete' => 'boolean',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Incrementar contador de procesados
     */
    public function incrementProcessed(bool $success = true): void
    {
        $this->increment('processed');
        
        if ($success) {
            $this->increment('successful');
        } else {
            $this->increment('failed');
        }

        // Marcar como completado si ya procesamos todos
        if ($this->processed >= $this->total) {
            $this->update([
                'is_complete' => true,
                'completed_at' => now(),
            ]);
        }
    }

    /**
     * Obtener porcentaje de progreso
     */
    public function getPercentageAttribute(): float
    {
        if ($this->total == 0) return 0;
        return round(($this->processed / $this->total) * 100, 1);
    }

    /**
     * Obtener tiempo transcurrido
     */
    public function getElapsedTimeAttribute(): ?int
    {
        if (!$this->started_at) return null;
        
        $end = $this->completed_at ?? now();
        return $this->started_at->diffInSeconds($end);
    }
}