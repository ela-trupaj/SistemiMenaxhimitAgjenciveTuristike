document.querySelectorAll('.password-toggle').forEach(button => {
    button.addEventListener('click', () => {
        const input = button.parentElement.querySelector('input');
        const visible = input.type === 'password';

        input.type = visible ? 'text' : 'password';

        button.querySelector('i').className =
            visible ? 'bx bx-hide' : 'bx bx-show';

        button.setAttribute(
            'aria-label',
            visible ? 'Fshih fjalëkalimin' : 'Shfaq fjalëkalimin'
        );

        button.setAttribute('aria-pressed', String(visible));
    });
});


// Layout.php

const sidebar = document.getElementById('dashboard-sidebar');
const menuToggle = document.getElementById('menu-toggle');
const sidebarOverlay = document.getElementById('sidebar-overlay');

if (sidebar && menuToggle && sidebarOverlay) {
    const setSidebarOpen = (open) => {
        sidebar.classList.toggle('open', open);
        sidebarOverlay.classList.toggle('show', open);
        document.body.classList.toggle('sidebar-open', open);
        menuToggle.setAttribute('aria-expanded', String(open));
        menuToggle.setAttribute(
            'aria-label',
            open ? 'Mbyll menynë' : 'Hap menynë'
        );
        sidebar.inert = !open && window.innerWidth <= 900;
    };

    menuToggle.addEventListener('click', () => {
        setSidebarOpen(!sidebar.classList.contains('open'));
    });

    sidebarOverlay.addEventListener('click', () => {
        setSidebarOpen(false);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && sidebar.classList.contains('open')) {
            setSidebarOpen(false);
            menuToggle.focus();
        }
    });

    sidebar.querySelectorAll('nav a').forEach((link) => {
        link.addEventListener('click', () => {
            setSidebarOpen(false);
        });
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth > 900) {
            setSidebarOpen(false);
            sidebar.inert = false;
        } else if (!sidebar.classList.contains('open')) {
            sidebar.inert = true;
        }
    });

    if (window.innerWidth <= 900) {
        sidebar.inert = true;
    }
}

// BUTTON LOADING STATE

document.querySelectorAll('.auth-form').forEach(form => {

    const button = form.querySelector('[data-loading]');

    if (!button) return;

    const originalContent = button.innerHTML;

    // Gjate dergimit te formularit
    form.addEventListener('submit', event => {

        if (button.disabled) {
            event.preventDefault();
            return;
        }

        button.disabled = true;
        button.classList.add('is-loading');
        button.setAttribute('aria-busy', 'true');

        button.innerHTML = `
            <span class="auth-spinner" aria-hidden="true"></span>
            <span>${button.dataset.loading}</span>
        `;
    });

    // Rikthimi i gjendjes fillestare
    window.addEventListener('pageshow', () => {

        button.disabled = false;
        button.classList.remove('is-loading');
        button.removeAttribute('aria-busy');
        button.innerHTML = originalContent;
    });
});

// DASHBOARD BUTTON LOADING STATE
document.querySelectorAll('.booking-form, .cancel-form, .profile-form, .staff-form, .entity-form, .delete-form').forEach(form => {
    const button = form.querySelector('[data-loading]');

    if (!button) return;

    const originalContent = button.innerHTML;

    form.addEventListener('submit', event => {

        // Kontrollimi i konfirmimit dhe klikimeve te perseritura
        if (event.defaultPrevented) return;

        if (button.disabled) {
            event.preventDefault();
            return;
        }

        button.disabled = true;
        button.classList.add('is-loading');
        button.setAttribute('aria-busy', 'true');

        button.innerHTML = `
            <span class="dashboard-spinner" aria-hidden="true"></span>
            <span>${button.dataset.loading}</span>
        `;
    });

    // Rikthimi i gjendjes fillestare
    window.addEventListener('pageshow', () => {

        button.disabled = false;
        button.classList.remove('is-loading');
        button.removeAttribute('aria-busy');
        button.innerHTML = originalContent;
    });
});