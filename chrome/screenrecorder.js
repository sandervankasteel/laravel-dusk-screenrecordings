class ScreenRecorder {
    _recorder;
    _data = [];
    _type = 'video/webm';

    _src;

    constructor(src) {
        this._src = src;
    }

    setUp() {
        this._recorder = new MediaRecorder(this._src, { mimeType: this._type });
        this._recorder.ondataavailable = this._handleDataAvailable.bind(this);
        return true;
    }

    start() {
        this._recorder.start();
    }

    stop() {
        this._recorder.stop();
        // console.log(this);
    }

    _handleDataAvailable(e) {
        this._data.push(e.data);
    }

    download() {
        var blob = new Blob(this._data, { type: "video/webm" });
        var url = URL.createObjectURL(blob);

        // console.log(blob);

        var a = document.createElement("a");
        document.body.appendChild(a);

        a.style = "display: none";
        a.href = url;
        a.download = "test.webm";

        a.click();

        window.URL.revokeObjectURL(url);
    }
}
