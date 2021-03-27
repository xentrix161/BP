//HOMEPAGE
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

//ARTICLE
//lexikalny uzaver, scope
(function () {
    function SendAndLoad() { //ajax trieda
        this.ajax = function (url, callback) {
            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'json',
                async: true,

                success: function (data, status) {
                    try {
                        callback(data, status);
                    } catch (e) {
                    }
                },
                error: function (xhr, textStatus, errorThrown) {
                    alert('Ajax request failed.');
                }
            });
        }
        this.addToCart = function (id, callback) {
            this.ajax("/shoppingcart/add/" + id, callback);
        }

        this.deleteFromCart = function (id, callback) {
            this.ajax("/shoppingcart/delete/" + id, callback);
        }

        this.getCartItems = function (callback) {
            this.ajax("/shoppingcart/get", callback);
        }

        this.deleteItemFromCart = function (id, callback) {
            this.ajax("/shoppingcart/delete-item/" + id, callback)
        }

        this.clearCart = function (callback) {
            this.ajax("/shoppingcart/delete-cart", callback)
        }
    }

    function Cart() {
        //trieda
        //var = private, this. = public
        var addButton = null;
        var deleteButton = null;
        var clearButton = null;
        var removeRowButton = null;

        var setCounter = function (btn, increase) {
            var counterElement = btn.parentNode.querySelector(".count span");
            if (!counterElement) {
                return;
            }
            var currValue = parseInt(counterElement.textContent); //kolko je prvkov z daneho tovaru v kosiku
            if (!isNaN(currValue)) {
                if (increase) {
                    currValue++;
                } else {
                    currValue--;
                    if (currValue < 0) {
                        currValue = 0;
                    }
                }
            }
            counterElement.textContent = currValue;
            calcPrice(counterElement);
            setTotalPrice();
        }

        var setTotalPrice = function (bypassValue) {
            var totalEl = document.querySelector("#total");
            var totalElVat = document.querySelector("#total-vat");
            if (typeof bypassValue !== "undefined" && !isNaN(parseInt(bypassValue))) {
                totalEl.textContent = bypassValue.toFixed(2);
                totalElVat.textContent = (bypassValue*0.8).toFixed(2);
                return;
            }
            var parent = totalEl.parentNode.parentNode.parentNode.parentNode;
            var rowPriceList = parent.querySelectorAll(".shopping-cart-row .price");
            console.log(rowPriceList);
            var total = 0;
            for (var i = 0; i < rowPriceList.length; i++) {
                total += parseFloat(rowPriceList[i].innerText);
            }
            totalEl.textContent = total.toFixed(2);
            totalElVat.textContent = (total*0.8).toFixed(2);
        }

        //upravuje finalnu cenu v kosiku za dany druh tovaru
        var calcPrice = function (counterElement) { //span medzi minus a plus => to cislo kolko je tovaru
            var priceValue = parseFloat(counterElement.parentNode.getAttribute("data-price")); //jednotkova cena tovaru
            var priceEl = counterElement.parentNode.parentNode.parentNode.querySelector(".price"); //price element
            var itemsCount = parseInt(counterElement.textContent); //pocet tovaru z jedneho druhu
            priceEl.textContent = (itemsCount * priceValue).toFixed(2) + "€";
        }

        var click = function () {

            if (!!addButton && addButton.length > 0) {
                for (var i = 0; i < addButton.length; i++) {
                    addButton[i].addEventListener("click", function (evt) { //prvy argument je pri com sa to vykona, a druha je callback, co sa vykona
                        evt.preventDefault(); //aby nebol page reload
                        setCounter(this, true); // v this je button, konkretne add button, v dalsej vetve je delete
                        addItem(this.getAttribute("data-id"));
                    })
                }
            }
            if (!!deleteButton && deleteButton.length > 0) {
                for (i = 0; i < deleteButton.length; i++) {
                    deleteButton[i].addEventListener("click", function (evt) { //prvy argument je pri com sa to vykona, a druha je callback, co sa vykona
                        evt.preventDefault(); //aby nebol page reload
                        setCounter(this, false); // v this je button, konkretne add button, v dalsej vetve je delete
                        deleteItem(this.getAttribute("data-id"));
                    })
                }
            }
            if (!!clearButton) {
                clearButton.addEventListener("click", function (evt) { //prvy argument je pri com sa to vykona, a druha je callback, co sa vykona
                    evt.preventDefault(); //aby nebol page reload
                    if (window.confirm("Naozaj si želáte vymazať celý obsah košíka?")) {
                        clearCart();
                    }
                })
            }
            if (!!removeRowButton && removeRowButton.length > 0) {
                for (i = 0; i < removeRowButton.length; i++) {
                    removeRowButton[i].addEventListener("click", function (evt) { //prvy argument je pri com sa to vykona, a druha je callback, co sa vykona
                        evt.preventDefault(); //aby nebol page reload
                        if (window.confirm("Naozaj si želáte odstrániť tovar z košíka?")) {
                            deleteItemFromCart(this, this.getAttribute("data-id"));
                        }
                    })
                }
            }
        }

        var addItem = function (id) {
            sendAndLoad.addToCart(id, function (data, status) {
                console.log(data, status);
                setCartCounter(data.length);
            })
        }

        var deleteItem = function (id) {
            sendAndLoad.deleteFromCart(id, function (data, status) {
                console.log(data, status);
                var len = data.length;
                var value = len ? len : 0;
                setCartCounter(value);
            })
        }

        var clearCart = function () {
            sendAndLoad.clearCart(function (data, status) {
                console.log(data, status);
                setCartCounter("0");
                var rows = document.querySelectorAll(".shopping-cart-row");
                for (var i = 0; i < rows.length; i++) {
                    var row = rows[i];
                    row.parentNode.removeChild(row);
                }
                setTotalPrice(0);
                visibilitySwitch();
            })
        }

        var deleteItemFromCart = function (btn, id) {
            sendAndLoad.deleteItemFromCart(id, function (data, status) {
                console.log(data, status);
                setCartCounter(data.numberOfItems);
                var row = btn.parentNode.parentNode;
                row.parentNode.removeChild(row);
                setTotalPrice();
                if (data.numberOfItems < 1) {
                    visibilitySwitch();
                }
            })
        }

        var setCartCounter = function (counterValue) {
            counterValue = counterValue || 0;
            document.querySelector("#item-counter span").textContent = counterValue;
        }

        var visibilitySwitch = function () {
            var elements = document.querySelectorAll(".cart-visibility-switch");
            for (var i = 0; i < elements.length; i++) {
                var element = elements[i];
                element.classList.toggle("hide");
            }
        }


        //ako konstruktor
        this.init = function () {
            addButton = document.querySelectorAll(".add-cart-btn");
            deleteButton = document.querySelectorAll(".delete-cart-btn");
            clearButton = document.querySelector("#cart-clear");
            removeRowButton = document.querySelectorAll(".delete-item");
            click();
        }
    }

    //po nacitani stranky
    (new Cart()).init();
    var sendAndLoad = new SendAndLoad();
})()
