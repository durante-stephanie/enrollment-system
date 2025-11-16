$(document).ready(function () {
    // Function to load departments into a select dropdown
    function loadDepartments(select) {
        $.get('instructor_crud.php?action=departments', function (data) {
            select.empty().append('<option value="">Select Department</option>');
            data.forEach(function (dept) {
                select.append(`<option value="${dept.dept_id}">${dept.dept_name}</option>`);
            });
            // ✅ Notify Select2
            select.trigger('change');
        });
    }

    loadDepartments($('#addForm select[name="dept_id"]'));

    // Initialize Select2
    $('#addModal, #editModal').on('shown.bs.modal', function () {
        const modal = $(this);
        modal.find('select').each(function() {
            $(this).select2({
                theme: 'bootstrap-5',
                dropdownParent: modal,
                width: '100%',
                placeholder: 'Select department...'
            });
        });
    });

    // Reset Select2
    $('#addModal').on('hidden.bs.modal', function () {
        $('#addForm')[0].reset();
        $('#addForm select').val('').trigger('change');
    });

    const table = $('#instructorTable').DataTable({
        ajax: {
            url: 'instructor_crud.php?action=read',
            dataSrc: ''
        },
        columns: [
            { data: null, render: data => `${data.last_name}, ${data.first_name}` },
            { data: 'email' },
            { data: 'dept_name' },
            {
                data: null, orderable: false,
                render: function(data) {
                    return `
                        <button class="btn btn-sm btn-warning editBtn"
                            data-id="${data.instructor_id}"
                            data-last="${data.last_name}"
                            data-first="${data.first_name}"
                            data-email="${data.email}"
                            data-dept="${data.dept_id}">Edit</button>
                        <button class="btn btn-sm btn-danger deleteBtn" data-id="${data.instructor_id}">Delete</button>
                    `;
                }
            }
        ]
    });
    
    // Department Filter
    $('#customFilter').html(`<select id="deptFilter" class="form-select form-select-sm" style="width:250px;"><option value="">Filter by Department</option></select>`);
    $.get('instructor_crud.php?action=departments', data => data.forEach(dept => $('#deptFilter').append(`<option value="${dept.dept_name}">${dept.dept_name}</option>`)));
    $('#deptFilter').on('change', function() { table.column(2).search(this.value ? `^${this.value}$` : '', true, false).draw(); });

    // Add Instructor
    $('#addForm').on('submit', function (e) {
        e.preventDefault();
        $.post('instructor_crud.php?action=create', $(this).serialize(), null, 'json')
            .done(function (res) {
                if (res.status === 'duplicate') {
                    Swal.fire('Duplicate', 'An instructor with this email already exists.', 'error');
                } else if (res.status === 'success') {
                    $('#addModal').modal('hide');
                    table.ajax.reload(null, false);
                    Swal.fire('Success', 'Instructor added successfully!', 'success');
                } else {
                    Swal.fire('Error', 'Failed to add instructor.', 'error');
                }
            }).fail(() => Swal.fire('Error', 'A server error occurred.', 'error'));
    });

    // Open Edit Modal
    $(document).on('click', '.editBtn', function () {
        const modal = $('#editModal');
        const btn = $(this);
        modal.find('[name="instructor_id"]').val(btn.data('id'));
        modal.find('[name="last_name"]').val(btn.data('last'));
        modal.find('[name="first_name"]').val(btn.data('first'));
        modal.find('[name="email"]').val(btn.data('email'));
        
        const deptSelect = modal.find('select[name="dept_id"]');
        loadDepartments(deptSelect);
        setTimeout(() => { deptSelect.val(btn.data('dept')).trigger('change'); }, 300);
        
        modal.modal('show');
    });

    // Update Instructor
    $('#editForm').on('submit', function (e) {
        e.preventDefault();
        $.post('instructor_crud.php?action=update', $(this).serialize(), null, 'json')
            .done(function (res) {
                if (res.status === 'duplicate') {
                    Swal.fire('Duplicate', 'Another instructor with this email already exists.', 'error');
                } else if (res.status === 'updated') {
                    $('#editModal').modal('hide');
                    table.ajax.reload(null, false);
                    Swal.fire('Updated', 'Instructor updated successfully!', 'success');
                } else {
                    Swal.fire('Error', 'Failed to update instructor.', 'error');
                }
            }).fail(() => Swal.fire('Error', 'A server error occurred.', 'error'));
    });

    // Soft Delete Instructor
    $(document).on('click', '.deleteBtn', function () {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Archive this instructor?',
            text: 'This will mark the instructor as inactive.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, archive it'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('instructor_crud.php?action=delete', { instructor_id: id }, null, 'json')
                    .done(function (res) {
                        if (res.status === 'deleted') {
                            table.ajax.reload(null, false);
                            Swal.fire('Archived', 'Instructor has been archived.', 'success');
                        } else {
                            Swal.fire('Error', 'Failed to archive instructor.', 'error');
                        }
                    }).fail(() => Swal.fire('Error', 'A server error occurred.', 'error'));
            }
        });
    });
});