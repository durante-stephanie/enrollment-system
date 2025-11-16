$(document).ready(function () {
    // Load departments into a select dropdown
    function loadDepartments(select) {
        $.get('program_crud.php?action=departments', function (data) {
            select.empty().append('<option value="">Select Department</option>');
            data.forEach(function (dept) {
                select.append(`<option value="${dept.id}">${dept.name}</option>`);
            });
        });
    }

    // Load departments for the "Add" form initially
    loadDepartments($('#addForm select[name="dept_id"]'));

    // ✅ Initialize Select2
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

    const table = $('#programTable').DataTable({
        ajax: {
            url: 'program_crud.php?action=read',
            dataSrc: ''
        },
        columns: [
            { data: 'program_code' },
            { data: 'program_name' },
            { data: 'dept_name' },
            {
                data: null,
                orderable: false,
                render: function (data) {
                    return `
                        <button class="btn btn-sm btn-warning editBtn"
                            data-id="${data.program_id}"
                            data-code="${data.program_code}"
                            data-name="${data.program_name}"
                            data-dept="${data.dept_id}">
                            Edit
                        </button>
                        <button class="btn btn-sm btn-danger deleteBtn" data-id="${data.program_id}">
                            Delete
                        </button>
                    `;
                }
            }
        ],
        order: [[1, 'asc']],
        responsive: true
    });
    
    // Department filter logic
    $('#customFilter').html(`<select id="deptFilter" class="form-select form-select-sm" style="width:250px; display:inline-block;"><option value="">Filter by Department</option></select>`);
    $.get('program_crud.php?action=departments', function (data) {
        data.forEach(function (dept) {
            $('#deptFilter').append(`<option value="${dept.name}">${dept.name}</option>`);
        });
    });
    $('#deptFilter').on('change', function () {
        let val = this.value;
        table.column(2).search(val ? '^' + val + '$' : '', true, false).draw();
    });

    // Add Program
    $('#addForm').on('submit', function (e) {
        e.preventDefault();
        $.post('program_crud.php?action=create', $(this).serialize(), null, 'json')
            .done(function (res) {
                if (res.status === 'duplicate') {
                    Swal.fire('Duplicate', 'This program code or name already exists!', 'error');
                } else if (res.status === 'success') {
                    $('#addModal').modal('hide');
                    table.ajax.reload(null, false);
                    Swal.fire('Success', 'Program added successfully!', 'success');
                } else {
                    Swal.fire('Error', 'Failed to add program.', 'error');
                }
            }).fail(function () {
                Swal.fire('Error', 'A server error occurred.', 'error');
            });
    });

    // Open Edit Modal
    $(document).on('click', '.editBtn', function () {
        const modal = $('#editModal');
        modal.find('input[name="program_id"]').val($(this).data('id'));
        modal.find('input[name="program_code"]').val($(this).data('code'));
        modal.find('input[name="program_name"]').val($(this).data('name'));
        
        const deptSelect = modal.find('select[name="dept_id"]');
        loadDepartments(deptSelect);
        setTimeout(() => { deptSelect.val($(this).data('dept')).trigger('change'); }, 200);
        
        modal.modal('show');
    });

    // Update Program
    $('#editForm').on('submit', function (e) {
        e.preventDefault();
        $.post('program_crud.php?action=update', $(this).serialize(), null, 'json')
            .done(function (res) {
                if (res.status === 'duplicate') {
                    Swal.fire('Duplicate', 'Another program with this code or name already exists!', 'error');
                } else if (res.status === 'updated') {
                    $('#editModal').modal('hide');
                    table.ajax.reload(null, false);
                    Swal.fire('Updated', 'Program updated successfully!', 'success');
                } else {
                    Swal.fire('Error', 'Failed to update program.', 'error');
                }
            }).fail(function () {
                Swal.fire('Error', 'A server error occurred.', 'error');
            });
    });

    // Soft Delete Program
    $(document).on('click', '.deleteBtn', function () {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Archive this program?', text: 'This will soft delete the program.', icon: 'warning',
            showCancelButton: true, confirmButtonText: 'Yes, archive it'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('program_crud.php?action=delete', { program_id: id }, null, 'json')
                    .done(function (res) {
                        if (res.status === 'deleted') {
                            table.ajax.reload(null, false);
                            Swal.fire('Archived', 'Program has been archived.', 'success');
                        } else {
                            Swal.fire('Error', 'Failed to archive program.', 'error');
                        }
                    }).fail(function () {
                        Swal.fire('Error', 'A server error occurred.', 'error');
                    });
            }
        });
    });
});