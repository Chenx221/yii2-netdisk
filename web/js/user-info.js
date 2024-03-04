$(document).ready(function() {
    $('#deleteConfirm').change(function() {
        if(this.checked) {
            $('#deleteButton').prop('disabled', false);
        } else {
            $('#deleteButton').prop('disabled', true);
        }
    });
});