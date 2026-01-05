$(document).ready(function() {
    
    // Handle Backup Download
    $('#btnBackup').click(function() {
        // Directly navigate to the PHP script triggers the download
        window.location.href = 'backup_crud.php?action=backup';
    });

    // Handle Restore
    $('#restoreForm').on('submit', function(e) {
        e.preventDefault();
        
        // Confirmation Alert
        Swal.fire({
            title: 'Are you sure?',
            text: "This will overwrite current data! Make sure you are on a fresh install or have a backup.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, Restore it!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Restoring Database...',
                    text: 'Please wait, this may take a moment.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                let formData = new FormData(this);

                $.ajax({
                    url: 'backup_crud.php?action=restore',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire(
                                'Restored!',
                                'Database has been restored successfully.',
                                'success'
                            ).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'An unexpected server error occurred.', 'error');
                    }
                });
            }
        });
    });
});