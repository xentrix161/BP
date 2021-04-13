var actualTime = function () {
    //Dátum
    var date = new Date();
    var year = date.getFullYear();
    var dayInWeek = date.getDay();
    var month = date.getMonth();
    var dayInMonth = date.getDate();
    var days = ["Nedeľa", "Pondelok", "Utorok", "Streda", "Štvrtok", "Piatok", "Sobota"];
    var months = ["Január", "Február", "Marec", "Apríl", "Máj", "Jún", "Júl", "August", "September",
        "Október", "November", "December"];
    //Čas
    var hours = date.getHours();
    var minutes = date.getMinutes();
    var seconds = date.getSeconds();
    if (hours === 24) {
        hours = 0;
    }

    if (hours < 10) {
        hours = "0" + hours;
    }

    if (minutes < 10) {
        minutes = "0" + minutes;
    }

    if (seconds < 10) {
        seconds = "0" + seconds;
    }
    var finalTime = document.getElementById("timeDisplay");
    var finalDate = document.getElementById("dateDisplay");

    if (!finalTime || !finalDate) {
        return;
    }

    finalDate.textContent = "" + dayInMonth + ". " + months[month] + " " + year;

    finalTime.textContent = "" + hours + ":" + minutes + ":" + seconds + "  (" + days[dayInWeek] + ")";
    setTimeout(actualTime, 1000);
}
actualTime();