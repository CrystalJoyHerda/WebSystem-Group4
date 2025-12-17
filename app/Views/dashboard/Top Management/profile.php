<?= view('dashboard/Top Management/_shell_start', ['title' => $title ?? 'My Profile', 'active' => $active ?? 'top-management/profile']) ?>

<div class="card" style="max-width:720px;margin:0 auto;">
    <div class="card-body">
        <div class="fw-semibold" style="font-size:18px;">My Profile</div>
        <div class="text-muted small mb-3">Update your information. Saving requires your current password.</div>

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label small">Name</label>
                <input id="pfName" class="form-control form-control-sm" />
            </div>
            <div class="col-md-6">
                <label class="form-label small">Email</label>
                <input id="pfEmail" type="email" class="form-control form-control-sm" />
            </div>
            <div class="col-md-6">
                <label class="form-label small">New Password (optional)</label>
                <input id="pfNewPassword" type="password" class="form-control form-control-sm" autocomplete="new-password" />
            </div>
            <div class="col-md-6">
                <label class="form-label small">Confirm New Password</label>
                <input id="pfConfirmPassword" type="password" class="form-control form-control-sm" autocomplete="new-password" />
            </div>
            <div class="col-12">
                <label class="form-label small">Current Password (required to save)</label>
                <input id="pfCurrentPassword" type="password" class="form-control form-control-sm" autocomplete="current-password" />
            </div>
        </div>

        <div class="d-flex gap-2 mt-3">
            <button id="pfSave" class="btn btn-sm btn-dark">Save Changes</button>
            <button id="pfReload" class="btn btn-sm btn-outline-secondary">Reload</button>
        </div>

        <div id="pfStatus" class="small mt-2"></div>
    </div>
</div>

<script>
    async function fetchJson(url, opts) {
        const res = await fetch(url, Object.assign({ headers: { 'Accept': 'application/json' }, credentials: 'same-origin' }, opts || {}));
        const data = await res.json().catch(() => null);
        if (!res.ok) {
            const msg = data && data.error ? data.error : `Request failed (${res.status})`;
            throw new Error(msg);
        }
        return data;
    }

    async function loadProfile() {
        const data = await fetchJson('<?= site_url('api/top/profile') ?>');
        const u = data && data.user ? data.user : {};
        document.getElementById('pfName').value = u.name || '';
        document.getElementById('pfEmail').value = u.email || '';
        document.getElementById('pfNewPassword').value = '';
        document.getElementById('pfConfirmPassword').value = '';
        document.getElementById('pfCurrentPassword').value = '';
    }

    async function saveProfile() {
        const payload = {
            name: document.getElementById('pfName').value,
            email: document.getElementById('pfEmail').value,
            new_password: document.getElementById('pfNewPassword').value,
            confirm_password: document.getElementById('pfConfirmPassword').value,
            current_password: document.getElementById('pfCurrentPassword').value,
        };

        return fetchJson('<?= site_url('api/top/profile') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(payload),
        });
    }

    function setStatus(msg, cls) {
        const el = document.getElementById('pfStatus');
        if (!el) return;
        el.textContent = msg;
        el.className = 'small mt-2 ' + (cls || '');
    }

    (async function init() {
        try {
            await loadProfile();
            document.getElementById('pfReload').addEventListener('click', async () => {
                setStatus('', '');
                await loadProfile();
            });

            document.getElementById('pfSave').addEventListener('click', async () => {
                try {
                    setStatus('', '');
                    const btn = document.getElementById('pfSave');
                    btn.disabled = true;
                    await saveProfile();
                    setStatus('Saved.', 'text-success');
                    await loadProfile();
                } catch (e) {
                    setStatus(e && e.message ? e.message : String(e), 'text-danger');
                } finally {
                    document.getElementById('pfSave').disabled = false;
                }
            });
        } catch (e) {
            setStatus(e && e.message ? e.message : String(e), 'text-danger');
        }
    })();
</script>

<?= view('dashboard/Top Management/_shell_end') ?>
