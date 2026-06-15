document.addEventListener('DOMContentLoaded', () => {
    // Flash message auto-dismiss
    const flashBanner = document.getElementById('flashBanner');
    if (flashBanner) {
        const dismissFlash = () => {
            flashBanner.classList.add('is-dismissing');
            setTimeout(() => flashBanner.remove(), 300);
        };
        const duration = Number.parseInt(flashBanner.dataset.duration || '10000', 10);
        const flashTimer = setTimeout(dismissFlash, Number.isFinite(duration) ? duration : 10000);
        const flashClose = document.getElementById('flashClose');
        if (flashClose) {
            flashClose.addEventListener('click', () => {
                clearTimeout(flashTimer);
                dismissFlash();
            });
        }
    }

    // Password show/hide toggle
    const passwordToggles = document.querySelectorAll('.password-toggle');
    passwordToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const input = this.parentElement.querySelector('input');
            if (input.type === 'password') {
                input.type = 'text';
                this.textContent = 'Hide';
            } else {
                input.type = 'password';
                this.textContent = 'Show';
            }
        });
    });

    // Admin Avatar Dropdown
    const adminAvatar = document.getElementById('adminAvatar');
    const adminDropdown = document.getElementById('adminDropdown');
    if (adminAvatar && adminDropdown) {
        adminAvatar.addEventListener('click', (e) => {
            e.stopPropagation();
            adminDropdown.classList.toggle('show');
        });
        document.addEventListener('click', (e) => {
            if (adminDropdown && !adminDropdown.contains(e.target)) {
                adminDropdown.classList.remove('show');
            }
        });
    }

    // Avatar Dropdown
    const avatar = document.getElementById('userAvatar');
    const dropdown = document.getElementById('userDropdown');
    if (avatar && dropdown) {
        avatar.addEventListener('click', (e) => {
            e.stopPropagation();
            dropdown.classList.toggle('show');
        });
        document.addEventListener('click', (e) => {
            if (!dropdown.contains(e.target)) {
                dropdown.classList.remove('show');
            }
        });
    }

    // Modals
    const modalTriggers = document.querySelectorAll('[data-modal-target]');
    const modalCloses = document.querySelectorAll('[data-modal-close]');
    
    modalTriggers.forEach(trigger => {
        trigger.addEventListener('click', (e) => {
            e.preventDefault();
            const targetId = trigger.getAttribute('data-modal-target');
            const targetModal = document.getElementById(targetId);
            if (targetModal) {
                targetModal.classList.add('is-open');
                
                // Close dropdowns if open
                const userDropdown = document.getElementById('userDropdown');
                if (userDropdown) {
                    userDropdown.classList.remove('show');
                }
                const adminDropdown = document.getElementById('adminDropdown');
                if (adminDropdown) {
                    adminDropdown.classList.remove('show');
                }
            }
        });
    });

    modalCloses.forEach(closeBtn => {
        closeBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const modal = closeBtn.closest('.modal-overlay');
            if (modal) {
                modal.classList.remove('is-open');
            }
        });
    });

    // Close modal on outside click
    const modals = document.querySelectorAll('.modal-overlay');
    modals.forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.remove('is-open');
            }
        });
    });

    // Academic Record toggle (Student Dashboard)
    const viewRecordBtn = document.getElementById('viewRecordBtn');
    const hideRecordBtn = document.getElementById('hideRecordBtn');
    const academicRecordCard = document.getElementById('academicRecordCard');

    if (viewRecordBtn && academicRecordCard) {
        viewRecordBtn.addEventListener('click', () => {
            academicRecordCard.classList.add('is-visible');
            viewRecordBtn.classList.add('is-hidden');
            academicRecordCard.scrollIntoView({ behavior: 'smooth' });
        });
    }
    if (hideRecordBtn && academicRecordCard) {
        hideRecordBtn.addEventListener('click', () => {
            academicRecordCard.classList.remove('is-visible');
            if (viewRecordBtn) {
                viewRecordBtn.classList.remove('is-hidden');
            }
        });
    }
});

