/**
 * Main JavaScript file for Kelulusan SMK NU 1 Slawi website
 */
document.addEventListener('DOMContentLoaded', function() {
    // Check if verification form exists
    const verificationForm = document.getElementById('verification-form');
    if (verificationForm) {
        verificationForm.addEventListener('submit', function(e) {
            const examNumber = document.getElementById('exam_number').value;
            if (examNumber.trim() === '') {
                e.preventDefault();
                showAlert('Nomor ujian tidak boleh kosong.', 'danger');
            }
        });
    }
    
    // Check if dynamic data tables exist and initialize them
    if (typeof $.fn.DataTable !== 'undefined' && document.querySelector('.data-table')) {
        $('.data-table').DataTable({
            responsive: true,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
            }
        });
    }

    // Tooltip initialization
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Toggle password visibility
    const togglePassword = document.querySelector('.toggle-password');
    if (togglePassword) {
        togglePassword.addEventListener('click', function() {
            const passwordInput = document.querySelector(this.getAttribute('toggle'));
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                this.innerHTML = '<i class="fa fa-eye-slash"></i>';
            } else {
                passwordInput.type = 'password';
                this.innerHTML = '<i class="fa fa-eye"></i>';
            }
        });
    }
    
    // Print button functionality
    const printButtons = document.querySelectorAll('.btn-print');
    if (printButtons.length > 0) {
        printButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                window.print();
            });
        });
    }
    
    // Confirmation modal setup
    const confirmationModal = document.getElementById('confirmationModal');
    if (confirmationModal) {
        confirmationModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const action = button.getAttribute('data-action');
            const id = button.getAttribute('data-id');
            
            const modalTitle = confirmationModal.querySelector('.modal-title');
            const modalBody = confirmationModal.querySelector('.modal-body');
            const confirmButton = confirmationModal.querySelector('#confirmAction');
            
            if (action === 'delete') {
                modalTitle.textContent = 'Konfirmasi Hapus';
                modalBody.textContent = 'Apakah Anda yakin ingin menghapus data ini?';
                confirmButton.classList.remove('btn-success');
                confirmButton.classList.add('btn-danger');
                confirmButton.textContent = 'Hapus';
            } else if (action === 'activate') {
                modalTitle.textContent = 'Konfirmasi Aktivasi';
                modalBody.textContent = 'Apakah Anda yakin ingin mengaktifkan pengumuman kelulusan?';
                confirmButton.classList.remove('btn-danger');
                confirmButton.classList.add('btn-success');
                confirmButton.textContent = 'Aktifkan';
            } else if (action === 'deactivate') {
                modalTitle.textContent = 'Konfirmasi Deaktivasi';
                modalBody.textContent = 'Apakah Anda yakin ingin menonaktifkan pengumuman kelulusan?';
                confirmButton.classList.remove('btn-success');
                confirmButton.classList.add('btn-warning');
                confirmButton.textContent = 'Nonaktifkan';
            }
            
            confirmButton.setAttribute('data-id', id);
            confirmButton.setAttribute('data-action', action);
        });
        
        const confirmButton = document.getElementById('confirmAction');
        if (confirmButton) {
            confirmButton.addEventListener('click', function() {
                const action = this.getAttribute('data-action');
                const id = this.getAttribute('data-id');
                
                if (action === 'delete') {
                    // Redirect to delete action
                    window.location.href = `delete.php?id=${id}`;
                } else if (action === 'activate') {
                    // Redirect to activate action
                    window.location.href = 'config.php?action=activate';
                } else if (action === 'deactivate') {
                    // Redirect to deactivate action
                    window.location.href = 'config.php?action=deactivate';
                }
            });
        }
    }
});

/**
 * Show custom alert message
 * 
 * @param {string} message Message to display
 * @param {string} type Alert type (success, danger, warning, info)
 */
function showAlert(message, type = 'info') {
    const alertContainer = document.getElementById('alert-container');
    if (!alertContainer) return;
    
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    alertContainer.appendChild(alertDiv);
    
    // Auto dismiss after 5 seconds
    setTimeout(() => {
        alertDiv.classList.remove('show');
        setTimeout(() => {
            alertContainer.removeChild(alertDiv);
        }, 150);
    }, 5000);
}

/**
 * Format date to Indonesian format
 * 
 * @param {string} dateString Date string
 * @return {string} Formatted date
 */
function formatDate(dateString) {
    const months = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    
    const date = new Date(dateString);
    return `${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear()}`;
}
