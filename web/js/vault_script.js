var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl)
});
$(document).on('click', '.download-btn', function () {
    window.location.href = $(this).attr('value');
});
$(document).on('click', '.delete-btn', function () {
    var relativePath = $(this).attr('value');
    $('#deleteRelativePath').val(relativePath);
    $('#deleteModal').modal('show');
});
$(document).on('click', '.file-upload-btn', function () {
    $('#file-input').click();
});
$('#file-input').on('change', function () {
    uploadFiles(this.files);
});
$('#folder-input').on('change', function () {
    uploadFiles(this.files);
});
$(document).on('click', '.refresh-btn', function () {
    window.location.reload();
});
$(document).on('click', '.single-download-btn', function () {
    var relativePath = $('.select-item:checked').first().data('relativePath');
    window.location.href = 'index.php?r=vault%2Fdownload&relativePath=' + encodeURIComponent(relativePath);
});

function uploadFiles(files) {
    $('#progress-bar').show();
    var formData = new FormData();
    for (var i = 0; i < files.length; i++) {
        formData.append('files[]', files[i]);
    }
    formData.append('targetDir', $('#target-dir').val());
    formData.append('_csrf', $('meta[name="csrf-token"]').attr('content'));

    var xhr = new XMLHttpRequest();
    xhr.upload.onprogress = function (event) {
        if (event.lengthComputable) {
            var percentComplete = event.loaded / event.total * 100;
            $('#progress-bar .progress-bar').css('width', percentComplete + '%').text(Math.round(percentComplete) + '%');
        }
    };
    xhr.onload = function () {
        if (xhr.status !== 200) {
            alert('An error occurred during the upload.');
        }
        window.location.reload();
    };
    xhr.open('POST', 'index.php?r=vault%2Fupload');
    xhr.send(formData);
}

var dropArea = document.getElementById('drop-area');
dropArea.addEventListener('dragover', function (event) {
    event.preventDefault();
});
dropArea.addEventListener('drop', function (event) {
    event.preventDefault();

    var items = event.dataTransfer.items;
    var files = [];
    for (var i = 0; i < items.length; i++) {
        var item = items[i];
        files.push(item.getAsFile());
    }
    uploadFiles(files);
    dropArea.classList.remove('dragging');
});
dropArea.addEventListener('dragenter', function (event) {
    event.preventDefault();
    dropArea.classList.add('dragging');
});

dropArea.addEventListener('dragleave', function (event) {
    event.preventDefault();
    if (!dropArea.contains(event.relatedTarget)) {
        dropArea.classList.remove('dragging');
    }
});
document.getElementById('select-all').addEventListener('change', function () {
    var checkboxes = document.querySelectorAll('.select-item');
    for (var i = 0; i < checkboxes.length; i++) {
        checkboxes[i].checked = this.checked;
        checkboxes[i].closest('tr').classList.toggle('selected', this.checked);
    }
});
var itemCheckboxes = document.querySelectorAll('.select-item');
for (var i = 0; i < itemCheckboxes.length; i++) {
    itemCheckboxes[i].addEventListener('change', function () {
        if (!this.checked) {
            document.getElementById('select-all').checked = false;
        } else {
            var allChecked = true;
            for (var j = 0; j < itemCheckboxes.length; j++) {
                if (!itemCheckboxes[j].checked) {
                    allChecked = false;
                    break;
                }
            }
            document.getElementById('select-all').checked = allChecked;
        }
        this.closest('tr').classList.toggle('selected', this.checked);
    });
}
document.addEventListener('keydown', function (event) {
    if (event.ctrlKey && event.key === 'a') {
        event.preventDefault();
        var checkboxes = document.querySelectorAll('.select-item');
        var allChecked = Array.from(checkboxes).every(checkbox => checkbox.checked);
        for (var i = 0; i < checkboxes.length; i++) {
            checkboxes[i].checked = !allChecked;
            checkboxes[i].closest('tr').classList.toggle('selected', !allChecked);
        }
        updateButtons();
    }
    if (event.ctrlKey && event.key === 'd') {
        event.preventDefault();
        $('tr.selected').removeClass('selected').find('input[type="checkbox"]').prop('checked', false);
        $('#select-all').prop('checked', false);
        updateButtons();
    }
});
$(document).on('click', 'tr', function (event) {
    if ($(event.target).is('input[type="checkbox"]') || $(event.target).is('a')) {
        return;
    }
    var checkboxes = document.querySelectorAll('.select-item');
    var checkedCount = Array.from(checkboxes).filter(checkbox => checkbox.checked).length;
    var allChecked = checkedCount >= 2;
    if (!event.ctrlKey) {
        $('tr.selected').not(this).removeClass('selected').find('input[type="checkbox"]').prop('checked', false);
    }
    if ((!allChecked) || (allChecked && event.ctrlKey)) {
        $(this).toggleClass('selected');
        var checkbox = $(this).children(':first-child').find('input[type="checkbox"]');
        checkbox.prop('checked', !checkbox.prop('checked'));
    }
    updateButtons();
});

function updateButtons() {
    var checkboxes = $('.select-item:checked');
    var count = checkboxes.length;
    var isSingleFile = count === 1 && !checkboxes.first().data('isDirectory');
    $('.single-download-btn').toggle(isSingleFile);
    $('.batch-delete-btn').toggle(count >= 1);
}

$(document).on('change', '.select-item', updateButtons);
$(document).ready(function () {
    updateButtons();
    $('tr').contextmenu(function (e) {
        e.preventDefault();
        if ($(e.target).is('button') || $(e.target).is('i') || $(e.target).is('a')) {
            e.preventDefault();
            return;
        }
        var clickedElement = $(this);
        if (!clickedElement.hasClass('selected')) {
            $('tr.selected').removeClass('selected').find('input[type="checkbox"]').prop('checked', false);
            clickedElement.addClass('selected');
            var checkbox = clickedElement.children(':first-child').find('input[type="checkbox"]');
            checkbox.prop('checked', true);
            updateButtons();
        }
        $('#option-download').toggle($('.single-download-btn').css('display') !== 'none');
        $('#option-batch-delete').toggle($('.batch-delete-btn').css('display') !== 'none');
        $('#option-refresh').toggle($('.refresh-btn').css('display') !== 'none');
        $('#contextMenu').css({
            display: "block",
            left: e.pageX,
            top: e.pageY
        }).addClass('show');
        $('#contextMenu .dropdown-menu').addClass('show');
    });
    $('#contextMenu a').off('click').on('click', function (e) {
        e.preventDefault();
        var clickedMenuItem = $(this).attr('id');
        switch (clickedMenuItem) {
            case 'option-download':
                $('.single-download-btn').click();
                break;
            case 'option-batch-delete':
                $('.batch-delete-btn').click();
                break;
            case 'option-refresh':
                $('.refresh-btn').click();
                break;
        }
        $('#contextMenu').hide().removeClass('show');
    });
    $(document).click(function () {
        $('#contextMenu').hide().removeClass('show');
        $('#contextMenu .dropdown-menu').removeClass('show');
    });
});
const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]')
const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl))