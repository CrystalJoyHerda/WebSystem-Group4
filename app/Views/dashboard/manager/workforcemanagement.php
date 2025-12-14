<?php $role = session() ? session()->get('role') ?? 'User' : 'User'; ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Workforce Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= base_url('css/site.css') ?>" rel="stylesheet">
    <style>
        body{font-family: "Times New Roman", serif; background:#fff}
        .app-shell{display:flex;min-height:100vh}
        .sidebar{width:220px;background:#ebeaea;padding:20px;border-right:1px solid #ddd;position:fixed;top:0;left:0;height:100vh;overflow:auto;z-index:10}
        .main{margin-left:220px;flex:1;padding:28px}
        @media (max-width:991px){.sidebar{position:relative;height:auto;width:100%}.main{margin-left:0;padding:16px}}
        .actions .btn{min-width:84px}
    </style>
</head>
<body>
<div class="app-shell">
    <?= view('partials/sidebar') ?>

    <main class="main">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h3 class="mb-0">Workforce Management</h3>
            <div>
                <button id="btnAddUser" class="btn btn-primary">Add User</button>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body p-3">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th style="width:60px">#</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th style="width:140px">Role</th>
                                <th style="width:200px">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="usersTbody">
                            <!-- filled by JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Add / Edit Modal (password added) -->
<div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="userForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="userModalTitle">Add User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
          <input type="hidden" id="userId" value="">
          <div class="mb-2">
              <label class="form-label small">Full name</label>
              <input id="userName" class="form-control form-control-sm" required>
          </div>
          <div class="mb-2">
              <label class="form-label small">Email</label>
              <input id="userEmail" type="email" class="form-control form-control-sm" required>
          </div>
          <div class="mb-2">
              <label class="form-label small">Role</label>
              <select id="userRole" class="form-select form-select-sm">
                  <option value="manager">Manager</option>
                  <option value="staff">Staff</option>
                  <option value="inventory_auditor">Inventory Auditor</option>
                  <option value="procurement_officer">Procurement Officer</option>
                  <option value="accounts_payable">Accounts Payable</option>
                  <option value="accounts_receivable">Accounts Receivable</option>
                  <option value="it_administrator">IT Administrator</option>
                  <option value="topmanagement">Top Management</option>
              </select>
          </div>

          <div class="mb-2">
              <label class="form-label small">Password</label>
              <input id="userPassword" type="password" class="form-control form-control-sm" placeholder="">
              <div id="passwordHelp" class="form-text small text-muted">Set password when creating. Leave blank on edit to keep existing password.</div>
          </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary btn-sm">Save</button>
      </div>
    </form>
  </div>
</div>

<!-- Confirm delete modal -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-body">
        <p class="mb-0">Delete this user?</p>
        <div class="mt-3 text-end">
            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
            <button id="confirmDeleteBtn" type="button" class="btn btn-danger btn-sm">Delete</button>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
(() => {
    const API_BASE = '<?= site_url('api/workforce') ?>';
    let users = [];
    let deleteTargetId = null;

    const userModal = new bootstrap.Modal(document.getElementById('userModal'));
    const deleteModal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));

    document.getElementById('btnAddUser').addEventListener('click', openAddModal);
    document.getElementById('userForm').addEventListener('submit', onSaveUser);
    document.getElementById('confirmDeleteBtn').addEventListener('click', onConfirmDelete);

    loadUsers();

    async function loadUsers() {
        const res = await fetch(API_BASE, { credentials: 'same-origin' });
        if (!res.ok) {
            document.getElementById('usersTbody').innerHTML = '<tr><td colspan="5" class="text-center small text-muted">Failed to load</td></tr>';
            return;
        }
        users = await res.json();
        renderTable();
    }

    function renderTable() {
        const tbody = document.getElementById('usersTbody');
        tbody.innerHTML = '';
        if (!Array.isArray(users) || users.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center small text-muted">No users</td></tr>';
            return;
        }

        users.forEach(u => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${u.id}</td>
                <td>${escapeHtml(u.name)}</td>
                <td>${escapeHtml(u.email)}</td>
                <td>${escapeHtml(prettyRole(u.role) || 'Not set')}</td>
                <td class="actions">
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-primary btn-edit" data-id="${u.id}">Edit</button>
                        <button class="btn btn-sm btn-outline-danger btn-delete" data-id="${u.id}">Delete</button>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    // Delegated click handler
    document.getElementById('usersTbody').addEventListener('click', function(e){
        const btn = e.target.closest('.btn-edit, .btn-delete');
        if (!btn) return;
        const id = Number(btn.dataset.id);
        if (btn.classList.contains('btn-edit')) {
            openEditModal(id);
        } else if (btn.classList.contains('btn-delete')) {
            deleteTargetId = id;
            deleteModal.show();
        }
    });

    function openAddModal() {
        document.getElementById('userModalTitle').textContent = 'Add User';
        document.getElementById('userId').value = '';
        document.getElementById('userName').value = '';
        document.getElementById('userEmail').value = '';
        document.getElementById('userRole').value = 'staff';
        const pwd = document.getElementById('userPassword');
        pwd.value = '';
        pwd.required = true;                     // required for add
        pwd.placeholder = 'Enter password';
        document.getElementById('passwordHelp').textContent = 'Password is required when adding a user.';
        userModal.show();
    }

    function openEditModal(id) {
        const u = users.find(x => Number(x.id) === Number(id));
        if (!u) return;
        document.getElementById('userModalTitle').textContent = 'Edit User';
        document.getElementById('userId').value = u.id;
        document.getElementById('userName').value = u.name;
        document.getElementById('userEmail').value = u.email;
        // If stored role is falsy or doesn't match any option, normalize DB label to UI key
        const roleSelect = document.getElementById('userRole');
        let stored = u.role || '';
        // normalize: trim, lowercase, spaces -> underscores
        let normalized = String(stored).trim().toLowerCase().replace(/\s+/g, '_');
        // if the select has this value, use it; otherwise default to 'staff'
        if (Array.from(roleSelect.options).some(o => o.value === normalized)) {
            roleSelect.value = normalized;
        } else {
            roleSelect.value = 'staff';
        }
        const pwd = document.getElementById('userPassword');
        pwd.value = '';
        pwd.required = false;                    // optional for edit
        pwd.placeholder = 'Leave blank to keep current password';
        document.getElementById('passwordHelp').textContent = 'Leave blank to keep existing password.';
        userModal.show();
    }

    async function onSaveUser(ev) {
        ev.preventDefault();
        const idVal = document.getElementById('userId').value;
        const name = document.getElementById('userName').value.trim();
        const email = document.getElementById('userEmail').value.trim();
        let role = document.getElementById('userRole').value;
        // ensure a valid role is sent
        if (!role) role = 'staff';
        const password = document.getElementById('userPassword').value;

        if (!name || !email) { alert('Name and email are required.'); return; }

        // require password on create
        if (!idVal && (!password || password.length === 0)) {
            alert('Password is required when creating a user.');
            return;
        }

        const payload = { name, email, role };
        if (password && password.length > 0) payload.password = password;

        try {
            if (idVal) {
                const res = await fetch(`${API_BASE}/${idVal}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload),
                    credentials: 'same-origin'
                });
                if (!res.ok) throw new Error('Update failed');
            } else {
                const res = await fetch(API_BASE, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload),
                    credentials: 'same-origin'
                });
                if (!res.ok) throw new Error('Create failed');
            }
            await loadUsers();
            userModal.hide();
        } catch (err) {
            alert(err.message || 'Request failed');
        }
    }

    async function onConfirmDelete() {
        if (deleteTargetId == null) return;
        try {
            const res = await fetch(`${API_BASE}/${deleteTargetId}`, {
                method: 'DELETE',
                credentials: 'same-origin'
            });
            if (!res.ok) throw new Error('Delete failed');
            await loadUsers();
            deleteTargetId = null;
            deleteModal.hide();
        } catch (err) {
            alert(err.message || 'Delete failed');
        }
    }

    function escapeHtml(s){
        if (!s) return '';
        return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    }

    // Convert stored role keys or DB labels into human friendly labels
    function prettyRole(role) {
        if (!role) return '';
        // normalize stored form to a key: trim, lowercase, spaces -> underscores
        const normalized = String(role).trim().toLowerCase().replace(/\s+/g, '_');
        const map = {
            'manager': 'Manager',
            'staff': 'Staff',
            'inventory_auditor': 'Inventory Auditor',
            'procurement_officer': 'Procurement Officer',
            'accounts_payable': 'Accounts Payable',
            'accounts_receivable': 'Accounts Receivable',
            'it_administrator': 'IT Administrator',
            'topmanagement': 'Top Management',
            'admin': 'Admin'
        };
        if (map[normalized]) return map[normalized];
        // fallback: title-case the trimmed original string
        return String(role).trim().split(/\s+/).map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
    }
})();
</script>
</body>
</html>