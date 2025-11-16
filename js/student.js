$(document).ready(function () {
    // Function to load programs into a select dropdown
    function loadPrograms(select) {
        $.get('student_crud.php?action=programs', function (data) {
            select.empty().append('<option value="">Select Program</option>');
            data.forEach(function (prog) {
                select.append(`<option value="${prog.id}">${prog.name}</option>`);
            });
        });
    }

    loadPrograms($('#addForm select[name="program_id"]'));

    // ✅ Initialize Select2
    $('#addModal, #editModal').on('shown.bs.modal', function () {
        const modal = $(this);
        modal.find('select').each(function() {
            $(this).select2({
                theme: 'bootstrap-5',
                dropdownParent: modal,
                width: '100%',
                placeholder: 'Select option...'
            });
        });
    });

    // Reset Select2
    $('#addModal').on('hidden.bs.modal', function () {
        $('#addForm')[0].reset();
        $('#addForm select').val('').trigger('change');
    });

    const table = $('#studentTable').DataTable({
        ajax: {
            url: 'student_crud.php?action=read',
            dataSrc: ''
        },
        order: [[1, 'asc']], 
        columns: [
            { data: 'student_no' },
            { data: null, render: data => `${data.last_name}, ${data.first_name}` },
            { data: 'email' }, { data: 'gender' }, { data: 'birthdate' },
            { data: 'year_level' }, { data: 'program_name' },
            {
                data: null, orderable: false,
                render: data => `<button class="btn btn-sm btn-warning editBtn" data-json='${JSON.stringify(data)}'>Edit</button>
                                 <button class="btn btn-sm btn-danger deleteBtn" data-id="${data.student_id}">Delete</button>`
            }
        ]
    });

    // Program Filter
    $('#customFilter').html(`<select id="programFilter" class="form-select form-select-sm" style="width:250px;"><option value="">Filter by Program</option></select>`);
    $.get('student_crud.php?action=programs', data => data.forEach(item => $('#programFilter').append(`<option value="${item.name}">${item.name}</option>`)));
    $('#programFilter').on('change', function() { table.column(6).search(this.value ? `^${this.value}$` : '', true, false).draw(); });

    // Add Student
    $('#addForm').on('submit', function (e) {
        e.preventDefault();
        $.post('student_crud.php?action=create', $(this).serialize(), null, 'json')
            .done(function (res) {
                if (res.status === 'duplicate') {
                    Swal.fire('Duplicate', 'A student with this Student No. or Email already exists.', 'error');
                } else if (res.status === 'success') {
                    $('#addModal').modal('hide');
                    table.ajax.reload(null, false);
                    Swal.fire('Success', 'Student added successfully!', 'success');
                } else {
                    Swal.fire('Error', 'Failed to add student.', 'error');
                }
            }).fail(() => Swal.fire('Error', 'A server error occurred.', 'error'));
    });

    // Open Edit Modal
    $(document).on('click', '.editBtn', function () {
        const data = $(this).data('json');
        const modal = $('#editModal');
        modal.find('[name="student_id"]').val(data.student_id);
        modal.find('[name="student_no"]').val(data.student_no);
        modal.find('[name="last_name"]').val(data.last_name);
        modal.find('[name="first_name"]').val(data.first_name);
        modal.find('[name="email"]').val(data.email);
        modal.find('[name="birthdate"]').val(data.birthdate);
        modal.find('[name="year_level"]').val(data.year_level);
        
        const programSelect = modal.find('select[name="program_id"]');
        loadPrograms(programSelect);
        
        // Set values and trigger change for Select2
        setTimeout(() => { 
            programSelect.val(data.program_id).trigger('change'); 
            modal.find('select[name="gender"]').val(data.gender).trigger('change');
        }, 250);

        modal.modal('show');
    });

    // Update Student
    $('#editForm').on('submit', function (e) {
        e.preventDefault();
        $.post('student_crud.php?action=update', $(this).serialize(), null, 'json')
            .done(function (res) {
                if (res.status === 'duplicate') {
                    Swal.fire('Duplicate', 'Another student with this Student No. or Email already exists.', 'error');
                } else if (res.status === 'updated') {
                    $('#editModal').modal('hide');
                    table.ajax.reload(null, false);
                    Swal.fire('Updated', 'Student updated successfully!', 'success');
                } else {
                    Swal.fire('Error', 'Failed to update student.', 'error');
                }
            }).fail(() => Swal.fire('Error', 'A server error occurred.', 'error'));
    });

    // Soft Delete Student
    $(document).on('click', '.deleteBtn', function () {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Archive this student?',
            text: 'This will mark the student as inactive.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, archive it'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('student_crud.php?action=delete', { student_id: id }, null, 'json')
                    .done(function (res) {
                        if (res.status === 'deleted') {
                            table.ajax.reload(null, false);
                            Swal.fire('Archived', 'Student has been archived.', 'success');
                        } else {
                            Swal.fire('Error', 'Failed to archive student.', 'error');
                        }
                    }).fail(() => Swal.fire('Error', 'A server error occurred.', 'error'));
            }
        });
    });
});