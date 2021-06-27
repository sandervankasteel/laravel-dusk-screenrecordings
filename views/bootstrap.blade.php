<html>
<head>
    <title>Screen recording</title>
</head>
<body>
<script>
    class ScreenRecorder {
        _recorder;
        _data = [];
        _type = 'video/webm';
        _isReady = false;
        _src;

        constructor(src = null) {
            this._src = src;
        }

        setUp() {
            this._recorder = new MediaRecorder(this._src, {
                mimeType: this._type
            });

            this._recorder.ondataavailable = this._handleDataAvailable.bind(this);
            this.start();

            return true;
        }

        start() {
            if(this._recorder.state !== 'recording') {
                this._recorder.start();
            }
        }

        stop() {
            this._recorder.stop();
        }

        setSource(newSrc) {
            if(this._src === null) {
                this._src = newSrc;
            }
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

            console.log(blob);

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

    const handleBroadcastMessages = function (ev) {
        if(!ev.data.signal) {
            return;
        }

        switch(ev.data.signal) {
            case 'START_RECORDING':
                recorder.start();
                break;

            case 'STOP_RECORDING':
                recorder.stop();
                break;

            case 'DOWNLOAD_RECORDING':
                recorder.download();
                break;
        }
    };

    const setup = async () => {
        stream = await navigator.mediaDevices.getDisplayMedia({
            video: true
        });

        recorder.setSource(stream);
        recorder.setUp();

        console.log(stream);
        console.log(recorder);
    };

    const broadcast = new BroadcastChannel("screenrecording");
    broadcast.onmessage = handleBroadcastMessages;

    setup();
</script>
</body>
</html>
