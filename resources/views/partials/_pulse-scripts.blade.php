    <script>
        (function () {
            const pages = document.querySelectorAll('.pulse-page');
            const buttons = document.querySelectorAll('.pulse-theme-btn');
            const storageKey = 'pulse-theme';

            const applyTheme = (theme) => {
                pages.forEach((page) => {
                    page.setAttribute('data-theme', theme);
                });
                buttons.forEach((button) => {
                    button.classList.toggle('active', button.dataset.theme === theme);
                });
                localStorage.setItem(storageKey, theme);
            };

            const savedTheme = localStorage.getItem(storageKey);
            const initialTheme = savedTheme === 'dark' ? 'dark' : 'light';
            applyTheme(initialTheme);

            buttons.forEach((button) => {
                button.addEventListener('click', () => {
                    applyTheme(button.dataset.theme);
                });
            });
        })();

        (function () {
            document.querySelectorAll('[data-toggle-password]').forEach((toggle) => {
                const input = document.getElementById(toggle.dataset.togglePassword);

                if (!input) {
                    return;
                }

                const toggleVisibility = () => {
                    const showing = input.type === 'text';
                    input.type = showing ? 'password' : 'text';
                    toggle.classList.toggle('fa-eye', showing);
                    toggle.classList.toggle('fa-eye-slash', !showing);
                    toggle.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
                };

                toggle.addEventListener('click', toggleVisibility);
                toggle.addEventListener('keydown', (event) => {
                    if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        toggleVisibility();
                    }
                });
            });
        })();

        (function () {
            document.querySelectorAll('form.pulse-form').forEach((form) => {
                form.addEventListener('submit', () => {
                    const button = form.querySelector('button[type="submit"]');

                    if (!button || button.disabled) {
                        return;
                    }

                    button.disabled = true;
                    button.dataset.originalLabel = button.innerHTML;
                    button.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Please wait…';
                });
            });
        })();
    </script>
