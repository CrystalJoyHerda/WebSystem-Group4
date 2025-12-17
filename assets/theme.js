(function () {
    function getStoredTheme() {
        try {
            return localStorage.getItem('theme');
        } catch (e) {
            return null;
        }
    }

    function setStoredTheme(theme) {
        try {
            localStorage.setItem('theme', theme);
        } catch (e) {}
    }

    function systemTheme() {
        try {
            return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        } catch (e) {
            return 'light';
        }
    }

    function applyTheme(theme) {
        var root = document.documentElement;
        root.setAttribute('data-theme', theme);
        root.setAttribute('data-bs-theme', theme);
    }

    var lockedTheme = document.documentElement.getAttribute('data-theme-lock');
    if (lockedTheme === 'dark' || lockedTheme === 'light') {
        applyTheme(lockedTheme);
        return;
    }

    function ensureToggle() {
        if (document.getElementById('themeToggle')) return;
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.id = 'themeToggle';
        btn.className = 'theme-toggle';
        btn.addEventListener('click', function () {
            var current = document.documentElement.getAttribute('data-theme') || 'light';
            var next = current === 'dark' ? 'light' : 'dark';
            applyTheme(next);
            setStoredTheme(next);
            updateLabel(btn, next);
        });

        function mount() {
            if (!document.body) return;
            var dock = document.querySelector('.header-right');
            if (dock) {
                btn.classList.add('theme-toggle--docked');
                dock.appendChild(btn);
            } else {
                document.body.appendChild(btn);
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', mount);
        } else {
            mount();
        }
        updateLabel(btn, document.documentElement.getAttribute('data-theme') || 'light');
    }

    function updateLabel(btn, theme) {
        btn.textContent = theme === 'dark' ? 'Light Mode' : 'Dark Mode';
    }

    var initial = getStoredTheme() || systemTheme();
    applyTheme(initial);
    ensureToggle();
})();
