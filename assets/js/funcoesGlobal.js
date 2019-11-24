$().ready(function () {
    setTimeout(function () {
        $('div.alert').delay(1500).fadeOut(400); // "div.alert" é a div que tem a class alert do elemento que deseja manipular.
    }, 2500); // O valor é representado em milisegundos.
});

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
