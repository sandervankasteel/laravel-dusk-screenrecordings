class ScreenRecorder {
    _recorder;
    _data = [];
    _type = 'video/webm';

    constructor() {}

    async setUp() {
        const stream = await navigator.mediaDevices.getDisplayMedia({
            audio: false,
            video: true
        });

        this._recorder = new MediaRecorder(stream, {
            mimeType: this._type
        });

        this._recorder.ondataavailable = this._handleDataAvailable.bind(this);

        return true;
    }

    start() {
        console.log('Starting recording');

        this._recorder.start();
    }

    stop() {
        console.log('Stopping recording');
        this._recorder.stop();
        console.log(this);
    }

    async getBase64Content() {
        return URL.createObjectURL(this._data);
    }

    _handleDataAvailable(e) {
        console.log(e);
        this._data.push(e.data);
    }

    download() {
        var blob = new Blob(this._data, {
            type: "video/webm"
        });
        var url = URL.createObjectURL(blob);

        var a = document.createElement("a");
        document.body.appendChild(a);

        a.style = "display: none";
        a.href = url;
        a.download = "test.webm";

        a.click();

        window.URL.revokeObjectURL(url);
    }
}

const recorder = new ScreenRecorder();
const resp = recorder.setUp();
console.log(resp)

document.addEventListener('StartRecording', function(e) {
    console.log('start recording from script.js');
    console.log(recorder);


    recorder.start();
});

document.addEventListener('StopRecording', function(e) {
    recorder.stop();
});

document.addEventListener('DownloadRecording', function(e) {
    recorder.download();
})
