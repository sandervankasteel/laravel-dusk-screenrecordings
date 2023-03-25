const broadcast = new BroadcastChannel( "screenrecording" );
broadcast.postMessage('ping');

chrome.runtime.sendMessage({ domain: window.location.origin }, function (response) {
    broadcast.postMessage({ signal: 'START_RECORDING' });
});

document.addEventListener('StartRecording', function(e) {
    broadcast.postMessage({ signal: 'START_RECORDING' });
});

document.addEventListener('StopRecording', function(e) {
    broadcast.postMessage({ signal: 'STOP_RECORDING' });
});

document.addEventListener('DownloadRecording', function(e) {
    broadcast.postMessage({ signal: 'DOWNLOAD_RECORDING' });
})
