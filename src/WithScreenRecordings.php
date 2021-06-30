<?php


namespace Sandervankasteel\LaravelDuskScreenrecordings;


use Closure;
use Laravel\Dusk\Browser;
use Laravel\Dusk\Concerns\ProvidesBrowser;

trait WithScreenRecordings
{
    use ProvidesBrowser;

    public string $downloadDir;

    public function browse(Closure $callback)
    {
        $this->downloadDir = config('screenrecording.download_directory');

        /** @var Browser[] $browsers */
        $browsers = $this->createBrowsersFor($callback);

        $failure = false;

        try {
            $callback(...$browsers->all());
        } catch (\Throwable | \Exception $e) {
            $this->captureFailuresFor($browsers);
            $this->storeSourceLogsFor($browsers);
            $failure = true;

            throw $e;
        } finally {
            $this->storeConsoleLogsFor($browsers);

            $this->endRecording($browsers);
            $this->storeRecording($browsers, $failure);


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

    public function storeRecording($browsers, $failure = false)
    {
        if(!$this->shouldStoreRecording($failure)) {
            return;
        }

        $browsers->each(function (Browser $browser, $key) {
            $browser->driver->executeScript("
                const downloadRecordingEvent = new Event('DownloadRecording');
                document.dispatchEvent(downloadRecordingEvent);"
            );

            // Give the browser some time, to handle the file downloading process
            $browser->pause(500);

            $target_dir = config('screenrecording.target_directory');

            $sourceFile = "$this->downloadDir/test.webm";
            $name = $this->getCallerName();

            rename($sourceFile, "$target_dir/$name.webm");
        });
    }

    /**
     * Gets the arguments needed for
     *
     * @return string[]
     */
    public function getChromeArgs()
    {
        $extensionPath = __DIR__ . '/chrome';
//        $extensionPath = base_path('vendor/sandervankasteel/laravel-dusk-screenrecordings/chrome');
        
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

    /**
     * Determines, based on the configuration and test status (failure or success) if a recording should be stoed
     *
     * @param $failure boolean Indicating if the test in question, has failed
     * @return bool boolean If the recording should be stoe
     */
    private function shouldStoreRecording($failure = false)
    {
        $setting = config('screenrecording.to_store');

        if($setting === 'all') {
            return true;
        }

        if($setting === 'failures' && $failure) {
            return true;
        }

        if($setting === 'successes' && !$failure) {
            return true;
        }

        return false;
    }
}
