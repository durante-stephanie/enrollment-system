$(document).ready(function () {
    // Function to load dropdowns for Add/Edit forms
    function loadFormDropdowns(formId) {
        const studentSelect = $(`#${formId} select[name="student_id"]`);
        const sectionSelect = $(`#${formId} select[name="section_id"]`);

        // CORRECTED PATH: Relative to the index.php file
        $.get('enrollment_crud.php?action=students', function (data) {
            studentSelect.empty().append('<option value="">Select Student</option>');
            data.forEach(item => studentSelect.append(`<option value="${item.id}">${item.name}</option>`));
        });
        // CORRECTED PATH
        $.get('enrollment_crud.php?action=sections', function (data) {
            sectionSelect.empty().append('<option value="">Select Section</option>');
            data.forEach(item => sectionSelect.append(`<option value="${item.id}">${item.name}</option>`));
        });
    }

    // Load dropdowns for the "Add" form initially
    loadFormDropdowns('addForm');

    const table = $('#enrollmentTable').DataTable({
        ajax: {
            url: 'enrollment_crud.php?action=read', // CORRECTED PATH
            dataSrc: ''
        },
        columns: [
            { data: 'student_name' },
            { data: 'section_code' },
            { data: 'status' },
            { data: 'letter_grade' },
            {
                data: null, orderable: false,
                render: function(data) {
                    return `
                        <button class="btn btn-sm btn-warning editBtn" 
                            data-id="${data.enrollment_id}"
                            data-student-id="${data.student_id}"
                            data-section-id="${data.section_id}"
                            data-status="${data.status}"
                            data-grade="${data.letter_grade}">Edit</button>
                        <button class="btn btn-sm btn-danger deleteBtn" data-id="${data.enrollment_id}">Delete</button>
                    `;
                }
            }
        ]
    });

    // Filters for Student and Section
    $('#customFilter').html(`
        <select id="sectionFilter" class="form-select form-select-sm" style="width:200px; display:inline-block;"><option value="">Filter by Section</option></select>
    `);
    // CORRECTED PATHS
    $.get('enrollment_crud.php?action=sections', data => data.forEach(item => $('#sectionFilter').append(`<option value="${item.name.split(' - ')[0]}">${item.name}</option>`)));

    $('#studentFilter').on('change', function() { table.column(0).search(this.value ? `^${this.value}$` : '', true, false).draw(); });
    $('#sectionFilter').on('change', function() { table.column(1).search(this.value ? `^${this.value}$` : '', true, false).draw(); });

    // Add Enrollment
    $('#addForm').on('submit', function (e) {
        e.preventDefault();
        // CORRECTED PATH
        $.post('enrollment_crud.php?action=create', $(this).serialize(), null, 'json')
            .done(function (res) {
                if (res.status === 'duplicate') {
                    Swal.fire('Duplicate', 'This student is already enrolled in this section.', 'error');
                } else if (res.status === 'success') {
                    $('#addModal').modal('hide');
                    $('#addForm')[0].reset();
                    table.ajax.reload(null, false);
                    Swal.fire('Success', 'Enrollment added successfully!', 'success');
                } else {
                    Swal.fire('Error', 'Failed to add enrollment.', 'error');
                }
            }).fail(() => Swal.fire('Error', 'A server error occurred.', 'error'));
    });

    // Open Edit Modal
    $(document).on('click', '.editBtn', function () {
        const modal = $('#editModal');
        const btn = $(this);
        modal.find('[name="enrollment_id"]').val(btn.data('id'));
        modal.find('[name="status"]').val(btn.data('status'));
        modal.find('[name="final_grade"]').val(btn.data('grade'));
        loadFormDropdowns('editForm');
        setTimeout(() => {
            modal.find('[name="student_id"]').val(btn.data('student-id'));
            modal.find('[name="section_id"]').val(btn.data('section-id'));
        }, 250);
        modal.modal('show');
    });

    // Update Enrollment
    $('#editForm').on('submit', function (e) {
        e.preventDefault();
        // CORRECTED PATH
        $.post('enrollment_crud.php?action=update', $(this).serialize(), null, 'json')
            .done(function (res) {
                if (res.status === 'updated') {
                    $('#editModal').modal('hide');
                    table.ajax.reload(null, false);
                    Swal.fire('Updated', 'Enrollment updated successfully!', 'success');
                } else {
                    Swal.fire('Error', 'Failed to update enrollment.', 'error');
                }
            }).fail(() => Swal.fire('Error', 'A server error occurred.', 'error'));
    });

    // Soft Delete Enrollment
    $(document).on('click', '.deleteBtn', function () {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Archive this enrollment?',
            text: 'This will mark the enrollment as inactive.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, archive it'
        }).then((result) => {
            if (result.isConfirmed) {
                // CORRECTED PATH
                $.post('enrollment_crud.php?action=delete', { enrollment_id: id }, null, 'json')
                    .done(function (res) {
                        if (res.status === 'deleted') {
                            table.ajax.reload(null, false);
                            Swal.fire('Archived', 'Enrollment has been archived.', 'success');
                        } else {
                            Swal.fire('Error', 'Failed to archive enrollment.', 'error');
                        }
                    }).fail(() => Swal.fire('Error', 'A server error occurred.', 'error'));
            }
        });
    });
});