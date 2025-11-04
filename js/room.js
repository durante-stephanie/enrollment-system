$(document).ready(function () {
    const table = $('#roomTable').DataTable({
        ajax: {
            url: 'room_crud.php?action=read', // CORRECTED PATH
            dataSrc: ''
        },
        columns: [
            { data: 'building' },
            { data: 'room_code' },
            { data: 'capacity' },
            {
                data: null, orderable: false,
                render: function (data) {
                    return `
                        <button class="btn btn-sm btn-warning editBtn"
                            data-id="${data.room_id}"
                            data-building="${data.building}"
                            data-code="${data.room_code}"
                            data-capacity="${data.capacity}">Edit</button>
                        <button class="btn btn-sm btn-danger deleteBtn" data-id="${data.room_id}">Delete</button>
                    `;
                }
            }
        ]
    });

    // 🔹 Building Filter
    $('#customFilter').html(`<select id="buildingFilter" class="form-select form-select-sm" style="width:250px;"><option value="">Filter by Building</option></select>`);
    $.get('room_crud.php?action=buildings', data => data.forEach(item => $('#buildingFilter').append(`<option value="${item.building}">${item.building}</option>`)));
    $('#buildingFilter').on('change', function() { table.column(0).search(this.value ? `^${this.value}$` : '', true, false).draw(); });

    // 🔹 Add Room
    $('#addForm').on('submit', function (e) {
        e.preventDefault();
        $.post('room_crud.php?action=create', $(this).serialize(), null, 'json')
            .done(function (res) {
                if (res.status === 'duplicate') {
                    Swal.fire('Duplicate', 'A room with this code already exists.', 'error');
                } else if (res.status === 'success') {
                    $('#addModal').modal('hide');
                    $('#addForm')[0].reset();
                    table.ajax.reload(null, false);
                    Swal.fire('Success', 'Room added successfully!', 'success');
                } else {
                    Swal.fire('Error', 'Failed to add room.', 'error');
                }
            }).fail(() => Swal.fire('Error', 'A server error occurred.', 'error'));
    });

    // 🔹 Open Edit Modal
    $(document).on('click', '.editBtn', function () {
        const modal = $('#editModal');
        const btn = $(this);
        modal.find('[name="room_id"]').val(btn.data('id'));
        modal.find('[name="building"]').val(btn.data('building'));
        modal.find('[name="room_code"]').val(btn.data('code'));
        modal.find('[name="capacity"]').val(btn.data('capacity'));
        modal.modal('show');
    });

    // 🔹 Update Room
    $('#editForm').on('submit', function (e) {
        e.preventDefault();
        $.post('room_crud.php?action=update', $(this).serialize(), null, 'json')
            .done(function (res) {
                 if (res.status === 'duplicate') {
                    Swal.fire('Duplicate', 'Another room with this code already exists.', 'error');
                } else if (res.status === 'updated') {
                    $('#editModal').modal('hide');
                    table.ajax.reload(null, false);
                    Swal.fire('Updated', 'Room updated successfully!', 'success');
                } else {
                    Swal.fire('Error', 'Failed to update room.', 'error');
                }
            }).fail(() => Swal.fire('Error', 'A server error occurred.', 'error'));
    });

    // 🔹 Soft Delete Room
    $(document).on('click', '.deleteBtn', function () {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Archive this room?',
            text: 'This will mark the room as inactive.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, archive it'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('room_crud.php?action=delete', { room_id: id }, null, 'json')
                    .done(function (res) {
                        if (res.status === 'deleted') {
                            table.ajax.reload(null, false);
                            Swal.fire('Archived', 'Room has been archived.', 'success');
                        } else {
                            Swal.fire('Error', 'Failed to archive room.', 'error');
                        }
                    }).fail(() => Swal.fire('Error', 'A server error occurred.', 'error'));
            }
        });
    });
});