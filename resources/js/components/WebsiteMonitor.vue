<template>
  <div class="min-h-screen bg-gray-50 p-6">
    <div class="max-w-4xl mx-auto">
      <!-- Header -->
      <header class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Website Uptime Monitor</h1>
        <p class="text-gray-600 mt-2">Monitor your websites and receive instant alerts when they're down.</p>
      </header>

      <!-- Main Content -->
      <div class="bg-white rounded-lg shadow p-6">
        <!-- Debug info (temporary) -->
        <div v-if="clients.length > 0" class="mb-4 p-2 bg-yellow-50 text-sm text-gray-700 rounded">
          <p>✅ Loaded {{ clients.length }} clients. Select one below:</p>
        </div>

        <!-- Client Selection -->
        <div class="mb-8">
          <label for="client-select" class="block text-sm font-medium text-gray-700 mb-2">
            Select Client Email
          </label>
          <select
            id="client-select"
            v-model="selectedClientId"
            @change="loadClientWebsites"
            class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
            :disabled="loadingClients"
          >
            <option value="">Select a client...</option>
            <option v-if="loadingClients" disabled>Loading clients...</option>
            <option v-else-if="clients.length === 0" disabled>No clients found</option>
            <option v-else v-for="client in clients" :key="client.id" :value="client.id">
              {{ client.email }} ({{ getWebsiteCount(client) }} websites)
            </option>
          </select>
          
          <!-- Loading indicator -->
          <div v-if="loadingClients" class="mt-2 text-sm text-blue-600">
            ⏳ Loading client list...
          </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="text-center py-8">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
          <p class="mt-2 text-gray-600">Loading websites...</p>
        </div>

        <!-- Websites List -->
        <div v-else-if="selectedClient && websites.length > 0">
          <h2 class="text-xl font-semibold text-gray-800 mb-4">
            Websites for {{ selectedClient.email }}
          </h2>
          
          <ul class="space-y-3">
            <li
              v-for="website in websites"
              :key="website.id"
              class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition"
            >
              <div class="flex items-center space-x-3">
                <!-- Status Indicator -->
                <span
                class="status-dot me-2"
                :class="{
                    'bg-success': website.status === 'up',
                    'bg-danger': website.status === 'down',
                    'bg-secondary': website.status === 'checking'
                }"
                :title="`Status: ${website.status}` "
                ></span>
                
                <!-- Website URL -->
                <a
                  href="#"
                  @click.prevent="confirmVisit(website)"
                  class="text-blue-600 hover:text-blue-800 hover:underline font-medium cursor-pointer"
                >
                  {{ website.url }}
                </a>
              </div>
              
              <div class="text-sm text-gray-500">
                Last checked: {{ formatDate(website.last_checked_at) }}
              </div>
            </li>
          </ul>
          
          <div class="mt-4 text-sm text-gray-500">
            Total: {{ websites.length }} websites • 
            <span class="text-success">{{ upCount }} up</span> • 
            <span class="text-danger">{{ downCount }} down</span> • 
            <span class="text-secondary">{{ checkingCount }} checking</span>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else-if="selectedClient && websites.length === 0" class="text-center py-8">
          <p class="text-gray-500">No websites found for this client.</p>
        </div>

        <!-- Instructions -->
        <div v-else class="text-center py-8 text-gray-500">
          <p v-if="clients.length > 0">Select a client email above to view their monitored websites.</p>
          <p v-else-if="loadingClients">Loading client list...</p>
          <p v-else>No clients available. Please check database.</p>
        </div>
      </div>

      <!-- Footer Info -->
      <div class="mt-6 text-sm text-gray-500 text-center">
        <p>Websites are checked every 15 minutes. Alerts are sent immediately when a website goes down.</p>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import Swal from 'sweetalert2';

export default {
  name: 'WebsiteMonitor',
  
  data() {
    return {
      clients: [],
      selectedClientId: '',
      selectedClient: null,
      websites: [],
      loading: false,
      loadingClients: true,
    };
  },
  
  computed: {
    upCount() {
      return this.websites.filter(w => w.status === 'up').length;
    },
    downCount() {
      return this.websites.filter(w => w.status === 'down').length;
    },
    checkingCount() {
      return this.websites.filter(w => w.status === 'checking').length;
    },
  },
  
  mounted() {
    this.loadClients();
  },
  
  methods: {
    async loadClients() {
      try {
        console.log('Loading clients from API...');
        const response = await axios.get('/api/v1/clients');
        console.log('API Response:', response.data);
        
        // Handle different response structures
        let clientsData = response.data;
        
        // If response has {data: [...]} structure
        if (response.data && response.data.data) {
          clientsData = response.data.data;
        }
        // If response is wrapped in another property
        else if (response.data && Array.isArray(response.data.data)) {
          clientsData = response.data.data;
        }
        
        console.log('Processed clients data:', clientsData);
        
        if (Array.isArray(clientsData)) {
          this.clients = clientsData;
          console.log(`✅ Loaded ${this.clients.length} clients`);
          
          // Log first client for debugging
          if (this.clients.length > 0) {
            console.log('First client:', this.clients[0]);
            console.log('First client email:', this.clients[0].email);
            console.log('First client website_count:', this.clients[0].website_count);
          }
        } else {
          console.error('Unexpected data format:', clientsData);
          this.clients = [];
        }
      } catch (error) {
        console.error('Error loading clients:', error);
        console.error('Error details:', error.response?.data || error.message);
        this.clients = [];
        
        // Show error to user
        Swal.fire({
          title: 'Error',
          text: 'Failed to load clients. Please check console for details.',
          icon: 'error',
        });
      } finally {
        // IMPORTANT: Always set loadingClients to false
        this.loadingClients = false;
        console.log('loadingClients set to:', this.loadingClients);
      }
    },
    
    getWebsiteCount(client) {
      // Safely get website count
      return client.website_count || client.websites_count || client.websites?.length || 0;
    },
    
    async loadClientWebsites() {
      if (!this.selectedClientId) {
        this.selectedClient = null;
        this.websites = [];
        return;
      }
      
      this.loading = true;
      
      try {
        console.log(`Loading websites for client ${this.selectedClientId}...`);
        const response = await axios.get(`/api/v1/clients/${this.selectedClientId}/websites`);
        console.log('Websites API response:', response.data);
        
        this.selectedClient = response.data.client || this.clients.find(c => c.id == this.selectedClientId);
        this.websites = response.data.websites || [];
        
        console.log(`✅ Loaded ${this.websites.length} websites for ${this.selectedClient?.email}`);
      } catch (error) {
        console.error('Error loading websites:', error);
        
        // Fallback: Use the client from our list
        this.selectedClient = this.clients.find(c => c.id == this.selectedClientId) || { email: 'Unknown Client' };
        this.websites = [];
        
        Swal.fire({
          title: 'Info',
          text: 'Could not load websites. Using fallback data.',
          icon: 'info',
        });
      } finally {
        this.loading = false;
      }
    },
    
    formatUrl(url) {
      if (!url.match(/^https?:\/\//)) {
        return 'http://' + url;
      }
      return url;
    },
    
    formatDate(dateString) {
      if (!dateString) return 'Never';
      
      const date = new Date(dateString);
      const now = new Date();
      const diffMs = now - date;
      const diffMins = Math.floor(diffMs / 60000);
      
      if (diffMins < 1) return 'Just now';
      if (diffMins < 60) return `${diffMins} minutes ago`;
      if (diffMins < 1440) return `${Math.floor(diffMins / 60)} hours ago`;
      return date.toLocaleDateString();
    },
    
    confirmVisit(website) {
      // Simple version without HTML to avoid rendering issues
      Swal.fire({
        title: 'Visit Website',
        text: `You are about to visit: ${website.url}\nDo you want to continue?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Continue',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
      }).then((result) => {
        if (result.isConfirmed) {
          window.open(this.formatUrl(website.url), '_blank', 'noopener,noreferrer');
        }
      });
    },
  },
};
</script>

<style scoped>
/* Make sure dropdown is visible */
select {
  min-height: 42px;
  background-color: white !important;
  color: #374151 !important; /* gray-700 */
}

select option {
  color: #374151;
  padding: 8px;
}

/* Style for the debug box */
.bg-yellow-50 {
  background-color: #fffbeb;
}
.status-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  display: inline-block;
}

</style>