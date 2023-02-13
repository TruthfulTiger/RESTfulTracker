import * as mdb from './mdb/mdb.min'; // lib
window.mdb = mdb;

let simoleons = wNumb({
    decimals: 0,
    thousand: ',',
    prefix: '§'
});

// Example starter JavaScript for disabling form submissions if there are invalid fields
(() => {
    'use strict';

    // Fetch all the forms we want to apply custom Bootstrap validation styles to
    const forms = document.querySelectorAll('.needs-validation');

    // Loop over them and prevent submission
    Array.prototype.slice.call(forms).forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
})();
// jQuery unobtrusive validation defauls

/*$.validator.setDefaults({

    errorClass: "",

    validClass: "",

    highlight: function (element, errorClass, validClass) {

        $(element).addClass("is-invalid").removeClass("is-valid");

        $(element.form).find("[data-valmsg-for=" + element.id + "]").addClass("invalid-feedback");

    },

    unhighlight: function (element, errorClass, validClass) {

        $(element).addClass("is-valid").removeClass("is-invalid");

        $(element.form).find("[data-valmsg-for=" + element.id + "]").removeClass("invalid-feedback");

    },

});*/

//Get the button
let mybutton = document.getElementById("btn-back-to-top");

// When the user scrolls down 20px from the top of the document, show the button
window.onscroll = function () {
    scrollFunction();
};

function scrollFunction() {
    if (
        document.body.scrollTop > 20 ||
        document.documentElement.scrollTop > 20
    ) {
        mybutton.style.display = "block";
    } else {
        mybutton.style.display = "none";
    }
}
// When the user clicks on the button, scroll to the top of the document
mybutton.addEventListener("click", backToTop);

function backToTop() {
    document.body.scrollTop = 0;
    document.documentElement.scrollTop = 0;
}

$(function() {
    $("body").tooltip({selector: '[data-mdb-toggle=tooltip]'});
});