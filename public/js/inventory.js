// Category and location filter for inventory table
document.addEventListener('DOMContentLoaded', function(){
	var categorySelect = document.querySelector('select[name="category"]');
	var locationSelect = document.querySelector('select[name="location"]');
	var tableRows = document.querySelectorAll('table.table tbody tr');

		var searchInput = document.querySelector('.controls input.form-control');

		function filterRows() {
			var selectedCategory = categorySelect ? categorySelect.value.trim() : '';
			var selectedLocation = locationSelect ? locationSelect.value.trim() : '';
			var searchTerm = searchInput ? searchInput.value.trim().toLowerCase() : '';
			tableRows.forEach(function(row) {
				var nameCell = row.querySelector('td:nth-child(1)');
				var skuCell = row.querySelector('td:nth-child(2)');
				var catCell = row.querySelector('td:nth-child(3)');
				var locCell = row.querySelector('td:nth-child(4)');
				if (!nameCell || !skuCell || !catCell || !locCell) return;
				var name = nameCell.textContent.trim().toLowerCase();
				var sku = skuCell.textContent.trim().toLowerCase();
				var cat = catCell.textContent.trim();
				var catLower = cat.toLowerCase();
				var loc = locCell.textContent.trim();
				var catMatch = (selectedCategory === 'All Category' || selectedCategory === '' || cat === selectedCategory);
				var locMatch = (selectedLocation === '' || loc.startsWith(selectedLocation));
								var searchMatch = (
									searchTerm === '' ||
									name.startsWith(searchTerm)
								);
						if (catMatch && locMatch && searchMatch) {
							row.style.display = '';
						} else {
							row.style.display = 'none';
						}
			});
		}

		if (categorySelect) categorySelect.addEventListener('change', filterRows);
		if (locationSelect) locationSelect.addEventListener('change', filterRows);
		if (searchInput) searchInput.addEventListener('input', filterRows);
});
// Inventory page specific JS
document.addEventListener('DOMContentLoaded', function(){
	// Modal-driven delete confirmation
	var deleteModalEl = document.getElementById('deleteConfirmModal');
	var bsDeleteModal = deleteModalEl ? new bootstrap.Modal(deleteModalEl) : null;
	var currentDeleteForm = null;

	document.querySelectorAll('.btn-delete').forEach(function(btn){
		btn.addEventListener('click', function(e){
			e.preventDefault();
			var name = btn.getAttribute('data-name') || 'this item';
			var id = btn.getAttribute('data-id');
			// find the closest form
			var form = btn.closest('form');
			if (!form) return;
			currentDeleteForm = form;
			var nameEl = document.getElementById('delete-item-name');
			if (nameEl) nameEl.textContent = name;
			if (bsDeleteModal) bsDeleteModal.show();
		});
	});

	var confirmBtn = document.getElementById('confirm-delete');
	if (confirmBtn) {
		confirmBtn.addEventListener('click', function(){
			if (currentDeleteForm) {
				currentDeleteForm.submit();
			}
		});
	}

	// enhance tables: clicking a row highlights it
	document.querySelectorAll('table.table tbody tr').forEach(function(row){
		row.addEventListener('click', function(){
			document.querySelectorAll('table.table tbody tr').forEach(r=>r.classList.remove('table-active'));
			row.classList.add('table-active');
		});
	});

	// Edit modal: populate fields and open modal
	var editModalEl = document.getElementById('editItemModal');
	var bsEditModal = editModalEl ? new bootstrap.Modal(editModalEl) : null;
	document.querySelectorAll('.btn-edit').forEach(function(btn){
		btn.addEventListener('click', function(e){
			e.preventDefault();
			var id = btn.getAttribute('data-id');
			if (!id) return;
			// populate fields
			var fields = ['name','sku','category','location','quantity','status','expiry'];
			fields.forEach(function(f){
				var el = document.getElementById('edit-' + f);
				if (!el) return;
				var val = btn.getAttribute('data-' + f) || '';
				el.value = val;
			});
			var idEl = document.getElementById('edit-id');
			if (idEl) idEl.value = id;
			// set form action to include id
			var form = document.getElementById('edit-item-form');
			if (form) form.action = form.getAttribute('action') + '/' + id;
			if (bsEditModal) bsEditModal.show();
		});
	});

	// Flash helper: create and auto-dismiss alerts in #flash-container
	function showFlash(message, type){
		var container = document.getElementById('flash-container');
		if (!container) return;
		var div = document.createElement('div');
		div.className = 'alert alert-' + (type || 'info') + ' shadow';
		div.style.minWidth = '220px';
		div.style.marginTop = '8px';
		div.innerHTML = message;
		container.appendChild(div);
		setTimeout(function(){
			div.classList.add('fade');
			setTimeout(function(){ if(div.parentNode) div.parentNode.removeChild(div); }, 300);
		}, 3500);
	}

	// If server rendered flash messages exist (data attributes), show them
	document.querySelectorAll('[data-flash-message]').forEach(function(el){
		var msg = el.getAttribute('data-flash-message');
		var type = el.getAttribute('data-flash-type') || 'success';
		if (msg) showFlash(msg, type);
	});
});
