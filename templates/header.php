
<style type="text/css">
    @import url('style/autocrud.css');
    @import url('libraries/jquery-ui-1.8.21.custom/css/ui-lightness/jquery-ui-1.8.21.custom.css');
    @import url('libraries/jquery-ui-timepicker-addon/jquery-ui-timepicker-addon.css');
</style>

<script type="text/javascript" src="libraries/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="libraries/jquery.form.js"></script>
<script type="text/javascript" src="libraries/jquery-ui-1.8.21.custom/js/jquery-ui-1.8.21.custom.min.js"></script>
<script type="text/javascript" src="libraries/jquery-ui-timepicker-addon/jquery-ui-timepicker-addon.js"></script>

<script type="text/javascript">
$(document).ready(function() {
    $('.autocrud-form').ajaxForm({
        //alert('saved');
        success: function (data) {
            $('.autocrud-form').append('<p>saved</p>');
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
        console.log(dateText);
        console.log(inst);
        console.log($(this).datepicker('getDate'));
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

</script>

<form method="get"><input type="hidden" name="action" value="create" /><button>Create new</button></form>
<form method="get"><button>View all</button></form>
