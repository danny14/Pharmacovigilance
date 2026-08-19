<template>
  <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="mb-8 flex justify-between items-end">
      <div>
        <h1 class="text-3xl font-extrabold text-gray-900">Pharmacovigilance Dashboard</h1>
        <p class="mt-2 text-sm text-gray-600">Identify and recall compromised medication lots.</p>
      </div>
    </div>

    <!-- Search Filters -->
    <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200 mb-8">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 items-end">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Lot Number *</label>
          <input v-model="filters.lot" type="text" placeholder="e.g. 951357" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 border">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
          <input v-model="filters.start_date" type="date" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 border">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
          <input v-model="filters.end_date" type="date" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 border">
        </div>
        <div>
          <button @click="search" :disabled="!filters.lot || loading" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md shadow-sm disabled:opacity-50 transition">
            {{ loading ? 'Searching...' : 'Search' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Results Table -->
    <div v-if="orders.length > 0" class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
      <div class="p-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
        <h3 class="text-lg font-semibold text-gray-800">Order Results</h3>
        <div class="space-x-3">
          <button @click="exportToCSV" class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold py-2 px-4 rounded-md shadow transition inline-flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            Export to CSV
          </button>
          <button v-if="userRole === 'admin'" @click="openBulkAlertModal" class="bg-red-600 hover:bg-red-700 text-white text-sm font-bold py-2 px-4 rounded-md shadow transition">
            Send Bulk Alert
          </button>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th v-if="userRole === 'admin'" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                <input type="checkbox" @change="toggleAll" :checked="selectedOrders.length === orders.length && orders.length > 0" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Purchase Date</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="order in orders" :key="order.id" class="hover:bg-gray-50">
              <td v-if="userRole === 'admin'" class="px-6 py-4 whitespace-nowrap">
                <input type="checkbox" :value="order.id" v-model="selectedOrders" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">#{{ order.id }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ order.customer.name }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ order.customer.email }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ new Date(order.purchase_date).toLocaleDateString() }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium flex space-x-2">
                <button @click="viewOrder(order)" class="inline-flex items-center bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200 px-3 py-1.5 rounded-md text-xs font-bold transition">
                  <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                  </svg>
                  View Order
                </button>
                <button @click="viewCustomer(order.customer)" class="inline-flex items-center bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200 px-3 py-1.5 rounded-md text-xs font-bold transition">
                  <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                  </svg>
                  View Buyer
                </button>
                <button v-if="userRole === 'admin'" @click="openSingleAlertModal(order)" class="inline-flex items-center bg-red-50 text-red-700 hover:bg-red-100 border border-red-200 px-3 py-1.5 rounded-md text-xs font-bold transition">
                  <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                  </svg>
                  Alert Buyer
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <!-- Pagination -->
      <div class="px-6 py-3 bg-gray-50 border-t border-gray-200 flex justify-between items-center">
        <span class="text-sm text-gray-700">Showing page {{ pagination.current_page }} of {{ pagination.last_page }}</span>
        <div class="space-x-2">
          <button @click="searchPage(pagination.current_page - 1)" :disabled="pagination.current_page === 1" class="px-3 py-1 border border-gray-300 rounded bg-white text-sm hover:bg-gray-50 disabled:opacity-50 transition">Previous</button>
          <button @click="searchPage(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page" class="px-3 py-1 border border-gray-300 rounded bg-white text-sm hover:bg-gray-50 disabled:opacity-50 transition">Next</button>
        </div>
      </div>
    </div>

    <div v-else-if="searched && !loading" class="text-center py-12 bg-white rounded-lg shadow-md border border-gray-200 mt-6">
      <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-gray-100 mb-4">
        <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
        </svg>
      </div>
      <h3 class="text-lg font-medium text-gray-900">No orders found</h3>
      <p class="text-gray-500 mt-1">We couldn't find any orders matching this lot number in the given date range.</p>
    </div>

    <!-- Alert History Table -->
    <div class="mt-12 bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
      <div class="p-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
        <h3 class="text-lg font-semibold text-gray-800">Alert History Log</h3>
        <button @click="fetchAlertHistory" class="text-gray-500 hover:text-gray-700 transition">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
        </button>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Alert ID</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sent At</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-if="alertHistory.length === 0">
              <td colspan="5" class="px-6 py-8 text-center text-gray-500 text-sm">No alerts sent yet.</td>
            </tr>
            <tr v-for="alertItem in alertHistory" :key="alertItem.id" class="hover:bg-gray-50 transition">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#{{ alertItem.id }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ new Date(alertItem.sent_at).toLocaleString() }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ alertItem.order.customer.name }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ alertItem.order.customer.email }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center"><span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full">#{{ alertItem.order_id }}</span></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Alert Modal (Confirm Action) -->
    <div v-if="alertModal.show" class="fixed inset-0 bg-gray-900 bg-opacity-75 overflow-y-auto h-full w-full flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded-xl shadow-2xl w-full max-w-md border border-gray-200 transform transition-all">
        <div class="flex items-center mb-4">
          <div class="flex-shrink-0 bg-red-100 p-2 rounded-full mr-3">
            <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
          </div>
          <h3 class="text-xl font-bold text-gray-900">Confirm Alert</h3>
        </div>
        <p class="text-gray-600 mb-6">Are you sure you want to send a recall alert to <span class="font-bold text-gray-900">{{ alertModal.isBulk ? selectedOrders.length + ' selected customers' : 'this customer' }}</span> for lot <span class="font-bold text-gray-900">{{ filters.lot }}</span>?</p>
        <div class="flex justify-end space-x-3">
          <button @click="alertModal.show = false" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 font-medium transition">Cancel</button>
          <button @click="sendAlerts" :disabled="alerting" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md shadow disabled:opacity-50 font-medium flex items-center transition">
            <svg v-if="alerting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            {{ alerting ? 'Sending...' : 'Yes, Send Alert' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Info Modal (Order/Customer Details) -->
    <div v-if="infoModal.show" class="fixed inset-0 bg-gray-900 bg-opacity-75 overflow-y-auto h-full w-full flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded-xl shadow-2xl w-full max-w-lg border border-gray-200">
        <div class="flex justify-between items-center mb-4 border-b pb-3">
          <h3 class="text-xl font-bold text-gray-900">{{ infoModal.title }}</h3>
          <button @click="infoModal.show = false" class="text-gray-400 hover:text-gray-600">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        
        <div class="space-y-4 mb-6">
          <div v-for="(value, key) in infoModal.data" :key="key" class="bg-gray-50 p-3 rounded-md border border-gray-100">
            <span class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">{{ key }}</span>
            <span class="block text-sm font-medium text-gray-900">{{ value }}</span>
          </div>
        </div>

        <div class="flex justify-end">
          <button @click="infoModal.show = false" class="bg-gray-800 hover:bg-gray-900 text-white px-5 py-2 rounded-md font-medium transition">Close</button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';

const filters = reactive({
  lot: '',
  start_date: '',
  end_date: ''
});

const orders = ref([]);
const alertHistory = ref([]);
const selectedOrders = ref([]);
const pagination = ref({});
const loading = ref(false);
const searched = ref(false);
const alerting = ref(false);

const alertModal = reactive({
  show: false,
  isBulk: false,
  targetId: null
});

const infoModal = reactive({
  show: false,
  title: '',
  data: {}
});

const userRole = ref('viewer');

const fetchUser = async () => {
  try {
    const res = await axios.get('/api/user');
    userRole.value = res.data.role;
  } catch (err) {
    console.error("Failed to load user profile", err);
  }
};

const fetchAlertHistory = async () => {
  try {
    const res = await axios.get('/api/alerts');
    alertHistory.value = res.data;
  } catch (err) {
    console.error("Failed to load alert history", err);
  }
};

onMounted(() => {
  fetchUser();
  fetchAlertHistory();
});

const search = () => searchPage(1);

const exportToCSV = () => {
  const params = new URLSearchParams(filters).toString();
  // Using token in URL or fetch with blob for download
  axios.get(`/api/medications/export?${params}`, { responseType: 'blob' })
    .then(response => {
      const url = window.URL.createObjectURL(new Blob([response.data]));
      const link = document.createElement('a');
      link.href = url;
      link.setAttribute('download', `pharmacovigilance_export_lot_${filters.lot}.csv`);
      document.body.appendChild(link);
      link.click();
      link.parentNode.removeChild(link);
    })
    .catch(error => {
      infoModal.title = 'Export Failed';
      infoModal.data = { Error: 'Could not export the CSV file.' };
      infoModal.show = true;
    });
};

const searchPage = async (page) => {
  loading.value = true;
  try {
    const response = await axios.get('/api/medications/search', {
      params: { ...filters, page }
    });
    orders.value = response.data.data.data;
    pagination.value = {
      current_page: response.data.data.current_page,
      last_page: response.data.data.last_page,
      total: response.data.data.total
    };
    searched.value = true;
    selectedOrders.value = [];
  } catch (error) {
    infoModal.title = 'Search Failed';
    infoModal.data = { Error: error.response?.data?.message || error.message };
    infoModal.show = true;
  } finally {
    loading.value = false;
  }
};

const toggleAll = (e) => {
  if (e.target.checked) {
    selectedOrders.value = orders.value.map(o => o.id);
  } else {
    selectedOrders.value = [];
  }
};

const viewOrder = (order) => {
  infoModal.title = `Order Details #${order.id}`;
  infoModal.data = {
    'Purchase Date': new Date(order.purchase_date).toLocaleString(),
    'Items Purchased': order.order_items.map(i => i.medication.name).join(', '),
    'Total Lot Matches': order.order_items.filter(i => i.medication.lot_number === filters.lot).length
  };
  infoModal.show = true;
};

const viewCustomer = (customer) => {
  infoModal.title = 'Customer Profile';
  infoModal.data = {
    'Full Name': customer.name,
    'Email Address': customer.email,
    'Phone Number': customer.phone || 'N/A',
    'Customer ID': `#${customer.id}`
  };
  infoModal.show = true;
};

const openSingleAlertModal = (order) => {
  alertModal.isBulk = false;
  alertModal.targetId = order.id;
  alertModal.show = true;
};

const openBulkAlertModal = () => {
  if (selectedOrders.value.length === 0) {
    infoModal.title = 'Selection Required';
    infoModal.data = { Notice: 'Please select at least one order to alert.' };
    infoModal.show = true;
    return;
  }
  alertModal.isBulk = true;
  alertModal.show = true;
};

const sendAlerts = async () => {
  alerting.value = true;
  const payload = {
    order_ids: alertModal.isBulk ? selectedOrders.value : [alertModal.targetId],
    lot: filters.lot
  };

  try {
    const res = await axios.post('/api/alerts/send', payload);
    alertModal.show = false;
    
    infoModal.title = 'Alerts Dispatched';
    infoModal.data = {
      Status: 'Success',
      'Alerts Sent': res.data.summary.success,
      'Failed': res.data.summary.failed
    };
    infoModal.show = true;
    
    // Recargar historial!
    fetchAlertHistory();
  } catch (error) {
    alertModal.show = false;
    infoModal.title = 'Failed to Send Alerts';
    infoModal.data = { Error: error.response?.data?.message || error.message };
    infoModal.show = true;
  } finally {
    alerting.value = false;
  }
};
</script>
