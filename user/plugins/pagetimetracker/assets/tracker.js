class PageTimeTracker {
    constructor() {
        this.startTime = 0;
        this.totalTime = 0;
        this.isVisible = true;
        this.pagePath = window.location.pathname;
        this.init();
    }

    init() {
        this.startTracking();
        this.setupEventListeners();
    }

    startTracking() {
        this.startTime = Date.now();
    }

    setupEventListeners() {
        document.addEventListener('visibilitychange', this.handleVisibilityChange.bind(this));
        window.addEventListener('beforeunload', this.sendData.bind(this));
        window.addEventListener('unload', this.sendData.bind(this));
    }

    handleVisibilityChange() {
        if (document.hidden) {
            this.isVisible = false;
            this.calculateTime();
        } else {
            this.isVisible = true;
            this.startTime = Date.now();
        }
    }

    calculateTime() {
        if (this.isVisible) {
            const now = Date.now();
            this.totalTime += now - this.startTime;
            this.startTime = now;
        }
    }

    sendData() {
        this.calculateTime();
        
        if (this.totalTime > 0) {
            const data = {
                path: this.pagePath,
                duration: Math.round(this.totalTime / 1000), // 转换为秒
                timestamp: Math.floor(Date.now() / 1000)
            };

            const url = '/time-tracker/log';
            const formData = new FormData();
            formData.append('data', JSON.stringify(data));

            if (navigator.sendBeacon) {
                navigator.sendBeacon(url, formData);
            } else {
                // 降级方案
                const xhr = new XMLHttpRequest();
                xhr.open('POST', url, false);
                xhr.send(formData);
            }
        }
    }
}

// 初始化跟踪器
if (typeof window !== 'undefined') {
    window.PageTimeTracker = new PageTimeTracker();
}