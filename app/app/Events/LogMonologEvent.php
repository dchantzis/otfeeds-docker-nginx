<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;

class LogMonologEvent
{

    use SerializesModels;

    public $record;

    public function __construct(array $record)
    {
        $this->record = $record;
    }

}
