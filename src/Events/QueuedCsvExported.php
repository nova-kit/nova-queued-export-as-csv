<?php

namespace NovaKit\NovaQueuedExportAsCsv\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QueuedCsvExported
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The User instance.
     *
     * @var \Illuminate\Contracts\Auth\Authenticatable&\Illuminate\Database\Eloquent\Model
     */
    public $user;

    /**
     * The storage filename.
     *
     * @var string
     */
    public $filename;

    /**
     * The storage disk.
     *
     * @var string|null
     */
    public $storageDisk;

    /**
     * Create a new event instance.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable&\Illuminate\Database\Eloquent\Model  $user
     * @param  string|null  $storageDisk
     * @return void
     */
    public function __construct($user, string $filename, $storageDisk)
    {
        $this->user = $user;
        $this->filename = $filename;
        $this->storageDisk = $storageDisk;
    }
}
