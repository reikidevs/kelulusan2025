    </main>

    <!-- Footer -->
    <footer class="footer-section">
        <!-- Main Footer -->
        <div class="main-footer py-5">
            <div class="container">
                <div class="row g-4">
                    <!-- Column 1: School Info -->
                    <div class="col-lg-4 col-md-6">
                        <div class="d-flex align-items-center mb-4">
                            <img src="<?php echo base_url('/assets/images/logo/logo-skanu.png'); ?>" alt="<?php echo $school_name; ?>" height="60" class="me-3">
                            <div>
                                <h4 class="text-white fw-bold mb-1"><?php echo $school_name; ?></h4>
                                <p class="text-white-50 fs-6 mb-0">Sekolah Vokasi Pusat Keunggulan dan Binaan Daihatsu</p>
                            </div>
                        </div>
                        <p class="text-white-50 mb-2">Website untuk pengecekan hasil kelulusan siswa <?php echo $school_name; ?> secara online dengan mudah dan cepat.</p>
                        <div class="d-flex gap-2 mb-4">
                            <a href="#" class="btn btn-sm btn-outline-light rounded-circle social-icon" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="btn btn-sm btn-outline-light rounded-circle social-icon" title="Instagram"><i class="fab fa-instagram"></i></a>
                            <a href="#" class="btn btn-sm btn-outline-light rounded-circle social-icon" title="YouTube"><i class="fab fa-youtube"></i></a>
                            <a href="#" class="btn btn-sm btn-outline-light rounded-circle social-icon" title="Website"><i class="fas fa-globe"></i></a>
                        </div>
                    </div>

                    <!-- Column 2: Quick Links -->
                    <div class="col-lg-3 col-md-6 ps-lg-5">
                        <h5 class="text-white mb-4 footer-heading">Info Sekolah</h5>
                        <ul class="list-unstyled footer-links">
                            <li><a href="https://smknu1slawi.sch.id"><i class="fas fa-angle-right me-2"></i>Profil Sekolah</a></li>
                            <li><a href="https://smknu1slawi.sch.id"><i class="fas fa-angle-right me-2"></i>Program Keahlian</a></li>
                            <li><a href="https://smknu1slawi.sch.id"><i class="fas fa-angle-right me-2"></i>Fasilitas</a></li>
                            <li><a href="https://smknu1slawi.sch.id"><i class="fas fa-angle-right me-2"></i>Prestasi</a></li>
                            <li><a href="https://smknu1slawi.sch.id"><i class="fas fa-angle-right me-2"></i>PPDB <?php echo date('Y'); ?></a></li>
                        </ul>
                    </div>

                    <!-- Column 3: Useful Links -->
                    <div class="col-lg-2 col-md-6">
                        <h5 class="text-white mb-4 footer-heading">Tautan</h5>
                        <ul class="list-unstyled footer-links">
                            <li><a href="<?php echo base_url('/'); ?>"><i class="fas fa-angle-right me-2"></i>Beranda</a></li>
                            <li><a href="#cek-kelulusan"><i class="fas fa-angle-right me-2"></i>Cek Kelulusan</a></li>
                        </ul>
                    </div>

                    <!-- Column 4: Contact Info -->
                    <div class="col-lg-3 col-md-6">
                        <h5 class="text-white mb-4 footer-heading">Kontak Kami</h5>
                        <div class="contact-info">
                            <div class="d-flex mb-3">
                                <div class="icon-box me-3">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div>
                                    <p class="text-white-50 mb-0">Jl. Jenderal Ahmad Yani Jl. Raya Sel. Banjaran No.20, Procot, Kec. Slawi, Kabupaten Tegal, Jawa Tengah 52412</p>
                                </div>
                            </div>
                            <div class="d-flex mb-3">
                                <div class="icon-box me-3">
                                    <i class="fas fa-phone-alt"></i>
                                </div>
                                <div>
                                    <p class="text-white-50 mb-0">(0283) 492695</p>
                                </div>
                            </div>
                            <div class="d-flex">
                                <div class="icon-box me-3">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div>
                                    <p class="text-white-50 mb-0">smknu01slawi@gmail.com</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Copyright Footer -->
        <div class="copyright-footer py-3">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-6 text-center text-md-start">
                        <p class="mb-0 text-white-50">© <?php echo date('Y'); ?> <?php echo $school_name; ?>. All rights reserved.</p>
                    </div>
                    <div class="col-md-6 text-center text-md-end">
                        <p class="mb-0 text-white-50">Designed & Developed by <a href="https://reikidevs.com/" target="_blank" class="developer-link">reikidevs</a></p>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery (untuk beberapa fungsionalitas) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script src="<?php echo base_url('/assets/js/script.js'); ?>"></script>
    <script src="<?php echo base_url('/assets/js/animations.js'); ?>"></script>
</body>
</html>
