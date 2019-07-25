$().ready(function () {
    setTimeout(function () {
        $('div.alert').delay(1500).fadeOut(400); // "div.alert" é a div que tem a class alert do elemento que deseja manipular.
    }, 2500); // O valor é representado em milisegundos.
});

// Removendo o atributo tittle para dispositivos moveis. 
$(document).ready(function() {
    if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {    
        $(".tip-top").removeAttr("title");
    }
});
