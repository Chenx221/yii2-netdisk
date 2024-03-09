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
        } else {
            $.post('index.php?r=user%2Fremove-two-factor', function () {
                location.reload();
            });
        }
    });
    $('#totpSetupModal').on('hidden.bs.modal', function () {
        $('#totp-enabled').prop('checked', false);
    });
    $('#useDarkTheme').change(function () {
        var darkMode = this.checked ? 1 : 0;
        $.post('index.php?r=user%2Fset-theme', {dark_mode: darkMode}, function () {
            location.reload();
        });
    });

    $('#followSystemTheme').change(function () {
        $('#useDarkTheme').prop('checked', false);
        var darkMode = this.checked ? 2 : 0;
        $.post('index.php?r=user%2Fset-theme', {dark_mode: darkMode}, function () {
            location.reload();
        });
    });
});

document.querySelector('.avatar-container').addEventListener('click', function () {
    $('#avatarModal').modal('show');
});