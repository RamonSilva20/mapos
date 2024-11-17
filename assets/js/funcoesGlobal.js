$(document).ready(function () {
    const isMobile = /Mobi|Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    if (isMobile) {
        $(".tip-top").removeAttr("title");
    }
    initTimer();
});

function showTimer() {
    const timerElement = document.getElementById("timer");
    if (timerElement) {
        const time = new Date();
        const hour = String(time.getHours()).padStart(2, '0');
        const minute = String(time.getMinutes()).padStart(2, '0');
        const second = String(time.getSeconds()).padStart(2, '0');

        timerElement.innerHTML = `${hour}:${minute}:${second}`;
    }
}

function initTimer() {
    setInterval(showTimer, 1000);
}
