$(document).ready(function () {
    // Function to load courses into select dropdowns
    function loadCourses(formId) {
        $.get('prerequisite_crud.php?action=courses', function (data) {
            const courseSelect = $(`#${formId} select[name="course_id"]`);
            const prereqSelect = $(`#${formId} select[name="prereq_course_id"]`);
            
            // Save current value if editing
            const currentCourse = courseSelect.val();
            const currentPrereq = prereqSelect.val();

            // Clear and add default option
            courseSelect.empty().append('<option value="">Select a Course</option>');
            prereqSelect.empty().append('<option value="">Select a Prerequisite</option>');

            data.forEach(c => {
                courseSelect.append(`<option value="${c.id}">${c.name}</option>`);
                prereqSelect.append(`<option value="${c.id}">${c.name}</option>`);
            });

            // Restore value and notify Select2
            if (currentCourse) courseSelect.val(currentCourse);
            if (currentPrereq) prereqSelect.val(currentPrereq);
            
            courseSelect.trigger('change');
            prereqSelect.trigger('change');
        });
    }

    loadCourses('addForm');

    // ✅ Initialize Select2 for Modals (Edit)
    $('#editModal').on('shown.bs.modal', function () {
        const modal = $(this);
        modal.find('select').each(function() {
            $(this).select2({
                theme: 'bootstrap-5',
                dropdownParent: modal,
                width: '100%',
                placeholder: 'Search for a course...'
            });
        });
    });

    // ✅ Initialize Select2 for the Inline Add Form
    // We check if the add form exists and is not inside a modal
    if ($('#addForm').length > 0 && $('#addForm').closest('.modal').length === 0) {
        $('#addForm select').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Search...'
        });
    }

    // Reset Add Form
    $('#addForm button[type="reset"]').on('click', function() {
         setTimeout(() => {
            $('#addForm select').val('').trigger('change');
         }, 50);
    });

    const table = $('#prereqTable').DataTable({
        ajax: {
            url: 'prerequisite_crud.php?action=read',
            dataSrc: ''
        },
        columns: [
            { 
                data: null,
                render: function (data) {
                    return `<div class="d-flex flex-column">
                                <span class="fw-bold">${data.course_code}</span>
                                <small class="text-muted">${data.course_title}</small>
                            </div>`;
                }
            },
            { 
                data: null,
                render: function (data) {
                    return `<div class="d-flex flex-column">
                                <span class="fw-bold">${data.prereq_code}</span>
                                <small class="text-muted">${data.prereq_title}</small>
                            </div>`;
                }
            },
            {
                data: null, orderable: false,
                render: function (data) {
                    return `
                        <button class="btn btn-sm btn-warning editBtn" 
                            data-course-id="${data.course_id}" 
                            data-prereq-id="${data.prereq_course_id}">Edit</button>
                        <button class="btn btn-sm btn-danger deleteBtn"
                            data-course-id="${data.course_id}" 
                            data-prereq-id="${data.prereq_course_id}">Remove</button>
                    `;
                }
            }
        ]
    });

    // Add Prerequisite
    $('#addForm').on('submit', function (e) {
        e.preventDefault();
        $.post('prerequisite_crud.php?action=create', $(this).serialize(), null, 'json')
            .done(function (res) {
                if (res.status === 'duplicate') {
                    Swal.fire('Duplicate', 'This prerequisite relationship already exists.', 'error');
                } else if (res.status === 'self_prereq') {
                    Swal.fire('Invalid', 'A course cannot be its own prerequisite.', 'warning');
                } else if (res.status === 'success') {
                    $('#addForm')[0].reset();
                    $('#addForm select').val('').trigger('change'); 
                    
                    table.ajax.reload(null, false);
                    Swal.fire('Success', 'Prerequisite added successfully!', 'success');
                } else {
                    Swal.fire('Error', 'Failed to add prerequisite.', 'error');
                }
            }).fail(() => Swal.fire('Error', 'A server error occurred.', 'error'));
    });

    // Open Edit Modal
    $(document).on('click', '.editBtn', function () {
        const modal = $('#editModal');
        const courseId = $(this).data('course-id');
        const prereqId = $(this).data('prereq-id');

        modal.find('[name="old_course_id"]').val(courseId);
        modal.find('[name="old_prereq_course_id"]').val(prereqId);
        
        loadCourses('editForm');
        
        // Set values after a short delay to allow options to populate
        setTimeout(() => {
            modal.find('[name="course_id"]').val(courseId).trigger('change');
            modal.find('[name="prereq_course_id"]').val(prereqId).trigger('change');
        }, 300);

        modal.modal('show');
    });

    // Update Prerequisite
    $('#editForm').on('submit', function (e) {
        e.preventDefault();
        $.post('prerequisite_crud.php?action=update', $(this).serialize(), null, 'json')
            .done(function (res) {
                if (res.status === 'updated') {
                    $('#editModal').modal('hide');
                    table.ajax.reload(null, false);
                    Swal.fire('Updated', 'Prerequisite updated successfully!', 'success');
                } else {
                    Swal.fire('Error', 'Failed to update prerequisite.', 'error');
                }
            }).fail(() => Swal.fire('Error', 'A server error occurred.', 'error'));
    });

    // Delete Prerequisite
    $(document).on('click', '.deleteBtn', function () {
        const courseId = $(this).data('course-id');
        const prereqId = $(this).data('prereq-id');
        
        Swal.fire({
            title: 'Archive this prerequisite?',
            text: 'This will mark the relationship as inactive.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, archive it',
            confirmButtonColor: '#d33'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('prerequisite_crud.php?action=delete', { course_id: courseId, prereq_course_id: prereqId }, null, 'json')
                    .done(function (res) {
                        if (res.status === 'deleted') {
                            table.ajax.reload(null, false);
                            Swal.fire('Archived', 'The prerequisite link has been archived.', 'success');
                        } else {
                            Swal.fire('Error', 'Failed to archive the link.', 'error');
                        }
                    }).fail(() => Swal.fire('Error', 'A server error occurred.', 'error'));
            }
        });
    });
});