// Removendo o atributo tittle para dispositivos moveis.
$(document).ready(function () {
    if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
        $(".tip-top").removeAttr("title");
    }
});

function showTimer() {
    var time = new Date();
    var hour = time.getHours();
    var minute = time.getMinutes();
    var second = time.getSeconds();

    if (hour < 10) hour = "0" + hour;
    if (minute < 10) minute = "0" + minute;
    if (second < 10) second = "0" + second;

    var st = hour + ":" + minute + ":" + second; document.getElementById("timer").innerHTML = st;
}

function initTimer() {

    // O metodo nativo setInterval executa uma determinada funcao em um determinado tempo
    setInterval(showTimer, 1000);
}
