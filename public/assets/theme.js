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

    function escapeHtml(s) {
        if (s === null || s === undefined) return '';
        return String(s).replace(/[&<>"']/g, function (c) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c];
        });
    }

    function getWarehouseIdFromPage() {
        var sel = document.getElementById('warehouseSelect');
        if (!sel) return null;
        var v = (sel.value || '').trim();
        if (!v) return null;
        var n = Number(v);
        if (!isFinite(n) || n <= 0) return null;
        return String(Math.floor(n));
    }

    function initNotificationBell(el) {
        if (!el || el.__notifInit) return;
        el.__notifInit = true;

        var api = el.getAttribute('data-notifications-api');
        if (!api) return;

        var countEl = el.querySelector('[data-notifications-count]');
        var dropdownEl = el.querySelector('[data-notifications-dropdown]');
        var listEl = el.querySelector('[data-notifications-list]');

        function setCount(n) {
            if (!countEl) return;
            var c = Number(n || 0);
            if (!isFinite(c) || c <= 0) {
                countEl.style.display = 'none';
                countEl.textContent = '0';
                return;
            }
            countEl.style.display = '';
            countEl.textContent = c > 99 ? '99+' : String(c);
        }

        function buildUrl() {
            var wid = getWarehouseIdFromPage();
            if (!wid) return api;
            return api.indexOf('?') >= 0 ? (api + '&warehouse_id=' + encodeURIComponent(wid)) : (api + '?warehouse_id=' + encodeURIComponent(wid));
        }

        function render(items) {
            if (!listEl) return;
            if (!items || !items.length) {
                listEl.innerHTML = '<div class="text-muted small p-2">No notifications</div>';
                return;
            }

            var html = '';
            for (var i = 0; i < items.length; i++) {
                var it = items[i] || {};
                var type = String(it.type || 'info');
                var title = escapeHtml(it.title || '');
                var message = escapeHtml(it.message || '');
                var createdAt = escapeHtml(it.created_at || '');
                var link = it.link ? String(it.link) : '';

                var badge = 'bg-secondary';
                if (type === 'warning') badge = 'bg-warning text-dark';
                if (type === 'danger') badge = 'bg-danger';
                if (type === 'success') badge = 'bg-success';
                if (type === 'info') badge = 'bg-primary';

                var inner = '';
                inner += '<div class="d-flex align-items-start gap-2 p-2">';
                inner += '<span class="badge ' + badge + '" style="margin-top:2px;">&nbsp;</span>';
                inner += '<div class="flex-grow-1">';
                if (title) inner += '<div class="fw-semibold" style="font-size:13px;">' + title + '</div>';
                inner += '<div class="small" style="color:#333;">' + message + '</div>';
                if (createdAt) inner += '<div class="text-muted" style="font-size:11px;">' + createdAt + '</div>';
                inner += '</div>';
                inner += '</div>';

                if (link) {
                    html += '<a href="' + escapeHtml(link) + '" class="text-decoration-none text-dark d-block" style="border-bottom:1px solid rgba(0,0,0,0.06);">' + inner + '</a>';
                } else {
                    html += '<div style="border-bottom:1px solid rgba(0,0,0,0.06);">' + inner + '</div>';
                }
            }
            listEl.innerHTML = html;
        }

        async function fetchNotifications() {
            if (!listEl) return;
            try {
                var res = await fetch(buildUrl(), { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
                var data = await res.json().catch(function () { return null; });
                if (!res.ok) {
                    var msg = (data && data.error) ? data.error : ('Request failed (' + res.status + ')');
                    throw new Error(msg);
                }
                var list = (data && Array.isArray(data.notifications)) ? data.notifications : [];
                setCount(data && data.count !== undefined ? data.count : list.length);
                render(list);
            } catch (e) {
                setCount(0);
                if (listEl) listEl.innerHTML = '<div class="text-muted small p-2">Failed to load notifications</div>';
            }
        }

        function open() {
            if (!dropdownEl) return;
            dropdownEl.style.display = '';
            fetchNotifications();
        }

        function close() {
            if (!dropdownEl) return;
            dropdownEl.style.display = 'none';
        }

        function toggle() {
            if (!dropdownEl) return;
            if (dropdownEl.style.display === 'none' || dropdownEl.style.display === '') {
                open();
            } else {
                close();
            }
        }

        el.addEventListener('click', function (evt) {
            evt.preventDefault();
            evt.stopPropagation();
            toggle();
        });

        document.addEventListener('click', function () {
            close();
        });

        // auto-refresh badge
        fetchNotifications();
        setInterval(fetchNotifications, 30000);
    }

    function ensureNotifications() {
        function mount() {
            var bells = document.querySelectorAll('[data-notifications-api]');
            if (!bells || !bells.length) return;
            for (var i = 0; i < bells.length; i++) {
                initNotificationBell(bells[i]);
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', mount);
        } else {
            mount();
        }
    }

    ensureNotifications();
})();
