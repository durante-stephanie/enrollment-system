$(document).ready(function () {
    // Function to load all dropdowns for the Add/Edit forms
    function loadFormDropdowns(formId) {
        const selects = {
            course_id: 'courses',
            term_id: 'terms',
            instructor_id: 'instructors',
            room_id: 'rooms'
        };
        for (const [field, action] of Object.entries(selects)) {
            $.get(`section_crud.php?action=${action}`, function (data) {
                const select = $(`#${formId} select[name="${field}"]`);
                const currentVal = select.val();
                select.empty().append(`<option value="">Select...</option>`);
                data.forEach(item => select.append(`<option value="${item.id}">${item.name}</option>`));
                select.val(currentVal);
            });
        }
    }

    loadFormDropdowns('addForm');

    const table = $('#sectionTable').DataTable({
        ajax: {
            url: 'section_crud.php?action=read', // CORRECTED PATH
            dataSrc: ''
        },
        columns: [
            { data: 'section_code' }, { data: 'course_code' }, { data: 'term_code' },
            { data: 'instructor_name' }, { data: 'room_code' },
            { data: null, render: d => `${d.day_pattern} ${d.start_time.substring(0,5)}–${d.end_time.substring(0,5)}` },
            { data: 'max_capacity' },
            {
                data: null, orderable: false,
                render: d => `
                    <button class="btn btn-sm btn-warning editBtn" data-json='${JSON.stringify(d)}'>Edit</button>
                    <button class="btn btn-sm btn-danger deleteBtn" data-id="${d.section_id}">Delete</button>
                `
            }
        ]
    });

    // 🔹 Filters for Term and Course
    $('#customFilter').html(`
        <select id="termFilter" class="form-select form-select-sm" style="width:200px; display:inline-block; margin-right: 5px;"><option value="">Filter by Term</option></select>
        <select id="courseFilter" class="form-select form-select-sm" style="width:200px; display:inline-block;"><option value="">Filter by Course</option></select>
    `);
    $.get('section_crud.php?action=terms', data => data.forEach(item => $('#termFilter').append(`<option value="${item.name}">${item.name}</option>`)));
    $.get('section_crud.php?action=courses', data => data.forEach(item => $('#courseFilter').append(`<option value="${item.name}">${item.name}</option>`)));

    $('#termFilter').on('change', function() { table.column(2).search(this.value ? `^${this.value}$` : '', true, false).draw(); });
    $('#courseFilter').on('change', function() { table.column(1).search(this.value ? `^${this.value}$` : '', true, false).draw(); });

    // 🔹 Add Section
    $('#addForm').on('submit', function (e) {
        e.preventDefault();
        $.post('section_crud.php?action=create', $(this).serialize(), null, 'json')
            .done(function (res) {
                if (res.status === 'duplicate') {
                    Swal.fire('Duplicate', 'This section code already exists for the selected term.', 'error');
                } else if (res.status === 'success') {
                    $('#addModal').modal('hide');
                    $('#addForm')[0].reset();
                    table.ajax.reload(null, false);
                    Swal.fire('Success', 'Section added successfully!', 'success');
                } else {
                    Swal.fire('Error', 'Failed to add section.', 'error');
                }
            }).fail(() => Swal.fire('Error', 'A server error occurred.', 'error'));
    });

    // 🔹 Open Edit Modal
    $(document).on('click', '.editBtn', function () {
        const data = $(this).data('json');
        const modal = $('#editModal');
        modal.find('[name="section_id"]').val(data.section_id);
        modal.find('[name="section_code"]').val(data.section_code);
        modal.find('[name="day_pattern"]').val(data.day_pattern);
        modal.find('[name="start_time"]').val(data.start_time);
        modal.find('[name="end_time"]').val(data.end_time);
        modal.find('[name="max_capacity"]').val(data.max_capacity);
        
        loadFormDropdowns('editForm');
        setTimeout(() => {
            modal.find('[name="course_id"]').val(data.course_id);
            modal.find('[name="term_id"]').val(data.term_id);
            modal.find('[name="instructor_id"]').val(data.instructor_id);
            modal.find('[name="room_id"]').val(data.room_id);
        }, 250);
        modal.modal('show');
    });

    // 🔹 Update Section
    $('#editForm').on('submit', function (e) {
        e.preventDefault();
        $.post('section_crud.php?action=update', $(this).serialize(), null, 'json')
            .done(function (res) {
                if (res.status === 'updated') {
                    $('#editModal').modal('hide');
                    table.ajax.reload(null, false);
                    Swal.fire('Updated', 'Section updated successfully!', 'success');
                } else {
                    Swal.fire('Error', 'Failed to update section.', 'error');
                }
            }).fail(() => Swal.fire('Error', 'A server error occurred.', 'error'));
    });

    // 🔹 Soft Delete Section
    $(document).on('click', '.deleteBtn', function () {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Archive this section?',
            text: 'This will mark the section as inactive.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, archive it'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('section_crud.php?action=delete', { section_id: id }, null, 'json')
                    .done(function (res) {
                        if (res.status === 'deleted') {
                            table.ajax.reload(null, false);
                            Swal.fire('Archived', 'Section has been archived.', 'success');
                        } else {
                            Swal.fire('Error', 'Failed to archive section.', 'error');
                        }
                    }).fail(() => Swal.fire('Error', 'A server error occurred.', 'error'));
            }
        });
    });
});