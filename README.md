import React, { useState, useEffect } from 'react';
import { Search, Plus, FileText, Package, Users, TrendingUp, AlertCircle, CheckCircle, Clock, X, Edit, Trash2, Eye, Download, Filter } from 'lucide-react';

const ProcurementOfficerApp = () => {
  const [activeTab, setActiveTab] = useState('dashboard');
  const [showModal, setShowModal] = useState(false);
  const [modalType, setModalType] = useState('');
  const [searchTerm, setSearchTerm] = useState('');
  const [selectedItem, setSelectedItem] = useState(null);
  const [filterStatus, setFilterStatus] = useState('all');

  // Sample data states
  const [purchaseOrders, setPurchaseOrders] = useState([
    { id: 'PO-2024-001', vendor: 'ABC Supplies Co.', items: 'Office Supplies', amount: 45000, date: '2024-11-10', status: 'pending', priority: 'high' },
    { id: 'PO-2024-002', vendor: 'Tech Solutions Inc.', items: 'Computers & Peripherals', amount: 250000, date: '2024-11-09', status: 'approved', priority: 'medium' },
    { id: 'PO-2024-003', vendor: 'Furniture World', items: 'Office Furniture', amount: 180000, date: '2024-11-08', status: 'completed', priority: 'low' },
    { id: 'PO-2024-004', vendor: 'Green Energy Corp', items: 'Solar Panels', amount: 500000, date: '2024-11-07', status: 'pending', priority: 'high' },
  ]);

  const [suppliers, setSuppliers] = useState([
    { id: 'SUP-001', name: 'ABC Supplies Co.', contact: 'Juan Dela Cruz', email: 'juan@abcsupplies.ph', phone: '0917-123-4567', category: 'Office Supplies', rating: 4.5 },
    { id: 'SUP-002', name: 'Tech Solutions Inc.', contact: 'Maria Santos', email: 'maria@techsolutions.ph', phone: '0918-234-5678', category: 'Technology', rating: 4.8 },
    { id: 'SUP-003', name: 'Furniture World', contact: 'Pedro Reyes', email: 'pedro@furnitureworld.ph', phone: '0919-345-6789', category: 'Furniture', rating: 4.2 },
    { id: 'SUP-004', name: 'Green Energy Corp', contact: 'Ana Garcia', email: 'ana@greenenergy.ph', phone: '0920-456-7890', category: 'Energy', rating: 4.6 },
  ]);

  const [inventory, setInventory] = useState([
    { id: 'INV-001', item: 'A4 Paper (Ream)', quantity: 250, unit: 'reams', reorderLevel: 100, status: 'adequate', lastOrdered: '2024-10-15' },
    { id: 'INV-002', item: 'Ballpen (Box)', quantity: 45, unit: 'boxes', reorderLevel: 50, status: 'low', lastOrdered: '2024-10-20' },
    { id: 'INV-003', item: 'Laptop Units', quantity: 15, unit: 'units', reorderLevel: 10, status: 'adequate', lastOrdered: '2024-09-01' },
    { id: 'INV-004', item: 'Office Chairs', quantity: 8, unit: 'units', reorderLevel: 15, status: 'critical', lastOrdered: '2024-08-15' },
  ]);

  const [formData, setFormData] = useState({});

  const handleInputChange = (e) => {
    setFormData({ ...formData, [e.target.name]: e.target.value });
  };

  const openModal = (type, item = null) => {
    setModalType(type);
    setSelectedItem(item);
    setFormData(item || {});
    setShowModal(true);
  };

  const closeModal = () => {
    setShowModal(false);
    setSelectedItem(null);
    setFormData({});
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    
    if (modalType === 'purchase-order') {
      if (selectedItem) {
        setPurchaseOrders(purchaseOrders.map(po => po.id === selectedItem.id ? { ...formData, id: selectedItem.id } : po));
      } else {
        const newPO = { ...formData, id: `PO-2024-${String(purchaseOrders.length + 1).padStart(3, '0')}`, date: new Date().toISOString().split('T')[0], status: 'pending' };
        setPurchaseOrders([newPO, ...purchaseOrders]);
      }
    } else if (modalType === 'supplier') {
      if (selectedItem) {
        setSuppliers(suppliers.map(sup => sup.id === selectedItem.id ? { ...formData, id: selectedItem.id } : sup));
      } else {
        const newSupplier = { ...formData, id: `SUP-${String(suppliers.length + 1).padStart(3, '0')}`, rating: 0 };
        setSuppliers([newSupplier, ...suppliers]);
      }
    }
    
    closeModal();
  };

  const handleDelete = (type, id) => {
    if (window.confirm('Are you sure you want to delete this item?')) {
      if (type === 'purchase-order') {
        setPurchaseOrders(purchaseOrders.filter(po => po.id !== id));
      } else if (type === 'supplier') {
        setSuppliers(suppliers.filter(sup => sup.id !== id));
      }
    }
  };

  const updatePOStatus = (id, newStatus) => {
    setPurchaseOrders(purchaseOrders.map(po => po.id === id ? { ...po, status: newStatus } : po));
  };

  const getStatusColor = (status) => {
    switch(status) {
      case 'pending': return 'bg-yellow-100 text-yellow-800';
      case 'approved': return 'bg-blue-100 text-blue-800';
      case 'completed': return 'bg-green-100 text-green-800';
      case 'rejected': return 'bg-red-100 text-red-800';
      case 'critical': return 'bg-red-100 text-red-800';
      case 'low': return 'bg-yellow-100 text-yellow-800';
      case 'adequate': return 'bg-green-100 text-green-800';
      default: return 'bg-gray-100 text-gray-800';
    }
  };

  const getPriorityColor = (priority) => {
    switch(priority) {
      case 'high': return 'text-red-600';
      case 'medium': return 'text-yellow-600';
      case 'low': return 'text-green-600';
      default: return 'text-gray-600';
    }
  };

  const filteredPurchaseOrders = purchaseOrders.filter(po => {
    const matchesSearch = po.id.toLowerCase().includes(searchTerm.toLowerCase()) || 
                         po.vendor.toLowerCase().includes(searchTerm.toLowerCase()) ||
                         po.items.toLowerCase().includes(searchTerm.toLowerCase());
    const matchesFilter = filterStatus === 'all' || po.status === filterStatus;
    return matchesSearch && matchesFilter;
  });

  const filteredSuppliers = suppliers.filter(sup => 
    sup.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
    sup.contact.toLowerCase().includes(searchTerm.toLowerCase()) ||
    sup.category.toLowerCase().includes(searchTerm.toLowerCase())
  );

  const filteredInventory = inventory.filter(inv =>
    inv.item.toLowerCase().includes(searchTerm.toLowerCase())
  );

  // Dashboard Statistics
  const stats = {
    totalPOs: purchaseOrders.length,
    pendingPOs: purchaseOrders.filter(po => po.status === 'pending').length,
    totalSuppliers: suppliers.length,
    lowStock: inventory.filter(inv => inv.status === 'low' || inv.status === 'critical').length,
    totalSpent: purchaseOrders.reduce((sum, po) => sum + po.amount, 0),
  };

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Header */}
      <header className="bg-blue-600 text-white shadow-lg">
        <div className="max-w-7xl mx-auto px-4 py-4">
          <div className="flex items-center justify-between">
            <div className="flex items-center space-x-3">
              <Package className="w-8 h-8" />
              <div>
                <h1 className="text-2xl font-bold">Procurement Management System</h1>
                <p className="text-blue-100 text-sm">Officer Dashboard</p>
              </div>
            </div>
            <div className="flex items-center space-x-4">
              <div className="text-right">
                <p className="text-sm font-medium">Officer Portal</p>
                <p className="text-xs text-blue-200">November 12, 2024</p>
              </div>
            </div>
          </div>
        </div>
      </header>

      {/* Navigation Tabs */}
      <nav className="bg-white shadow-sm border-b">
        <div className="max-w-7xl mx-auto px-4">
          <div className="flex space-x-1">
            {[
              { id: 'dashboard', label: 'Dashboard', icon: TrendingUp },
              { id: 'purchase-orders', label: 'Purchase Orders', icon: FileText },
              { id: 'suppliers', label: 'Suppliers', icon: Users },
              { id: 'inventory', label: 'Inventory', icon: Package },
            ].map(tab => (
              <button
                key={tab.id}
                onClick={() => setActiveTab(tab.id)}
                className={`flex items-center space-x-2 px-6 py-4 font-medium transition-colors ${
                  activeTab === tab.id
                    ? 'text-blue-600 border-b-2 border-blue-600'
                    : 'text-gray-600 hover:text-blue-600'
                }`}
              >
                <tab.icon className="w-4 h-4" />
                <span>{tab.label}</span>
              </button>
            ))}
          </div>
        </div>
      </nav>

      {/* Main Content */}
      <main className="max-w-7xl mx-auto px-4 py-6">
        {/* Dashboard Tab */}
        {activeTab === 'dashboard' && (
          <div className="space-y-6">
            <h2 className="text-2xl font-bold text-gray-800">Dashboard Overview</h2>
            
            {/* Statistics Cards */}
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
              <div className="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
                <div className="flex items-center justify-between">
                  <div>
                    <p className="text-gray-600 text-sm">Total Purchase Orders</p>
                    <p className="text-3xl font-bold text-gray-800 mt-2">{stats.totalPOs}</p>
                  </div>
                  <FileText className="w-12 h-12 text-blue-500 opacity-20" />
                </div>
              </div>

              <div className="bg-white rounded-lg shadow-md p-6 border-l-4 border-yellow-500">
                <div className="flex items-center justify-between">
                  <div>
                    <p className="text-gray-600 text-sm">Pending Approval</p>
                    <p className="text-3xl font-bold text-gray-800 mt-2">{stats.pendingPOs}</p>
                  </div>
                  <Clock className="w-12 h-12 text-yellow-500 opacity-20" />
                </div>
              </div>

              <div className="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
                <div className="flex items-center justify-between">
                  <div>
                    <p className="text-gray-600 text-sm">Active Suppliers</p>
                    <p className="text-3xl font-bold text-gray-800 mt-2">{stats.totalSuppliers}</p>
                  </div>
                  <Users className="w-12 h-12 text-green-500 opacity-20" />
                </div>
              </div>

              <div className="bg-white rounded-lg shadow-md p-6 border-l-4 border-red-500">
                <div className="flex items-center justify-between">
                  <div>
                    <p className="text-gray-600 text-sm">Low Stock Items</p>
                    <p className="text-3xl font-bold text-gray-800 mt-2">{stats.lowStock}</p>
                  </div>
                  <AlertCircle className="w-12 h-12 text-red-500 opacity-20" />
                </div>
              </div>
            </div>

            {/* Recent Activities */}
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <div className="bg-white rounded-lg shadow-md p-6">
                <h3 className="text-lg font-semibold text-gray-800 mb-4">Recent Purchase Orders</h3>
                <div className="space-y-3">
                  {purchaseOrders.slice(0, 5).map(po => (
                    <div key={po.id} className="flex items-center justify-between p-3 bg-gray-50 rounded">
                      <div>
                        <p className="font-medium text-gray-800">{po.id}</p>
                        <p className="text-sm text-gray-600">{po.vendor}</p>
                      </div>
                      <span className={`px-3 py-1 rounded-full text-xs font-medium ${getStatusColor(po.status)}`}>
                        {po.status}
                      </span>
                    </div>
                  ))}
                </div>
              </div>

              <div className="bg-white rounded-lg shadow-md p-6">
                <h3 className="text-lg font-semibold text-gray-800 mb-4">Critical Inventory Items</h3>
                <div className="space-y-3">
                  {inventory.filter(inv => inv.status === 'critical' || inv.status === 'low').map(inv => (
                    <div key={inv.id} className="flex items-center justify-between p-3 bg-gray-50 rounded">
                      <div>
                        <p className="font-medium text-gray-800">{inv.item}</p>
                        <p className="text-sm text-gray-600">Qty: {inv.quantity} {inv.unit}</p>
                      </div>
                      <span className={`px-3 py-1 rounded-full text-xs font-medium ${getStatusColor(inv.status)}`}>
                        {inv.status}
                      </span>
                    </div>
                  ))}
                </div>
              </div>
            </div>
          </div>
        )}

        {/* Purchase Orders Tab */}
        {activeTab === 'purchase-orders' && (
          <div className="space-y-6">
            <div className="flex items-center justify-between">
              <h2 className="text-2xl font-bold text-gray-800">Purchase Orders</h2>
              <button
                onClick={() => openModal('purchase-order')}
                className="flex items-center space-x-2 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors"
              >
                <Plus className="w-5 h-5" />
                <span>New Purchase Order</span>
              </button>
            </div>

            {/* Search and Filter */}
            <div className="flex space-x-4">
              <div className="flex-1 relative">
                <Search className="absolute left-3 top-3 w-5 h-5 text-gray-400" />
                <input
                  type="text"
                  placeholder="Search purchase orders..."
                  value={searchTerm}
                  onChange={(e) => setSearchTerm(e.target.value)}
                  className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
              </div>
              <select
                value={filterStatus}
                onChange={(e) => setFilterStatus(e.target.value)}
                className="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="all">All Status</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="completed">Completed</option>
              </select>
            </div>

            {/* Purchase Orders Table */}
            <div className="bg-white rounded-lg shadow-md overflow-hidden">
              <table className="w-full">
                <thead className="bg-gray-50 border-b">
                  <tr>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">PO Number</th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vendor</th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Items</th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Priority</th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-200">
                  {filteredPurchaseOrders.map(po => (
                    <tr key={po.id} className="hover:bg-gray-50">
                      <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{po.id}</td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{po.vendor}</td>
                      <td className="px-6 py-4 text-sm text-gray-600">{po.items}</td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">₱{po.amount.toLocaleString()}</td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{po.date}</td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm">
                        <span className={`font-medium ${getPriorityColor(po.priority)}`}>
                          {po.priority}
                        </span>
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap">
                        <span className={`px-3 py-1 rounded-full text-xs font-medium ${getStatusColor(po.status)}`}>
                          {po.status}
                        </span>
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm">
                        <div className="flex space-x-2">
                          <button className="text-blue-600 hover:text-blue-800" title="View">
                            <Eye className="w-4 h-4" />
                          </button>
                          <button onClick={() => openModal('purchase-order', po)} className="text-green-600 hover:text-green-800" title="Edit">
                            <Edit className="w-4 h-4" />
                          </button>
                          <button onClick={() => handleDelete('purchase-order', po.id)} className="text-red-600 hover:text-red-800" title="Delete">
                            <Trash2 className="w-4 h-4" />
                          </button>
                        </div>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        )}

        {/* Suppliers Tab */}
        {activeTab === 'suppliers' && (
          <div className="space-y-6">
            <div className="flex items-center justify-between">
              <h2 className="text-2xl font-bold text-gray-800">Suppliers</h2>
              <button
                onClick={() => openModal('supplier')}
                className="flex items-center space-x-2 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors"
              >
                <Plus className="w-5 h-5" />
                <span>Add Supplier</span>
              </button>
            </div>

            {/* Search */}
            <div className="relative">
              <Search className="absolute left-3 top-3 w-5 h-5 text-gray-400" />
              <input
                type="text"
                placeholder="Search suppliers..."
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>

            {/* Suppliers Grid */}
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              {filteredSuppliers.map(supplier => (
                <div key={supplier.id} className="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                  <div className="flex items-start justify-between mb-4">
                    <div>
                      <h3 className="text-lg font-semibold text-gray-800">{supplier.name}</h3>
                      <p className="text-sm text-gray-600">{supplier.id}</p>
                    </div>
                    <span className="px-3 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded-full">
                      {supplier.category}
                    </span>
                  </div>
                  
                  <div className="space-y-2 mb-4">
                    <p className="text-sm text-gray-600"><span className="font-medium">Contact:</span> {supplier.contact}</p>
                    <p className="text-sm text-gray-600"><span className="font-medium">Email:</span> {supplier.email}</p>
                    <p className="text-sm text-gray-600"><span className="font-medium">Phone:</span> {supplier.phone}</p>
                    <div className="flex items-center space-x-1">
                      <span className="font-medium text-sm text-gray-600">Rating:</span>
                      <span className="text-yellow-500">★</span>
                      <span className="text-sm font-medium">{supplier.rating}</span>
                    </div>
                  </div>

                  <div className="flex space-x-2">
                    <button onClick={() => openModal('supplier', supplier)} className="flex-1 px-3 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 transition-colors">
                      Edit
                    </button>
                    <button onClick={() => handleDelete('supplier', supplier.id)} className="px-3 py-2 bg-red-600 text-white text-sm rounded hover:bg-red-700 transition-colors">
                      Delete
                    </button>
                  </div>
                </div>
              ))}
            </div>
          </div>
        )}

        {/* Inventory Tab */}
        {activeTab === 'inventory' && (
          <div className="space-y-6">
            <div className="flex items-center justify-between">
              <h2 className="text-2xl font-bold text-gray-800">Inventory Management</h2>
            </div>

            {/* Search */}
            <div className="relative">
              <Search className="absolute left-3 top-3 w-5 h-5 text-gray-400" />
              <input
                type="text"
                placeholder="Search inventory items..."
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>

            {/* Inventory Table */}
            <div className="bg-white rounded-lg shadow-md overflow-hidden">
              <table className="w-full">
                <thead className="bg-gray-50 border-b">
                  <tr>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item ID</th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item Name</th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit</th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reorder Level</th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Last Ordered</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-200">
                  {filteredInventory.map(item => (
                    <tr key={item.id} className="hover:bg-gray-50">
                      <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{item.id}</td>
                      <td className="px-6 py-4 text-sm text-gray-900">{item.item}</td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">{item.quantity}</td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{item.unit}</td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{item.reorderLevel}</td>
                      <td className="px-6 py-4 whitespace-nowrap">
                        <span className={`px-3 py-1 rounded-full text-xs font-medium ${getStatusColor(item.status)}`}>
                          {item.status}
                        </span>
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{item.lastOrdered}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        )}
      </main>

      {/* Modal */}
      {showModal && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
          <div className="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div className="flex items-center justify-between p-6 border-b">
              <h3 className="text-xl font-semibold text-gray-800">
                {selectedItem ? 'Edit' : 'New'} {modalType === 'purchase-order' ? 'Purchase Order' : 'Supplier'}
              </h3>
              <button onClick={closeModal} className="text-gray-400 hover:text-gray-600">
                <X className="w-6 h-6" />
              </button>
            </div>

            <form onSubmit={handleSubmit} className="p-6 space-y-4">
              {modalType === 'purchase-order' && (
                <>
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">Vendor</label>
                    <input
                      type="text"
                      name="vendor"
                      value={formData.vendor || ''}
                      onChange={handleInputChange}
                      required
                      className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">Items</label>
                    <input
                      type="text"
                      name="items"
                      value={formData.items || ''}
                      onChange={handleInputChange}
                      required
                      className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                  </div>

                  <div className="grid grid-cols-2 gap-4">
                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-1">Amount (₱)</label>
                      <input
                        type="number"
                        name="amount"
                        value={formData.amount || ''}
                        onChange={handleInputChange}
                        required
                        className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                      />
                    </div>

                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                      <select
                        name="priority"
                        value={formData.priority || 'medium'}
                        onChange={handleInputChange}
                        className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                      >
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                      </select>
                    </div>
                  </div>

                  {selectedItem && (
                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-1">Status</label>
                      <select
                        name="status"
                        value={formData.status || 'pending'}
                        onChange={handleInputChange}
                        className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                      >
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="completed">Completed</option>
                        <option value="rejected">Rejected</option>
                      </select>
                    </div>
                  )}
                </>
              )}

              {modalType === 'supplier' && (
                <>
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">Supplier Name</label>
                    <input
                      type="text"
                      name="name"
                      value={formData.name || ''}
                      onChange={handleInputChange}
                      required
                      className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">Contact Person</label>
                    <input
                      type="text"
                      name="contact"
                      value={formData.contact || ''}
                      onChange={handleInputChange}
                      required
                      className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                  </div>

                  <div className="grid grid-cols-2 gap-4">
                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-1">Email</label>
                      <input
                        type="email"
                        name="email"
                        value={formData.email || ''}
                        onChange={handleInputChange}
                        required
                        className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                      />
                    </div>

                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                      <input
                        type="tel"
                        name="phone"
                        value={formData.phone || ''}
                        onChange={handleInputChange}
                        required
                        className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                      />
                    </div>
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select
                      name="category"
                      value={formData.category || ''}
                      onChange={handleInputChange}
                      required
                      className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                      <option value="">Select Category</option>
                      <option value="Office Supplies">Office Supplies</option>
                      <option value="Technology">Technology</option>
                      <option value="Furniture">Furniture</option>
                      <option value="Energy">Energy</option>
                      <option value="Maintenance">Maintenance</option>
                      <option value="Food & Beverage">Food & Beverage</option>
                    </select>
                  </div>
                </>
              )}

              <div className="flex justify-end space-x-3 pt-4 border-t">
                <button
                  type="button"
                  onClick={closeModal}
                  className="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                >
                  {selectedItem ? 'Update' : 'Create'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};

export default ProcurementOfficerApp;