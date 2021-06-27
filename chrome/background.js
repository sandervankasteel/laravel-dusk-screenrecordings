chrome.runtime.onMessage.addListener(
    function (request, sender, sendResponse) {

        chrome.tabs.query({
            title: 'Screen recording',
        }).then((tabs) => {
            if(tabs.length === 0) {
                chrome.tabs.create({
                    url: request.domain + '/_screenrecording/bootstrap',
                    pinned: true
                });

                chrome.tabs.highlight({tabs: 1});
            }
        })

        return true;
    }
)
