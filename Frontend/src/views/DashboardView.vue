<template>
  <div class="dashboard">
    <header class="dashboard-header">
      <h1>Dashboard</h1>
      <div class="user-info">
        <span>Olá, {{ user?.name }}</span>
        <button @click="handleLogout" class="btn btn-outline">
          Sair
        </button>
      </div>
    </header>
    
    <main class="dashboard-content">
      <div class="welcome-card">
        <h2>Bem-vindo ao OnHappy!</h2>
        <p>Sistema de gerenciamento de pedidos de viagem</p>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import { useAuth } from '../composables/useAuth'

const { user, logout } = useAuth()

const handleLogout = async () => {
  await logout()
}
</script>

<style scoped>
.dashboard {
  min-height: 100vh;
  background-color: #f8fafc;
}

.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 2rem;
  background: white;
  border-bottom: 1px solid #e2e8f0;
}

.dashboard-header h1 {
  margin: 0;
  color: #1a202c;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.user-info span {
  color: #4a5568;
}

.dashboard-content {
  padding: 2rem;
}

.welcome-card {
  background: white;
  padding: 2rem;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  max-width: 600px;
}

.welcome-card h2 {
  margin: 0 0 1rem 0;
  color: #1a202c;
}

.welcome-card p {
  margin: 0;
  color: #4a5568;
}

.btn {
  padding: 0.5rem 1rem;
  border-radius: 6px;
  border: none;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-outline {
  background: transparent;
  border: 1px solid #e2e8f0;
  color: #4a5568;
}

.btn-outline:hover {
  background: #f7fafc;
  border-color: #cbd5e0;
}
</style>