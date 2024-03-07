$(document).ready(function () {
    $('#deleteConfirm').change(function () {
        if (this.checked) {
            $('#deleteButton').prop('disabled', false);
        } else {
            $('#deleteButton').prop('disabled', true);
        }
    });
    $('#totp-enabled').change(function () {
        if (this.checked) {
            $('#totpSetupModal').modal('show');
        }else {
            $.post('index.php?r=user%2Fremove-two-factor', function () {
                location.reload();
            });
        }
    });
    $('#totpSetupModal').on('hidden.bs.modal', function () {
        $('#totp-enabled').prop('checked', false);
    });

});