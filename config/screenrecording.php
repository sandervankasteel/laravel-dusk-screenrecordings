<?php

return [

    /**
     * Configuration to set which recordings should be stored.
     * Options
     *  - 'all', stores all failures and successes tests
     *  - 'failures', only stores failures
     *  - 'successes', only store successes
     */
    'to_store' => env('SCREENRECORDING_STORE', 'all'),

    /**
     * The source which is being used to record with.
     *
     * By default it will select the 'Entire Screen', but you can use a window name or 'Screen 1' / 'Screen 2'
     * in case when running this on a dual  monitor setup. Please note, naming need to conform to what the browser calls
     * the sharing options, when 'sharing'.
     */
    'source' => env('SCREENRECORDING_SOURCE', 'Entire Screen'),


    /**
     * The directory where to store the screen recordings.
     * Can be used in CI pipelines for archiving purposes.
     */
    'target_directory' => env('SCREENRECORDING_TARGET_DIR', base_path('tests/Browser/screenrecordings')),

    /**
     * The directory where the browser downloads the screen recording to.
     */
    'download_directory' => env('SCREENRECORDING_DOWNLAD_DIR', sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'screenrecordings'),
];
