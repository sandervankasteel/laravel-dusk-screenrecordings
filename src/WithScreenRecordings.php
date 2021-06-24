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

    public function browse(Closure $callback): Closure
    {
        /** @var Browser[] $browsers */
        $browsers = $this->createBrowsersFor($callback);

//        try {
            parent::browse(function() use ($callback, $browsers) {
                $this->startRecording($browsers);

                $callback();

                $this->endRecording($browsers);
                $this->storeRecording($browsers);
            });
/*        } catch (\Throwable | \Exception $e) {
            $this->endRecording($browsers);
            $this->storeRecording($browsers);

            throw $e;
        }*/
    }

    public function startRecording($browsers)
    {
        dump("Starting recording");

        $browsers->each(static function (Browser $browser, $key) {
            $browser->driver->executeScript("
                const startRecordingEvent = new Event('StartRecording');
                document.dispatchEvent(startRecordingEvent);"
            );
        });

    }

    public function endRecording($browsers)
    {
        dump("Ending recording");

        $browsers->each(function (Browser $browser, $key) {
            $browser->driver->executeScript("
                const stopRecordingEvent = new Event('StopRecording');
                document.dispatchEvent(stopRecordingEvent);"
            );

            if($this->shouldStoreRecording()) {
                $this->storeRecording($browser);
            }
        });
    }

    public function storeRecording($browsers)
    {
        dump("Storing recording");

        $browsers->each(function (Browser $browser, $key) {
            $browser->driver->execute("
            const downloadRecordingEvent = new Event('DownloadRecording');
            document.dispatchEvent(downloadRecordingEvent);
        ");

            // Give the browser some time, to handle the file download
            $browser->pause(500);

            $target_dir = base_path('tests/Browser/Screenrecordings');
            $sourceFile = "$this->downloadDir/test.webm";

            $success = rename($sourceFile, $target_dir + $this->getCallerName() . ".webm");

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
