<template>
  <header class="bg-white border-b border-gray-200 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center h-14">
        <div class="flex items-center space-x-8">
          <div class="flex items-center space-x-2">
            <PlaneTakeoff class="text-primary h-6 w-6" />
            <span class="text-xl font-bold italic text-primary">TravelsCorp</span>
          </div>

          <nav class="hidden md:flex space-x-6">
            <router-link
              v-for="item in navigationItems"
              :key="item.name"
              :to="item.href"
              class="relative px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-900 transition-colors duration-200"
              :class="{ 'text-primary': isActive(item.href) }"
            >
              {{ item.name }}
              <div
                v-if="isActive(item.href)"
                class="absolute -bottom-2.5 left-0 right-0 h-1 bg-primary"
              />
            </router-link>
          </nav>
        </div>

        <div class="ml-auto flex items-center space-x-4">
          <div class="relative" ref="dropdownRef">
            <button
              @click="toggleDropdown"
              class="flex items-center space-x-2 text-sm font-medium text-gray-700 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 rounded-md px-3 py-2 transition-colors"
            >
              <div class="w-8 h-8 bg-primary rounded-full flex items-center justify-center text-white text-sm font-semibold">
                {{ userInitials }}
              </div>
              <div class="hidden sm:flex items-center space-x-2">
                <span>{{ user?.name }}</span>
                <Badge v-if="isAdmin" variant="secondary" class="text-xs px-2 py-0.5">
                  <Crown class="w-3 h-3 mr-1" />
                  Admin
                </Badge>
              </div>
              <ChevronDown class="w-4 h-4" :class="{ 'rotate-180': isDropdownOpen }" />
            </button>

            <Transition
              enter-active-class="transition ease-out duration-100"
              enter-from-class="transform opacity-0 scale-95"
              enter-to-class="transform opacity-100 scale-100"
              leave-active-class="transition ease-in duration-75"
              leave-from-class="transform opacity-100 scale-100"
              leave-to-class="transform opacity-0 scale-95"
            >
              <div
                v-if="isDropdownOpen"
                class="absolute right-0 mt-2 bg-white rounded-md shadow-lg border border-gray-200 py-1"
              >
                <div class="px-4 py-2 text-sm text-gray-900 border-b border-gray-100">
                  <div class="flex items-center justify-between">
                    <div>
                      <p class="font-medium">{{ user?.name }}</p>
                      <p class="text-gray-500 truncate">{{ user?.email }}</p>
                    </div>
                  </div>
                </div>
                <button
                  @click="handleLogout"
                  class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center space-x-2"
                >
                  <LogOut class="w-4 h-4" />
                  <span>Sair</span>
                </button>
              </div>
            </Transition>
          </div>

          <button
            @click="toggleMobileMenu"
            class="md:hidden p-2 rounded-md text-gray-500 hover:text-gray-900 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
          >
            <Menu v-if="!isMobileMenuOpen" class="w-6 h-6" />
            <X v-else class="w-6 h-6" />
          </button>
        </div>
      </div>

      <Transition
        enter-active-class="transition ease-out duration-200"
        enter-from-class="opacity-0 -translate-y-1"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition ease-in duration-150"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 -translate-y-1"
      >
        <div v-if="isMobileMenuOpen" class="md:hidden border-t border-gray-200">
          <nav class="px-2 pt-2 pb-4 space-y-1">
            <router-link
              v-for="item in navigationItems"
              :key="item.name"
              :to="item.href"
              @click="closeMobileMenu"
              class="block px-3 py-2 text-base font-medium text-gray-500 hover:text-gray-900 hover:bg-gray-50 rounded-md transition-colors"
              :class="{ 'text-primary bg-primary/5': isActive(item.href) }"
            >
              {{ item.name }}
            </router-link>
          </nav>
        </div>
      </Transition>
    </div>
  </header>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute } from 'vue-router'
import { useAuth } from '@/composables/useAuth'
import { Badge } from '@/components/ui/badge'
import { PlaneTakeoff, ChevronDown, Menu, X, LogOut, Crown } from 'lucide-vue-next'

const route = useRoute()
const { user, isAdmin, logout } = useAuth()

const isDropdownOpen = ref(false)
const isMobileMenuOpen = ref(false)
const dropdownRef = ref<HTMLElement>()

const navigationItems = computed(() => {
  const baseItems = [
    { name: 'Dashboard', href: '/dashboard' }
  ]

  return baseItems
})

const userInitials = computed(() => {
  if (!user?.name) return 'U'
  return user.name
    .split(' ')
    .map((name: string) => name[0])
    .join('')
    .toUpperCase()
    .slice(0, 2)
})

const isActive = (href: string) => {
  return route.path === href
}

const toggleDropdown = () => {
  isDropdownOpen.value = !isDropdownOpen.value
  if (isDropdownOpen.value) {
    isMobileMenuOpen.value = false
  }
}

const toggleMobileMenu = () => {
  isMobileMenuOpen.value = !isMobileMenuOpen.value
  if (isMobileMenuOpen.value) {
    isDropdownOpen.value = false
  }
}

const closeMobileMenu = () => {
  isMobileMenuOpen.value = false
}

const closeDropdown = () => {
  isDropdownOpen.value = false
}

const handleLogout = () => {
  closeDropdown()
  logout()
}

const handleClickOutside = (event: Event) => {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target as Node)) {
    closeDropdown()
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>