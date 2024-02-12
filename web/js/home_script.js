$(document).on('click', '.rename-btn', function () {
    var relativePath = $(this).attr('value');
    var fileName = $(this).closest('tr').find('td:first').text().trim();
    $('#renameRelativePath').val(relativePath);
    $('#renameform-newname').val(fileName);
    $('#renameModal').modal('show');
})
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