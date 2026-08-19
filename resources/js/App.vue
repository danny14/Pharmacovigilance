<template>
  <div class="min-h-screen flex flex-col">
    <!-- Navbar (solo si está autenticado) -->
    <nav v-if="isAuthenticated" class="bg-blue-800 text-white p-4 shadow-md flex justify-between items-center">
      <div class="text-xl font-bold">Pharmacovigilance Alert System</div>
      <button @click="logout" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded font-medium transition">
        Logout
      </button>
    </nav>
    
    <!-- Contenido Principal -->
    <main class="flex-grow flex flex-col items-center p-6">
      <router-view />
    </main>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();

const isAuthenticated = computed(() => {
  return !!localStorage.getItem('token');
});

const logout = async () => {
  try {
    await axios.post('/api/logout');
  } catch (error) {
    console.error('Error logging out', error);
  } finally {
    localStorage.removeItem('token');
    router.push('/login');
  }
};
</script>
