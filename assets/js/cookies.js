// (function (){
//     var cookieBar = document.querySelector('.cookie-bar');
//     var cookieBtn = cookieBar.querySelector('button');
//
//     if (window.localStorage.getItem('cookieBar')) {
//         cookieBar.parentNode.removeChild(cookieBar);
//     }
//
//     cookieBtn.addEventListener("click", function (){
//         var ls = window.localStorage;
//         if (!ls.getItem('cookieBar')) {
//             ls.setItem('cookieBar', 'true');
//             cookieBar.parentNode.removeChild(cookieBar);
//         }
//     });
// })()