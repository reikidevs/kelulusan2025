<?php
require_once '../includes/functions.php';

// Hanya superadmin yang boleh mengakses
if (!is_logged_in() || !is_superadmin()) {
    redirect('/kelulusan2025/admin/login.php');
}

$page_title = 'Pengaturan Website';
include '../includes/header.php';

// Handle pengaturan website di sini (akan dilengkapi setelah file dasar dibuat)
?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Pengaturan Website</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="/kelulusan2025/admin/">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Pengaturan</li>
            </ol>
        </nav>
    </div>
    <div class="card">
        <div class="card-body">
            <!-- Form pengaturan website akan diisi di sini -->
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
