<?php


namespace Sandervankasteel\LaravelDuskScreenrecordings;


use Closure;
use Laravel\Dusk\Browser;
use Laravel\Dusk\Concerns\ProvidesBrowser;

trait WithScreenRecordings
{
    use ProvidesBrowser;

    protected string $whichRecording = "failures"; // "failures" or "all"

    public static string $storeRecordingsAt;

    public string $downloadDir = "/tmp/screenrecordings";

    public function browse(Closure $callback)
    {
//        /** @var Browser[] $browsers */
        $browsers = $this->createBrowsersFor($callback);
        $this->startRecording($browsers);

        try {
            $callback(...$browsers->all());
        } catch (\Throwable | \Exception $e) {
            $this->captureFailuresFor($browsers);
            $this->storeSourceLogsFor($browsers);

            throw $e;
        } finally {
            $this->storeConsoleLogsFor($browsers);

            $this->endRecording($browsers);
            $this->storeRecording($browsers);


            static::$browsers = $this->closeAllButPrimary($browsers);
        }
    }


    /**
     * @param  \Illuminate\Support\Collection  $browsers
     */
    public function startRecording($browsers): void
    {
        dump("Starting recording");

        $browsers->each(static function (Browser $browser) {
            $browser->driver->executeScript("
                const startRecordingEvent = new Event('StartRecording');
                document.dispatchEvent(startRecordingEvent);"
            );

            $browser->pause(1000);
        });

    }

    public function endRecording($browsers)
    {
        dump("Ending recording");

        $browsers->each(function (Browser $browser) {
            $browser->driver->executeScript("
                const stopRecordingEvent = new Event('StopRecording');
                document.dispatchEvent(stopRecordingEvent);"
            );
        });
    }

    public function storeRecording($browsers)
    {
        dump("Storing recording");

        if(!$this->shouldStoreRecording()) {
            return;
        }

        $browsers->each(function (Browser $browser, $key) {
            $browser->driver->executeScript("
                const downloadRecordingEvent = new Event('DownloadRecording');
                document.dispatchEvent(downloadRecordingEvent);"
            );

            // Give the browser some time, to handle the file download
            $browser->pause(10000);

            $target_dir = base_path('tests/Browser/Screenrecordings');
            $sourceFile = "$this->downloadDir/test.webm";
            $name = $this->getCallerName();

            $success = rename($sourceFile, "$target_dir/$name.webm");

            dump($success);
        });
    }

    public function getChromeArgs()
    {
        $extensionPath = base_path('vendor/sandervankasteel/laravel-dusk-screenrecordings/chrome');
        
        return [
            '--auto-select-desktop-capture-source="Entire screen"',
            "--load-extension=$extensionPath",
            '--disable-web-security'
        ];
    }

    public function getGeckoArgs()
    {
        return [

        ];
    }

    private function shouldStoreRecording()
    {
        return true;
    }
}
