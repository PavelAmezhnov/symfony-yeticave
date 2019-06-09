$(document).ready(function() {
    $('.datepicker input:first-child').datepicker({
        dateFormat: 'dd.mm.yy',
        minDate: +1,
        maxDate: +30
    }).datepicker('setDate', '+1d');
    
    var now = new Date();
    $('.datepicker select:first-child').val(now.getHours().toString());
    $('.datepicker select:last-child').val(now.getMinutes().toString());
});