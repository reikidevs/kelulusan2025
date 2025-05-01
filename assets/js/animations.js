/**
 * Animations and enhanced UI/UX for Kelulusan SMK NU 1 Slawi website
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialize particles background
    initParticlesBackground();
    
    // Initialize floating elements
    document.querySelectorAll('.float').forEach(function(element) {
        const randomDelay = Math.random() * 2;
        element.style.animationDelay = `${randomDelay}s`;
    });
    
    // Animate elements on scroll
    const fadeElements = document.querySelectorAll('.fade-in');
    
    // Check if elements are in viewport on scroll
    window.addEventListener('scroll', function() {
        fadeElements.forEach(function(element) {
            if (isInViewport(element)) {
                element.style.opacity = '1';
            }
        });
    });
    
    // Trigger once on load to check initial elements
    fadeElements.forEach(function(element) {
        if (isInViewport(element)) {
            element.style.opacity = '1';
        }
    });
    
    // Setup verification form with validation and loading spinner
    setupVerificationForm();
});

/**
 * Initialize particles background on hero section
 */
function initParticlesBackground() {
    const heroSection = document.querySelector('.hero');
    if (!heroSection) return;

    // Add class for styling
    heroSection.classList.add('particles-bg');
    
    // Create particles
    for (let i = 0; i < 15; i++) {
        const particle = document.createElement('div');
        particle.className = 'particle';
        
        // Random properties
        const size = Math.random() * 10 + 5;
        const posX = Math.random() * 100;
        const posY = Math.random() * 100;
        const opacity = Math.random() * 0.5 + 0.1;
        const translateX = (Math.random() - 0.5) * 200;
        const translateY = (Math.random() - 0.5) * 200;
        const duration = Math.random() * 20 + 10;
        
        // Apply styles
        particle.style.width = `${size}px`;
        particle.style.height = `${size}px`;
        particle.style.left = `${posX}%`;
        particle.style.top = `${posY}%`;
        particle.style.opacity = `${opacity}`;
        particle.style.backgroundColor = 'rgba(255, 255, 255, 0.5)';
        particle.style.setProperty('--translate-x', `${translateX}px`);
        particle.style.setProperty('--translate-y', `${translateY}px`);
        particle.style.animation = `particle-animation ${duration}s infinite ease-in-out alternate`;
        
        heroSection.appendChild(particle);
    }
}

/**
 * Check if element is in viewport
 * 
 * @param {Element} element Element to check
 * @return {boolean} Is element in viewport
 */
function isInViewport(element) {
    const rect = element.getBoundingClientRect();
    return (
        rect.top <= (window.innerHeight || document.documentElement.clientHeight) &&
        rect.bottom >= 0
    );
}

/**
 * Setup verification form with validation, loading state, and error modal
 */
function setupVerificationForm() {
    const form = document.getElementById('verification-form');
    if (!form) return;
    
    form.addEventListener('submit', function(event) {
        const examNumber = document.getElementById('exam_number').value.trim();
        
        if (examNumber === '') {
            event.preventDefault();
            showErrorModal('Nomor ujian tidak boleh kosong!', 'Silakan masukkan nomor ujian untuk melanjutkan.');
        } else {
            // Add loading state
            const submitButton = form.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Memproses...';
                submitButton.disabled = true;
            }
        }
    });
}

/**
 * Show error modal with custom message
 * 
 * @param {string} title Modal title
 * @param {string} message Modal message
 */
function showErrorModal(title, message) {
    // Create modal if it doesn't exist
    let errorModal = document.getElementById('errorModal');
    
    if (!errorModal) {
        const modalHTML = `
            <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title" id="errorModalLabel">Error</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="text-center mb-3">
                                <i class="fas fa-exclamation-circle text-danger fa-4x"></i>
                            </div>
                            <p id="errorMessage" class="text-center">Error message</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        errorModal = document.getElementById('errorModal');
    }
    
    // Set modal content
    document.getElementById('errorModalLabel').textContent = title;
    document.getElementById('errorMessage').textContent = message;
    
    // Show modal
    const modal = new bootstrap.Modal(errorModal);
    modal.show();
}
