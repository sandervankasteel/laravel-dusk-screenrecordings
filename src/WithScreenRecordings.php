<?php


namespace Sandervankasteel\LaravelDuskScreenrecordings;


use Closure;
use Laravel\Dusk\Browser;
use Laravel\Dusk\Concerns\ProvidesBrowser;

trait WithScreenRecordings
{
    use ProvidesBrowser;

    protected string $whichRecording = "failures"; // "failures" or "all"

    public string $downloadDir = "/tmp/screenrecordings";

    public function browse(Closure $callback)
    {
        /** @var Browser[] $browsers */
        $browsers = $this->createBrowsersFor($callback);

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

    public function endRecording($browsers)
    {
        $browsers->each(function (Browser $browser) {
            $browser->driver->executeScript("
                const stopRecordingEvent = new Event('StopRecording');
                document.dispatchEvent(stopRecordingEvent);"
            );
        });
    }

    public function storeRecording($browsers)
    {
        if(!$this->shouldStoreRecording()) {
            return;
        }

        $browsers->each(function (Browser $browser, $key) {
            $browser->driver->executeScript("
                const downloadRecordingEvent = new Event('DownloadRecording');
                document.dispatchEvent(downloadRecordingEvent);"
            );

            // Give the browser some time, to handle the file download
            $browser->pause(500);

            $target_dir = base_path('tests/Browser/screenrecordings');
            $sourceFile = "$this->downloadDir/test.webm";
            $name = $this->getCallerName();

            rename($sourceFile, "$target_dir/$name.webm");
        });
    }

    public function getChromeArgs()
    {
        $extensionPath = base_path('vendor/sandervankasteel/laravel-dusk-screenrecordings/chrome');
        
        return [
            '--enable-usermedia-screen-capturing',
            "--auto-select-desktop-capture-source='Entire Screen'",
            '--whitelisted-extension-id=cnphpifdbdiampamdipgobliknffgelk',
            "--load-extension=$extensionPath",
            '--disable-web-security',
            '--allow-http-screen-capture'
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
