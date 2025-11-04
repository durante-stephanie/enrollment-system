$(document).ready(function () {
  const table = $('#deptTable').DataTable({
    ajax: {
      url: '../../modules/department/department_crud.php?action=read',
      dataSrc: ''
    },
    columns: [
      { data: 'dept_code' },
      { data: 'dept_name' },
      {
        data: null,
        orderable: false,
        render: function (data) {
          return `
            <button class="btn btn-sm btn-warning editBtn"
              data-id="${data.dept_id}"
              data-code="${data.dept_code}"
              data-name="${data.dept_name}">
              Edit
            </button>
            <button class="btn btn-sm btn-danger deleteBtn" data-id="${data.dept_id}">
              Delete
            </button>
          `;
        }
      }
    ],
    order: [[0, 'desc']],
    responsive: true
  });

  // 🔹 Department Name Filter (similar to course page)
  $('#customFilter').html(`
    <select id="deptFilter" class="form-select form-select-sm" style="width:250px; display:inline-block;">
      <option value="">All Department</option>
    </select>
  `);
  
  // Populate the filter dropdown using the same data source as the table
  $.get('../../modules/department/department_crud.php?action=read', function (data) {
    // Sort data alphabetically by department name
    data.sort((a, b) => a.dept_name.localeCompare(b.dept_name));
    data.forEach(function (dept) {
      $('#deptFilter').append(`<option value="${dept.dept_name}">${dept.dept_name}</option>`);
    });
  });

  // Event listener for the filter
  $('#deptFilter').on('change', function () {
    let val = this.value;
    // Perform an exact match search on the 'Name' column (index 1)
    if (val) {
      table.column(1).search('^' + val + '$', true, false).draw();
    } else {
      table.column(1).search('').draw();
    }
  });

  // 🔹 Add Department
  $('#addForm').on('submit', function (e) {
    e.preventDefault();
    $.post('../../modules/department/department_crud.php?action=create', $(this).serialize(), null, 'json')
      .done(function (res) {
        if (res.status === 'duplicate') {
          Swal.fire('Duplicate', 'This department code or name already exists!', 'error');
        } else if (res.status === 'success') {
          $('#addModal').modal('hide');
          $('#addForm')[0].reset();
          table.ajax.reload(null, false); // Reload table data
          Swal.fire('Success', 'Department added successfully!', 'success');
        } else {
          Swal.fire('Error', 'Failed to add department. ' + (res.message || ''), 'error');
        }
      })
      .fail(function () {
        Swal.fire('Error', 'A server error occurred while adding the department.', 'error');
      });
  });

  // 🔹 Open Edit Modal
  $(document).on('click', '.editBtn', function () {
    const modal = $('#editModal');
    modal.find('input[name="dept_id"]').val($(this).data('id'));
    modal.find('input[name="dept_code"]').val($(this).data('code'));
    modal.find('input[name="dept_name"]').val($(this).data('name'));
    modal.modal('show');
  });

  // 🔹 Update Department
  $('#editForm').on('submit', function (e) {
    e.preventDefault();
    $.post('../../modules/department/department_crud.php?action=update', $(this).serialize(), null, 'json')
      .done(function (res) {
        if (res.status === 'duplicate') {
          Swal.fire('Duplicate', 'Another department with this code or name already exists!', 'error');
        } else if (res.status === 'updated') {
          $('#editModal').modal('hide');
          table.ajax.reload(null, false);
          Swal.fire('Updated', 'Department updated successfully!', 'success');
        } else {
          Swal.fire('Error', 'Failed to update department. ' + (res.message || ''), 'error');
        }
      })
      .fail(function () {
        Swal.fire('Error', 'A server error occurred while updating the department.', 'error');
      });
  });

  // 🔹 Soft Delete Department
  $(document).on('click', '.deleteBtn', function () {
    const id = $(this).data('id');
    Swal.fire({
      title: 'Archive this department?',
      text: 'This will soft delete the department.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, archive it'
    }).then((result) => {
      if (result.isConfirmed) {
        $.post('../../modules/department/department_crud.php?action=delete', { dept_id: id }, null, 'json')
          .done(function (res) {
            if (res.status === 'deleted') {
              table.ajax.reload(null, false);
              Swal.fire('Archived', 'Department has been archived.', 'success');
            } else {
              Swal.fire('Error', 'Failed to archive department. ' + (res.message || ''), 'error');
            }
          })
          .fail(function () {
            Swal.fire('Error', 'A server error occurred while archiving.', 'error');
          });
      }
    });
  });
});