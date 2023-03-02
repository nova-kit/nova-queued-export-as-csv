Laravel Nova's Queued Export As CSV Action
==============

[![tests](https://github.com/nova-kit/nova-queued-export-as-csv/workflows/tests/badge.svg?branch=main)](https://github.com/nova-kit/nova-queued-export-as-csv/actions?query=workflow%3Atests+branch%3Amain)
[![Latest Stable Version](https://poser.pugx.org/nova-kit/nova-queued-export-as-csv/v/stable)](https://packagist.org/packages/nova-kit/nova-queued-export-as-csv)
[![Total Downloads](https://poser.pugx.org/nova-kit/nova-queued-export-as-csv/downloads)](https://packagist.org/packages/nova-kit/nova-queued-export-as-csv)
[![Latest Unstable Version](https://poser.pugx.org/nova-kit/nova-queued-export-as-csv/v/unstable)](https://packagist.org/packages/nova-kit/nova-queued-export-as-csv)
[![License](https://poser.pugx.org/nova-kit/nova-queued-export-as-csv/license)](https://packagist.org/packages/nova-kit/nova-queued-export-as-csv)

### Installation

To install through composer, run the following command from terminal:

```bash 
composer require "nova-kit/nova-queued-export-as-csv"
```

## Usages

You can replace `Laravel\Nova\Actions\ExportAsCsv` with `NovaKit\NovaQueuedExportAsCsv\Actions\QueuedExportAsCsv`:

```php
use Laravel\Nova\Actions\ExportAsCsv;
use NovaKit\NovaQueuedExportAsCsv\Actions\QueuedExportAsCsv;

/**
 * Get the actions available for the resource.
 *
 * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
 * @return array
 */
public function actions(NovaRequest $request)
{
    return [
        QueuedExportAsCsv::make(),
    ];
}
```

If you would like to change the storage disk to store the CSV file that is available for download, you may invoke the `withStorageDisk()` method when registering the action:

```php
return [
    QueuedExportAsCsv::make()->withStorageDisk('s3'),
];
```

You can also customise the response message by using the following code:

```php
use Laravel\Nova\Actions\Action;
use NovaKit\NovaQueuedExportAsCsv\Actions\QueuedExportAsCsv;

return [
    QueuedExportAsCsv::make()->then(function () {
        return response()->json(Action::message('Action has been queued!'));
    }),
];
```

In order to handle the stored CSV, you need to listen to `NovaKit\NovaQueuedExportAsCsv\Events\QueuedCsvExported` event, as an example you can broadcast to Nova's Notification using the following listener class:

```php
<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Storage;
use Laravel\Nova\Notifications\NovaNotification;
use Laravel\Nova\URL;
use NovaKit\NovaQueuedExportAsCsv\Events\QueuedCsvExported;

class NotifyCsvExported
{
    /**
     * Handle the event.
     *
     * @param  \NovaKit\NovaQueuedExportAsCsv\Events\QueuedCsvExported  $event
     * @return void
     */
    public function handle(QueuedCsvExported $event)
    {
        $event->user->notify(
            NovaNotification::make()
                ->message('Your CSV is ready to download.')
                ->action('Download', URL::remote(Storage::disk($event->storageDisk)->url($event->filename)))
                ->icon('download')
                ->type('info')
        );
    }
}
```
