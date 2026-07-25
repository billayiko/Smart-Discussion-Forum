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
            const resetSubmitButtons = () => {
                document.querySelectorAll('form.pulse-form button[type="submit"][data-original-label]').forEach((button) => {
                    button.disabled = false;
                    button.innerHTML = button.dataset.originalLabel;
                    delete button.dataset.originalLabel;
                });
            };

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

            // Browsers can restore a full DOM snapshot from back-forward cache
            // (bfcache) — e.g. after a failed login redirects back and the user
            // hits Back — which would otherwise leave the submit button stuck
            // disabled from the previous attempt. A click on a disabled button
            // does nothing at all, which looks exactly like "the page won't
            // submit." pageshow with event.persisted fires whenever a page is
            // restored this way (a plain load doesn't set it).
            window.addEventListener('pageshow', (event) => {
                if (event.persisted) {
                    resetSubmitButtons();
                }
            });
        })();
    </script>
