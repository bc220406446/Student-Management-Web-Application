// Wait until the HTML page is fully loaded before looking for page elements.
document.addEventListener('DOMContentLoaded', () => {
    // FLASH MESSAGES: close automatically or when the user clicks the X button.
    const flashBanner = document.getElementById('flashBanner');
    if (flashBanner) {
        // First play the CSS closing animation, then remove the banner from the page.
        const dismissFlash = () => {
            flashBanner.classList.add('is-dismissing');
            setTimeout(() => flashBanner.remove(), 300);
        };
        // Read data-duration from the HTML; use 10 seconds if it is missing or invalid.
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

    // PASSWORD TOGGLE: change a password field between hidden and visible text.
    const passwordToggles = document.querySelectorAll('.password-toggle');
    passwordToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            // "this" refers to the Show/Hide control that the user clicked.
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

    // ADMIN MENU: open the dropdown from the administrator avatar.
    const adminAvatar = document.getElementById('adminAvatar');
    const adminDropdown = document.getElementById('adminDropdown');
    if (adminAvatar && adminDropdown) {
        adminAvatar.addEventListener('click', (e) => {
            // Prevent this click from immediately reaching the document listener below.
            e.stopPropagation();
            adminDropdown.classList.toggle('show');
        });
        document.addEventListener('click', (e) => {
            if (adminDropdown && !adminDropdown.contains(e.target)) {
                adminDropdown.classList.remove('show');
            }
        });
    }

    // STUDENT MENU: same dropdown behavior for the student avatar.
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

    // MODALS: data-modal-target connects an opening button to a modal ID.
    const modalTriggers = document.querySelectorAll('[data-modal-target]');
    const modalCloses = document.querySelectorAll('[data-modal-close]');
    
    modalTriggers.forEach(trigger => {
        trigger.addEventListener('click', (e) => {
            e.preventDefault();
            const targetId = trigger.getAttribute('data-modal-target');
            const targetModal = document.getElementById(targetId);
            if (targetModal) {
                targetModal.classList.add('is-open');
                
                // Close avatar dropdowns so they do not appear above the modal.
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
            // closest() finds the modal that contains the clicked close button.
            const modal = closeBtn.closest('.modal-overlay');
            if (modal) {
                modal.classList.remove('is-open');
            }
        });
    });

    // Also close a modal when the dark background outside its card is clicked.
    const modals = document.querySelectorAll('.modal-overlay');
    modals.forEach(modal => {
        modal.addEventListener('click', (e) => {
            // This check prevents clicks inside the modal card from closing it.
            if (e.target === modal) {
                modal.classList.remove('is-open');
            }
        });
    });

    // ACADEMIC RECORD: show or hide the record card on the student dashboard.
    const viewRecordBtn = document.getElementById('viewRecordBtn');
    const hideRecordBtn = document.getElementById('hideRecordBtn');
    const academicRecordCard = document.getElementById('academicRecordCard');

    if (viewRecordBtn && academicRecordCard) {
        viewRecordBtn.addEventListener('click', () => {
            academicRecordCard.classList.add('is-visible');
            viewRecordBtn.classList.add('is-hidden');
            // Smoothly move the page to the newly displayed record.
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

