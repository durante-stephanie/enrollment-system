$(document).ready(function () {
        
    function loadStudents(selectElement) {
        $.get('enrollment_crud.php?action=students', function (data) {
            const currentVal = selectElement.val();
            selectElement.empty().append('<option value="">Select Student</option>');
            data.forEach(item => selectElement.append(`<option value="${item.id}">${item.name}</option>`));
            if(currentVal) selectElement.val(currentVal);
            selectElement.trigger('change');
        });
    }

    function loadSections(selectElement, courseId = 0) {
        let url = 'enrollment_crud.php?action=sections';
        // Only filter if courseId is provided (for Irregular)
        if (courseId > 0) {
            url += `&course_id=${courseId}`;
        }

        $.get(url, function (data) {
            const currentVal = selectElement.val();
            selectElement.empty().append('<option value="">Select Section</option>');
            if (data.length === 0 && courseId > 0) {
                selectElement.append('<option value="" disabled>No sections found for this subject</option>');
            } else if (data.length === 0) {
                selectElement.append('<option value="" disabled>No sections available</option>');
            }
            data.forEach(item => selectElement.append(`<option value="${item.id}">${item.name}</option>`));
            
            if(currentVal) selectElement.val(currentVal);
            selectElement.trigger('change');
        });
    }

    function loadCoursesForFilter() {
        $.get('enrollment_crud.php?action=courses', function (data) {
            const courseSelect = $('#courseSelect');
            courseSelect.empty().append('<option value="">-- Select Subject to Filter Sections --</option>');
            data.forEach(item => courseSelect.append(`<option value="${item.id}">${item.name}</option>`));
            courseSelect.trigger('change');
        });
    }


    $('#addModal').on('shown.bs.modal', function () {
        $('#addForm select[name="student_id"]').select2({ theme: 'bootstrap-5', dropdownParent: $(this), placeholder: 'Search for a student...', width: '100%'});
        $('#courseSelect').select2({ theme: 'bootstrap-5', dropdownParent: $(this), placeholder: 'Search for a subject...', allowClear: true, width: '100%'});
        $('#addForm select[name="section_id"]').select2({ theme: 'bootstrap-5', dropdownParent: $(this), placeholder: 'Search for a section...', width: '100%'});
        $('#addForm select[name="status"]').select2({ theme: 'bootstrap-5', dropdownParent: $(this), width: '100%', minimumResultsForSearch: Infinity });
        $('#enrollmentType').select2({ theme: 'bootstrap-5', dropdownParent: $(this), width: '100%', minimumResultsForSearch: Infinity });

        // Load data
        loadStudents($('#addForm select[name="student_id"]'));
        loadCoursesForFilter();
        loadSections($('#addForm select[name="section_id"]'), 0); // Load all sections by default
    });

    $('#addModal').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
        $(this).find('select').val(null).trigger('change');
        $('#irregularFields').hide();
        $('#enrollmentType').val('regular').trigger('change');
    });
    
    $('#editModal').on('shown.bs.modal', function () {
        $(this).find('select[name="status"]').select2({ theme: 'bootstrap-5', dropdownParent: $(this), width: '100%', minimumResultsForSearch: Infinity });
    });

    // --- Logic for Irregular Student ---
    
    $('#enrollmentType').on('change', function() {
        const type = $(this).val();
        if (type === 'irregular') {
            $('#irregularFields').slideDown();
            // In irregular, reset sections to show all, filtered by course
            loadSections($('#addForm select[name="section_id"]'), 0); 
        } else {
            $('#irregularFields').slideUp();
             // In regular, just show all sections
            loadSections($('#addForm select[name="section_id"]'), 0);
        }
        $('#courseSelect').val(null).trigger('change');
        $('#addForm select[name="section_id"]').val(null).trigger('change');
    });

    $('#courseSelect').on('change', function() {
        const courseId = $(this).val();
        // This only fires in irregular mode, filtering sections by the selected course
        loadSections($('#addForm select[name="section_id"]'), courseId);
    });

    // --- DataTable Initialization ---
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
                    return `<div class="d-flex flex-column">
                                <span class="fw-bold">${data.section_code}</span>
                                <small class="text-muted">${data.course_title || ''}</small>
                            </div>`;
                }
            },
            { data: 'status' },
            { data: 'letter_grade' },
            {
                data: null, orderable: false, width: '80px',
                render: function(data) {
                    return `
                        <button class="btn btn-sm btn-warning editBtn" data-json='${JSON.stringify(data)}' title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger deleteBtn" data-id="${data.enrollment_id}" title="Archive">
                            <i class="fas fa-trash"></i>
                        </button>
                    `;
                }
            }
        ]
    });

    // --- Form Handlers ---
    $('#addForm').on('submit', function (e) {
        e.preventDefault();
        
        // ✅ START: Determine action based on enrollment type
        const enrollmentType = $('#enrollmentType').val();
        let actionUrl = 'enrollment_crud.php?action=create'; // Default to irregular
        
        if (enrollmentType === 'regular') {
            actionUrl = 'enrollment_crud.php?action=create_block';
        }
        // ✅ END: Determine action

        $.post(actionUrl, $(this).serialize(), null, 'json')
            .done(function (res) {
                if (res.status === 'duplicate') {
                    Swal.fire('Duplicate', 'This student is already enrolled in this specific section.', 'error');
                } else if (res.status === 'prereq_failed') {
                    const missingCourses = (res.missing || []).join(', ');
                    Swal.fire({
                        icon: 'error',
                        title: 'Prerequisite(s) Not Met',
                        // ✅ Updated message
                        text: `Student has not completed prerequisites for one or more courses: ${missingCourses}`
                    });
                
                // ✅ START: Handle new block success
                } else if (res.status === 'success_block') {
                    $('#addModal').modal('hide');
                    table.ajax.reload(null, false);
                    Swal.fire('Success', `Enrolled student in ${res.enrolled_count} sections successfully!`, 'success');
                // ✅ END: Handle new block success

                } else if (res.status === 'success') {
                    $('#addModal').modal('hide');
                    table.ajax.reload(null, false);
                    Swal.fire('Success', 'Enrollment added successfully!', 'success');
                } else {
                    Swal.fire('Error', 'Failed to add enrollment. ' + (res.message || ''), 'error');
                }
            }).fail(() => Swal.fire('Error', 'A server error occurred.', 'error'));
    });

    //Edit Modal Logic
    $(document).on('click', '.editBtn', function () {
        const data = $(this).data('json');
        const modal = $('#editModal');
        
        modal.find('[name="enrollment_id"]').val(data.enrollment_id);
        
        modal.find('[name="status"]').val(data.status).trigger('change');
        modal.find('[name="final_grade"]').val(data.letter_grade);
        
        const studentSelect = modal.find('select[name="student_id"]');
        studentSelect.empty().append(`<option value="${data.student_id}">${data.student_name}</option>`);
        studentSelect.val(data.student_id);

        const sectionSelect = modal.find('select[name="section_id"]');
        sectionSelect.empty().append(`<option value="${data.section_id}">${data.section_name}</option>`);
        sectionSelect.val(data.section_id);
        
        modal.modal('show');
    });

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