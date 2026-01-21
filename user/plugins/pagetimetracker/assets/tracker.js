;(function() {
    let startTime = Date.now();
    let activeTime = 0;
    let lastVisibleTime = Date.now();

    function updateActiveTime() {
        if (document.visibilityState === 'visible') {
            activeTime += Date.now() - lastVisibleTime;
            lastVisibleTime = Date.now();
        }
    }

    document.addEventListener('visibilitychange', function() {
        if (document.visibilityState === 'hidden') {
            updateActiveTime();
            sendData();
        } else {
            lastVisibleTime = Date.now();
        }
    });

    window.addEventListener('beforeunload', function() {
        updateActiveTime();
        sendData();
    });

    function sendData() {
        const duration = Math.round(activeTime / 1000);
        if (duration > 0) {
            const data = {
                url: window.location.href,
                duration: duration
            };

            if (navigator.sendBeacon) {
                navigator.sendBeacon('/time-tracker/log', JSON.stringify(data));
            } else {
                fetch('/time-tracker/log', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data),
                    keepalive: true
                });
            }
        }
    }
})();
