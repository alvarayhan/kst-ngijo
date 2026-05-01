<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DataApproved
{
    use Dispatchable, SerializesModels;

    public $data;
    public $type;

    /**
     * Konstruktor Event
     * @param object $data Model yang disetujui (Production/Research/Sustainability)
     * @param string $type Jenis data untuk sinkronisasi
     */
    public function __construct($data, $type)
    {
        $this->data = $data;
        $this->type = $type;
    }
}