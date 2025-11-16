$(document).ready(function () {
    // Function to load dropdowns for Add/Edit forms
    function loadFormDropdowns(formId) {
        const studentSelect = $(`#${formId} select[name="student_id"]`);
        const sectionSelect = $(`#${formId} select[name="section_id"]`);

        $.get('enrollment_crud.php?action=students', function (data) {
            studentSelect.empty().append('<option value="">Select Student</option>');
            data.forEach(item => studentSelect.append(`<option value="${item.id}">${item.name}</option>`));
        });
        
        // Load all sections initially
        loadSections(sectionSelect);
    }

    function loadSections(selectElement, courseId = 0) {
        let url = 'enrollment_crud.php?action=sections';
        if (courseId > 0) {
            url += `&course_id=${courseId}`;
        }

        $.get(url, function (data) {
            selectElement.empty().append('<option value="">Select Section</option>');
            if (data.length === 0) {
                selectElement.append('<option value="" disabled>No sections available for this subject</option>');
            }
            data.forEach(item => selectElement.append(`<option value="${item.id}">${item.name}</option>`));
            
            // ✅ Notify Select2 that the options have changed
            selectElement.trigger('change'); 
        });
    }

    // Load Courses for the "Irregular Student" filter
    function loadCoursesForFilter() {
        $.get('enrollment_crud.php?action=courses', function (data) {
            const courseSelect = $('#courseSelect');
            courseSelect.empty().append('<option value="">-- Search & Select Subject --</option>');
            data.forEach(item => courseSelect.append(`<option value="${item.id}">${item.name}</option>`));
        });
    }

    // Event Listener for Course Filter (Irregular Logic)
    $('#courseSelect').on('change', function() {
        const courseId = $(this).val();
        const sectionSelect = $('#addForm select[name="section_id"]');
        loadSections(sectionSelect, courseId);
    });

    // Initial Loads
    loadFormDropdowns('addForm');
    loadCoursesForFilter();

    // ✅ Initialize Select2 for ALL dropdowns when Modal Opens
    $('#addModal').on('shown.bs.modal', function () {
        // 1. Subject Filter
        $('#courseSelect').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#addModal'),
            placeholder: 'Search for a subject...',
            allowClear: true,
            width: '100%'
        });

        // 2. Student Dropdown
        $('#addForm select[name="student_id"]').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#addModal'),
            placeholder: 'Search for a student...',
            width: '100%'
        });

        // 3. ✅ NEW: Section Dropdown
        $('#addForm select[name="section_id"]').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#addModal'),
            placeholder: 'Search for a section...',
            width: '100%'
        });
    });

    // Reset Select2 when modal closes
    $('#addModal').on('hidden.bs.modal', function () {
        $('#courseSelect').val('').trigger('change'); 
        $('#addForm')[0].reset(); 
        
        // Reset Student and Section Select2 visuals
        $('#addForm select[name="student_id"]').val('').trigger('change');
        $('#addForm select[name="section_id"]').val('').trigger('change');
    });

    const table = $('#enrollmentTable').DataTable({
        ajax: {
            url: 'enrollment_crud.php?action=read',
            dataSrc: ''
        },
        columns: [
            { data: 'student_name' },
            { 
                data: null,
                render: function(data) {
                    return `<strong>${data.section_code}</strong><br><small class="text-muted">${data.course_title || ''}</small>`;
                }
            },
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
    $.get('enrollment_crud.php?action=sections', data => data.forEach(item => $('#sectionFilter').append(`<option value="${item.name.split(' - ')[0]}">${item.name}</option>`)));

    $('#studentFilter').on('change', function() { table.column(0).search(this.value ? `^${this.value}$` : '', true, false).draw(); });
    $('#sectionFilter').on('change', function() { table.column(1).search(this.value ? `^${this.value}` : '', true, false).draw(); });

    // Add Enrollment
    $('#addForm').on('submit', function (e) {
        e.preventDefault();
        $.post('enrollment_crud.php?action=create', $(this).serialize(), null, 'json')
            .done(function (res) {
                if (res.status === 'duplicate') {
                    Swal.fire('Duplicate', 'This student is already enrolled in this section.', 'error');
                } else if (res.status === 'success') {
                    $('#addModal').modal('hide');
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