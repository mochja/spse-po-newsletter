$(document).ready(function() {
    $('input[data-dateinput-type]').dateinput({
        datetime: {
            dateFormat: 'd.m.yy',
            timeFormat: 'H:mm',
            options: { // nastaven� datepickeru pro konkr�tn� typ
                changeYear: true
            }
        },
        'datetime-local': {
            dateFormat: 'd.m.yy',
            timeFormat: 'H:mm'
        },
        date: {
            dateFormat: 'd.m.yy'
        },
        month: {
            dateFormat: 'MM yy'
        },
        week: {
            dateFormat: "w. 't?den' yy"
        },
        time: {
            timeFormat: 'H:mm'
        },
        options: { // glob�ln� nastaven� datepickeru
            closeText: "Close"
        }
    });
});