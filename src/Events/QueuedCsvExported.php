<?php

namespace NovaKit\NovaQueuedExportAsCsv\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QueuedCsvExported
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable&\Illuminate\Database\Eloquent\Model  $user
     * @return void
     */
    public function __construct(
        public $user,
        public readonly string $filename,
        public readonly ?string $storageDisk
    ) {
        //
    }
}
