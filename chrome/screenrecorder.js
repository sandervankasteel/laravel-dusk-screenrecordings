class ScreenRecorder {
    _stream;
    _recorder;

    constructor() {
        this._stream = new MediaStream();
        this._recorder = new MediaRecorder(this._stream, {
            mimeType: 'video/mp4; codecs="avc1.4d002a"'
        });
    }

    start() {
        console.log('starting recording');

        this._recorder.start();
    }

    stop() {
        console.log('stopping recording');
        this._recorder.stop();
    }

    getContent() {

    }
}
