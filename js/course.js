$(document).ready(function () {
    // 🔹 Load departments into dropdown
    function loadDepartments(select) {
        $.get('course_crud.php?action=departments', function (data) {
            const currentVal = select.val();
            select.empty().append('<option value="">Select Department</option>');
            data.forEach(function (dept) {
                select.append(`<option value="${dept.id}">${dept.name}</option>`);
            });
            select.val(currentVal);
        });
    }

    loadDepartments($('#addForm select[name="dept_id"]'));

    // 🔹 Initialize DataTable
    const table = $('#courseTable').DataTable({
        ajax: {
            url: 'course_crud.php?action=read', // Correct path
            dataSrc: ''
        },
        columns: [
            { data: 'course_code' }, { data: 'course_title' }, { data: 'units' },
            { data: 'lecture_hours' }, { data: 'lab_hours' }, { data: 'dept_name' },
            {
                data: null, orderable: false,
                render: function (row) {
                    return `
                        <button class="btn btn-sm btn-warning editBtn"
                            data-id="${row.course_id}" data-code="${row.course_code}"
                            data-title="${row.course_title}" data-units="${row.units}"
                            data-lecture="${row.lecture_hours}" data-lab="${row.lab_hours}"
                            data-dept="${row.dept_id}">Edit</button>
                        <button class="btn btn-sm btn-danger deleteBtn" data-id="${row.course_id}">Delete</button>
                    `;
                }
            }
        ],
        order: [[0, 'desc']],
        responsive: true
    });

    // 🔹 Department filter
    $('#customFilter').html(`<select id="deptFilter" class="form-select form-select-sm" style="width:200px;"><option value="">All Departments</option></select>`);
    $.get('course_crud.php?action=departments', function (data) {
        data.forEach(function (dept) {
            $('#deptFilter').append(`<option value="${dept.name}">${dept.name}</option>`);
        });
    });
    $('#deptFilter').on('change', function () {
        table.column(5).search(this.value ? `^${this.value}$` : '', true, false).draw();
    });

    // 🔹 Add Course
    $('#addForm').on('submit', function (e) {
        e.preventDefault();
        $.post('course_crud.php?action=create', $(this).serialize(), null, 'json')
            .done(function (res) {
                // ✅ Handle different responses from the server
                if (res.status === 'duplicate') {
                    Swal.fire('Duplicate', 'A course with this code or title already exists.', 'error');
                } else if (res.status === 'success') {
                    $('#addModal').modal('hide');
                    $('#addForm')[0].reset();
                    table.ajax.reload(null, false);
                    Swal.fire('Success', 'Course added successfully!', 'success');
                } else {
                    Swal.fire('Error', 'Failed to add course.', 'error');
                }
            })
            .fail(function () {
                Swal.fire('Error', 'A server error occurred.', 'error');
            });
    });

    // 🔹 Open Edit Modal
    $(document).on('click', '.editBtn', function () {
        const modal = $('#editModal');
        const btn = $(this);
        modal.find('[name="course_id"]').val(btn.data('id'));
        modal.find('[name="course_code"]').val(btn.data('code'));
        modal.find('[name="course_title"]').val(btn.data('title'));
        modal.find('[name="units"]').val(btn.data('units'));
        modal.find('[name="lecture_hours"]').val(btn.data('lecture'));
        modal.find('[name="lab_hours"]').val(btn.data('lab'));
        const deptSelect = modal.find('select[name="dept_id"]');
        loadDepartments(deptSelect);
        setTimeout(() => { deptSelect.val(btn.data('dept')); }, 200);
        modal.modal('show');
    });

    // 🔹 Update Course
    $('#editForm').on('submit', function (e) {
        e.preventDefault();
        $.post('course_crud.php?action=update', $(this).serialize(), null, 'json')
            .done(function (res) {
                if (res.status === 'duplicate') {
                    Swal.fire('Duplicate', 'Another course with this code or title already exists.', 'error');
                } else if (res.status === 'updated') {
                    $('#editModal').modal('hide');
                    table.ajax.reload(null, false);
                    Swal.fire('Updated', 'Course updated successfully!', 'success');
                } else {
                    Swal.fire('Error', 'Failed to update course.', 'error');
                }
            })
            .fail(function () {
                Swal.fire('Error', 'A server error occurred.', 'error');
            });
    });

    // 🔹 Soft Delete Course
    $(document).on('click', '.deleteBtn', function () {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Archive this course?',
            text: 'This will soft delete the course.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, archive it'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('course_crud.php?action=delete', { course_id: id }, null, 'json')
                    .done(function () {
                        table.ajax.reload(null, false);
                        Swal.fire('Archived', 'Course has been archived.', 'success');
                    })
                    .fail(function () {
                        Swal.fire('Error', 'Failed to archive course.', 'error');
                    });
            }
        });
    });
});