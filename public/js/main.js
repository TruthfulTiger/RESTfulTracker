$(function() {
    $('body').tooltip({
        selector: '[data-toggle="tooltip"]',
        placement: 'auto'
    });

    $('<input>').attr({
        type: 'text',
        id: 'hptrap',
        name: 'hptrap'
    }).appendTo('form');
});

//select all checkboxes - https://www.sanwebe.com/2014/01/how-to-select-all-deselect-checkboxes-jquery
$("#select_all").change(function () {  //"select all" change
    var status = this.checked; // "select all" checked status
    $('.select_all').each(function () { //iterate all listed checkbox items
        this.checked = status; //change ".checkbox" checked status
    });
});

$("#select_all3").change(function () {  //"select all" change
    var status = this.checked; // "select all" checked status
    $('.select_all3').each(function () { //iterate all listed checkbox items
        this.checked = status; //change ".checkbox" checked status
    });
});

$("#select_all4").change(function () {  //"select all" change
    var status = this.checked; // "select all" checked status
    $('.select_all4').each(function () { //iterate all listed checkbox items
        this.checked = status; //change ".checkbox" checked status
    });
});

$('.select_all').change(function () { //".checkbox" change
    //uncheck "select all", if one of the listed checkbox item is unchecked
    if (this.checked == false) { //if this item is unchecked
        $("#select_all")[0].checked = false; //change "select all" checked status to false
    }

    //check "select all" if all checkbox items are checked
    if ($('.select_all:checked').length == $('.select_all').length) {
        $("#select_all")[0].checked = true; //change "select all" checked status to true
    }
});

$('.select_all3').change(function () { //".checkbox" change
    //uncheck "select all", if one of the listed checkbox item is unchecked
    if (this.checked == false) { //if this item is unchecked
        $("#select_all3")[0].checked = false; //change "select all" checked status to false
    }

    //check "select all" if all checkbox items are checked
    if ($('.select_all3:checked').length == $('.select_all3').length) {
        $("#select_all3")[0].checked = true; //change "select all" checked status to true
    }
});

$('.select_all4').change(function () { //".checkbox" change
    //uncheck "select all", if one of the listed checkbox item is unchecked
    if (this.checked == false) { //if this item is unchecked
        $("#select_all4")[0].checked = false; //change "select all" checked status to false
    }

    //check "select all" if all checkbox items are checked
    if ($('.select_all4:checked').length == $('.select_all4').length) {
        $("#select_all4")[0].checked = true; //change "select all" checked status to true
    }
});

// Simoleons formatter
let simoleons = wNumb({
    decimals: 0,
    thousand: ',',
    prefix: '§'
});

// Delete confirmation dialog
function confirmDelete(url) {
    Swal.fire({
        titleText: 'Are you sure?',
        text: 'You will not be able to recover this data!',
        icon: 'warning',
        showConfirmButton: true,
        confirmButtonAriaLabel: 'Confirm',
        showCancelButton: true,
        cancelButtonAriaLabel: 'Cancel',
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire(
                    'Deleted!',
                    'Item has been deleted.',
                    'success'
                )
                .then(window.document.location = url)
        }
    })
}