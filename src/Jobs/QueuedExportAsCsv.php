<?php

namespace NovaKit\NovaQueuedExportAsCsv\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Auth\User;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\File;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Laravel\Nova\Util;
use function Laravie\SerializesQuery\unserialize;
use NovaKit\NovaQueuedExportAsCsv\Events\QueuedCsvExported;
use Rap2hpoutre\FastExcel\FastExcel;

class QueuedExportAsCsv implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The query builder.
     *
     * @var array<string, mixed>
     */
    public $query;

    /**
     * The User ID.
     *
     * @var string|int
     */
    public $userId;

    /**
     * The custom format callback.
     *
     * @var (callable(\Illuminate\Database\Eloquent\Model):array<string, mixed>)|null
     */
    public $withFormatCallback;

    /**
     * The configuration options.
     *
     * @var array{filename: string, storageDisk: string|null, notify: string}
     */
    public $options;

    /**
     * Create a new job instance.
     *
     * @param  array<string, mixed>  $query
     * @param  string|int  $userId
     * @param  (callable(\Illuminate\Database\Eloquent\Model):array<string, mixed>)|null  $withFormatCallback
     * @param  array{filename: string, storageDisk: string|null, notify: string}  $options
     * @return void
     */
    public function __construct(array $query, $userId, $withFormatCallback, array $options)
    {
        $this->query = $query;
        $this->userId = $userId;
        $this->withFormatCallback = $withFormatCallback;

        $this->options = array_merge([
            'storageDisk' => null,
        ], $options);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $query = unserialize($this->query);

        $eloquentGenerator = function () use ($query) {
            foreach ($query->cursor() as $model) {
                yield $model;
            }
        };

        /** @phpstan-ignore-next-line */
        $withFormatCallback = ! is_null($this->withFormatCallback) ? \unserialize($this->withFormatCallback)->getClosure() : null;

        /** @var \Illuminate\Contracts\Auth\Authenticatable&\Illuminate\Database\Eloquent\Model $userModel */
        $userModel = Util::userModel();
        $storageDisk = $this->options['storageDisk'];
        $filename = $this->options['filename'];

        $exportedFilename = (new FastExcel($eloquentGenerator()))->export("/tmp/{$filename}", $withFormatCallback);

        $storedFilename = Storage::disk($storageDisk)->putFileAs(
            'nova-actions-export-as-csv', new File($exportedFilename), $filename, 'public'
        );

        (new Filesystem())->delete($exportedFilename);

        /** @var \Illuminate\Contracts\Auth\Authenticatable&\Illuminate\Database\Eloquent\Model $user */
        $user = $userModel::findOrFail($this->userId);

        QueuedCsvExported::dispatch(
            $user, $storedFilename, $storageDisk
        );
    }
}
