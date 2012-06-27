
$(document).ready(function() {
    /*
    $('.autocrud-button').button();
    $('.autocrud-button-desc').button({icons: { primary: 'ui-icon-triangle-1-s'}, text: false});
    $('.autocrud-button-asc').button({icons: { primary: 'ui-icon-triangle-1-n'}, text: false});
    */
    $('.autocrud-form').ajaxForm({
        //alert('saved');
        success: function (data) {
            $('.autocrud-form').append('<div class="autocrud-saved ui-state-highlight"><span style="float: left; margin-right: .3em;" class="ui-icon ui-icon-info"></span>saved</div>');
            $('.autocrud-saved').delay(500).fadeOut(function() { $(this).remove(); });
            
            $('.autocrud-form input[type="file"]').each(function () {
                var filename = $(this).val();
                if (filename) {
                    $(this).prev('input').val(filename);
                }
            });
        }
    });
    
    $('input.autocrud-datepicker').datepicker({ 
        dateFormat: "<?php echo $this->dateFormatDatePicker; ?>",
        onSelect: updateHiddenDateField 
    });
    
    $('input.autocrud-datetimepicker').datetimepicker({ 
        dateFormat: "<?php echo $this->dateFormatDatePicker; ?>",
        timeFormat: 'hh:mm:ss' ,
        onSelect: updateHiddenDateField
    });
    
    function updateHiddenDateField(dateText, inst) {
        var val = $(this).val();
        var time = val.substring(val.lastIndexOf(' '));
        var dateString = '';
        if ($(this).is('.autocrud-datepicker')) {
            dateString = inst.currentYear + '-' + inst.currentMonth + '-' + inst.currentDay;
        } else if ($(this).is('.autocrud-datetimepicker')) {
            dateString = inst.currentYear + '-' + inst.currentMonth + '-' + inst.currentDay
                       + ' ' + time;
        } else if ($(this).is('.autocrud-timepicker')) {
            
        }
        $(this).prev('input').val(dateString);
    }
});