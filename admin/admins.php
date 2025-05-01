<?php
require_once '../includes/functions.php';

// Hanya superadmin yang boleh mengakses
if (!is_logged_in() || !is_superadmin()) {
    redirect('/kelulusan2025/admin/login.php');
}

$page_title = 'Kelola Akun Admin';
include '../includes/header.php';

// Handle add, edit, delete logic di sini (akan dilengkapi setelah file dasar dibuat)
?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Kelola Akun Admin</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="/kelulusan2025/admin/">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Kelola Admin</li>
            </ol>
        </nav>
    </div>
    <div class="card">
        <div class="card-body">
            <a href="#" class="btn btn-primary mb-3"><i class="fas fa-plus me-1"></i>Tambah Admin</a>
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data admin akan diisi di sini -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
