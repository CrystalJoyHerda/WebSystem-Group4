// Inventory Management JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Initialize modals
    const editModal = new bootstrap.Modal(document.getElementById('editItemModal'));
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    
    let currentDeleteForm = null;
    let warehouses = [];

    // Load warehouses on page load
    loadWarehouses();

    // Load warehouses function
    async function loadWarehouses() {
        try {
            const response = await fetch(`${window.siteUrl}/api/warehouse/list`);
            if (response.ok) {
                warehouses = await response.json();
                populateWarehouseSelects();
            }
        } catch (error) {
            console.error('Error loading warehouses:', error);
        }
    }

    // Populate warehouse select elements
    function populateWarehouseSelects() {
        const addSelect = document.getElementById('add-warehouse');
        const editSelect = document.getElementById('edit-warehouse');

        const warehouseOptions = warehouses.map(w => 
            `<option value="${w.id}">${w.name}</option>`
        ).join('');

        if (addSelect) {
            addSelect.innerHTML = '<option value="">Select Warehouse</option>' + warehouseOptions;
        }
        if (editSelect) {
            editSelect.innerHTML = '<option value="">Select Warehouse</option>' + warehouseOptions;
        }
    }

    // Handle edit button clicks
    document.querySelectorAll('.btn-edit').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.dataset.id;
            const name = this.dataset.name;
            const sku = this.dataset.sku;
            const category = this.dataset.category;
            const location = this.dataset.location;
            const warehouse = this.dataset.warehouse;
            const quantity = this.dataset.quantity;
            const status = this.dataset.status;
            const expiry = this.dataset.expiry;

            // Populate edit form
            document.getElementById('edit-id').value = id;
            document.getElementById('edit-name').value = name;
            document.getElementById('edit-sku').value = sku;
            document.getElementById('edit-category').value = category;
            document.getElementById('edit-location').value = location;
            document.getElementById('edit-warehouse').value = warehouse;
            document.getElementById('edit-quantity').value = quantity;
            document.getElementById('edit-status').value = status;
            document.getElementById('edit-expiry').value = expiry;

            // Update form action
            document.getElementById('edit-item-form').action = `${window.siteUrl}/inventory/update/${id}`;

            // Show modal
            editModal.show();
        });
    });

    // Handle delete button clicks
    document.querySelectorAll('.btn-delete').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.dataset.id;
            const name = this.dataset.name;
            
            // Find the form associated with this delete button
            currentDeleteForm = this.closest('.delete-form');
            
            // Update modal content
            document.getElementById('delete-item-name').textContent = name;
            
            // Show delete confirmation modal
            deleteModal.show();
        });
    });

    // Handle delete confirmation
    document.getElementById('confirm-delete').addEventListener('click', function() {
        if (currentDeleteForm) {
            currentDeleteForm.submit();
        }
        deleteModal.hide();
    });

    // Flash message handling
    function showFlashMessage(message, type = 'success') {
        const container = document.getElementById('flash-container');
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-dismissible fade show`;
        alert.style.minWidth = '300px';
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        container.appendChild(alert);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, 5000);
    }

    // Check for flash messages on page load
    const flashElements = document.querySelectorAll('[data-flash-message]');
    flashElements.forEach(element => {
        const message = element.dataset.flashMessage;
        const type = element.dataset.flashType || 'success';
        showFlashMessage(message, type);
        element.remove(); // Remove the hidden element
    });

    // Form validation
    const addForm = document.querySelector('#addItemModal form');
    const editForm = document.querySelector('#editItemModal form');

    function validateForm(form) {
        const name = form.querySelector('[name="name"]').value.trim();
        const quantity = parseInt(form.querySelector('[name="quantity"]').value);
        
        if (!name) {
            showFlashMessage('Item name is required', 'danger');
            return false;
        }
        
        if (isNaN(quantity) || quantity < 0) {
            showFlashMessage('Quantity must be a valid number', 'danger');
            return false;
        }
        
        return true;
    }

    // Add form submission
    if (addForm) {
        addForm.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
    }

    // Edit form submission
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
    }

    // Search functionality
    const searchInput = document.querySelector('.controls input[type="text"], .controls input[placeholder=""]');
    if (searchInput) {
        searchInput.placeholder = 'Search items...';
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }

    // Category filter
    const categorySelect = document.querySelector('.controls select[name="category"]');
    if (categorySelect) {
        categorySelect.addEventListener('change', function() {
            const selectedCategory = this.value;
            const rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const categoryCell = row.cells[2]; // Category is 3rd column (index 2)
                if (selectedCategory === 'All Category' || categoryCell.textContent.trim() === selectedCategory) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }

    // Location filter
    const locationSelect = document.querySelector('.controls select[name="location"]');
    if (locationSelect) {
        locationSelect.addEventListener('change', function() {
            const selectedLocation = this.value;
            const rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const locationCell = row.cells[3]; // Location is 4th column (index 3)
                if (!selectedLocation || locationCell.textContent.trim() === selectedLocation) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
});