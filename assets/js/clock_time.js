// função time
setInterval(() => {

    let date = new Date(),
        hour = date.getHours(),
        min = date.getMinutes();

    let d;
    d = hour < 12 ? "AM" : "PM";
    our = hour > 12 ? hour - 12 : hour;
    our = hour == 0 ? hour = 12 : hour;

    // adicionar 0 a esquerda das unidades Exp: h:m:s antes 2:5:7, depois 02:05:07
    hour = hour < 10 ? "0" + hour : hour;
    min = min < 10 ? "0" + min : min;

    document.querySelector(".hour_num").innerText = hour;
    document.querySelector(".min_num").innerText = min;
    document.querySelector(".am_pm").innerText = d;

}, 1000); // 1000 milésimos é que vale a 1 segundo
