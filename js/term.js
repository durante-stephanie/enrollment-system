$(document).ready(function () {
    const table = $('#termTable').DataTable({
        ajax: {
            url: 'term_crud.php?action=read', // CORRECTED PATH
            dataSrc: ''
        },
        columns: [
            { data: 'term_code' },
            { data: 'start_date' },
            { data: 'end_date' },
            {
                data: null, orderable: false,
                render: function (data) {
                    return `
                        <button class="btn btn-sm btn-warning editBtn"
                            data-id="${data.term_id}"
                            data-code="${data.term_code}"
                            data-start="${data.start_date}"
                            data-end="${data.end_date}">Edit</button>
                        <button class="btn btn-sm btn-danger deleteBtn" data-id="${data.term_id}">Delete</button>
                    `;
                }
            }
        ]
    });

    // 🔹 Add Term
    $('#addForm').on('submit', function (e) {
        e.preventDefault();
        $.post('term_crud.php?action=create', $(this).serialize(), null, 'json')
            .done(function (res) {
                if (res.status === 'duplicate') {
                    Swal.fire('Duplicate', 'A term with this code already exists.', 'error');
                } else if (res.status === 'success') {
                    $('#addModal').modal('hide');
                    $('#addForm')[0].reset();
                    table.ajax.reload(null, false);
                    Swal.fire('Success', 'Term added successfully!', 'success');
                } else {
                    Swal.fire('Error', 'Failed to add term.', 'error');
                }
            }).fail(() => Swal.fire('Error', 'A server error occurred.', 'error'));
    });

    // 🔹 Open Edit Modal
    $(document).on('click', '.editBtn', function () {
        const modal = $('#editModal');
        const btn = $(this);
        modal.find('[name="term_id"]').val(btn.data('id'));
        modal.find('[name="term_code"]').val(btn.data('code'));
        modal.find('[name="start_date"]').val(btn.data('start'));
        modal.find('[name="end_date"]').val(btn.data('end'));
        modal.modal('show');
    });

    // 🔹 Update Term
    $('#editForm').on('submit', function (e) {
        e.preventDefault();
        $.post('term_crud.php?action=update', $(this).serialize(), null, 'json')
            .done(function (res) {
                 if (res.status === 'duplicate') {
                    Swal.fire('Duplicate', 'Another term with this code already exists.', 'error');
                } else if (res.status === 'updated') {
                    $('#editModal').modal('hide');
                    table.ajax.reload(null, false);
                    Swal.fire('Updated', 'Term updated successfully!', 'success');
                } else {
                    Swal.fire('Error', 'Failed to update term.', 'error');
                }
            }).fail(() => Swal.fire('Error', 'A server error occurred.', 'error'));
    });

    // 🔹 Soft Delete Term
    $(document).on('click', '.deleteBtn', function () {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Archive this term?',
            text: 'This will mark the term as inactive.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, archive it'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('term_crud.php?action=delete', { term_id: id }, null, 'json')
                    .done(function (res) {
                        if (res.status === 'deleted') {
                            table.ajax.reload(null, false);
                            Swal.fire('Archived', 'Term has been archived.', 'success');
                        } else {
                            Swal.fire('Error', 'Failed to archive term.', 'error');
                        }
                    }).fail(() => Swal.fire('Error', 'A server error occurred.', 'error'));
            }
        });
    });
});