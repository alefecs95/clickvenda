<template>
  <div class="permissions-view">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Gerenciar Permissões</h1>
        <p class="text-gray-600">Gerencie as permissões do sistema e suas atribuições</p>
      </div>
      <button
        @click="openCreateModal"
        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Nova Permissão
      </button>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
      <div class="flex flex-wrap gap-4">
        <div class="flex-1 min-w-64">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Buscar permissões..."
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          />
        </div>
        <div class="flex gap-2">
          <select
            v-model="moduleFilter"
            class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="">Todos os Módulos</option>
            <option v-for="module in availableModules" :key="module" :value="module">
              {{ module }}
            </option>
          </select>
          <select
            v-model="statusFilter"
            class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="">Todos os Status</option>
            <option value="active">Ativo</option>
            <option value="inactive">Inativo</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="rolesStore.loading" class="flex justify-center py-8">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
    </div>

    <!-- Error -->
    <div v-if="rolesStore.error" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
      <div class="flex">
        <svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
        </svg>
        <div class="ml-3">
          <p class="text-red-800">{{ rolesStore.error }}</p>
        </div>
      </div>
    </div>

    <!-- Tabela de Permissões -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Permissão
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Módulo
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Descrição
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Roles
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Status
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Ações
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="permission in filteredPermissions" :key="permission.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div>
                  <div class="text-sm font-medium text-gray-900">{{ permission.display_name }}</div>
                  <div class="text-sm text-gray-500">{{ permission.name }}</div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  v-if="permission.module"
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800"
                >
                  {{ permission.module }}
                </span>
                <span v-else class="text-gray-400">-</span>
              </td>
              <td class="px-6 py-4">
                <div class="text-sm text-gray-900">{{ permission.description || '-' }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex flex-wrap gap-1">
                  <span
                    v-for="role in permission.roles"
                    :key="role.id"
                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                  >
                    {{ role.display_name }}
                  </span>
                  <span v-if="!permission.roles?.length" class="text-gray-400 text-sm">
                    Nenhuma role
                  </span>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  :class="[
                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                    permission.active
                      ? 'bg-green-100 text-green-800'
                      : 'bg-red-100 text-red-800'
                  ]"
                >
                  {{ permission.active ? 'Ativo' : 'Inativo' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex items-center gap-2">
                  <button
                    @click="openEditModal(permission)"
                    class="text-blue-600 hover:text-blue-900"
                    title="Editar"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </button>
                  <button
                    @click="openRolesModal(permission)"
                    class="text-purple-600 hover:text-purple-900"
                    title="Gerenciar Roles"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                  </button>
                  <button
                    @click="togglePermissionStatus(permission)"
                    :class="[
                      'hover:opacity-75',
                      permission.active ? 'text-red-600' : 'text-green-600'
                    ]"
                    :title="permission.active ? 'Desativar' : 'Ativar'"
                  >
                    <svg v-if="permission.active" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728" />
                    </svg>
                    <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                  </button>
                  <button
                    @click="confirmDelete(permission)"
                    class="text-red-600 hover:text-red-900"
                    title="Excluir"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Empty State -->
      <div v-if="filteredPermissions.length === 0 && !rolesStore.loading" class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhuma permissão encontrada</h3>
        <p class="mt-1 text-sm text-gray-500">Comece criando uma nova permissão.</p>
      </div>
    </div>

    <!-- Modal Criar/Editar Permissão -->
    <div v-if="showPermissionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">
            {{ editingPermission ? 'Editar Permissão' : 'Nova Permissão' }}
          </h3>
          
          <form @submit.prevent="savePermission" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Nome da Permissão</label>
              <input
                v-model="permissionForm.name"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="ex: users.create"
              />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Nome de Exibição</label>
              <input
                v-model="permissionForm.display_name"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="ex: Criar Usuários"
              />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Módulo</label>
              <input
                v-model="permissionForm.module"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="ex: Usuários"
              />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
              <textarea
                v-model="permissionForm.description"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="Descrição da permissão..."
              ></textarea>
            </div>
            
            <div class="flex items-center">
              <input
                v-model="permissionForm.active"
                type="checkbox"
                id="active"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              />
              <label for="active" class="ml-2 block text-sm text-gray-900">
                Permissão ativa
              </label>
            </div>
            
            <div class="flex justify-end gap-3 pt-4">
              <button
                type="button"
                @click="closePermissionModal"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg"
              >
                Cancelar
              </button>
              <button
                type="submit"
                :disabled="rolesStore.loading"
                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg disabled:opacity-50"
              >
                {{ rolesStore.loading ? 'Salvando...' : 'Salvar' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Modal Gerenciar Roles -->
    <div v-if="showRolesModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-2/3 max-w-2xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">
            Gerenciar Roles - {{ selectedPermission?.display_name }}
          </h3>
          
          <div class="space-y-3">
            <div v-for="role in rolesStore.roles" :key="role.id" class="flex items-center">
              <input
                v-model="selectedRoles"
                :value="role.id"
                type="checkbox"
                :id="`role-${role.id}`"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              />
              <label :for="`role-${role.id}`" class="ml-3 block text-sm">
                <div class="font-medium text-gray-900">{{ role.display_name }}</div>
                <div class="text-gray-500">{{ role.description || role.name }}</div>
              </label>
            </div>
          </div>
          
          <div class="flex justify-end gap-3 pt-6">
            <button
              type="button"
              @click="closeRolesModal"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg"
            >
              Cancelar
            </button>
            <button
              @click="saveRoles"
              :disabled="rolesStore.loading"
              class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg disabled:opacity-50"
            >
              {{ rolesStore.loading ? 'Salvando...' : 'Salvar Roles' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRolesStore, type Permission } from '@/stores/roles'

const rolesStore = useRolesStore()

// Reactive data
const searchQuery = ref('')
const moduleFilter = ref('')
const statusFilter = ref('')
const showPermissionModal = ref(false)
const showRolesModal = ref(false)
const editingPermission = ref<Permission | null>(null)
const selectedPermission = ref<Permission | null>(null)
const selectedRoles = ref<number[]>([])

const permissionForm = ref({
  name: '',
  display_name: '',
  description: '',
  module: '',
  active: true
})

// Computed
const filteredPermissions = computed(() => {
  let filtered = rolesStore.permissions

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(permission =>
      permission.name.toLowerCase().includes(query) ||
      permission.display_name.toLowerCase().includes(query) ||
      (permission.description && permission.description.toLowerCase().includes(query)) ||
      (permission.module && permission.module.toLowerCase().includes(query))
    )
  }

  if (moduleFilter.value) {
    filtered = filtered.filter(permission =>
      permission.module === moduleFilter.value
    )
  }

  if (statusFilter.value) {
    filtered = filtered.filter(permission =>
      statusFilter.value === 'active' ? permission.active : !permission.active
    )
  }

  return filtered
})

const availableModules = computed(() => {
  const modules = new Set<string>()
  rolesStore.permissions.forEach(permission => {
    if (permission.module) {
      modules.add(permission.module)
    }
  })
  return Array.from(modules).sort()
})

// Methods
function openCreateModal() {
  editingPermission.value = null
  permissionForm.value = {
    name: '',
    display_name: '',
    description: '',
    module: '',
    active: true
  }
  showPermissionModal.value = true
}

function openEditModal(permission: Permission) {
  editingPermission.value = permission
  permissionForm.value = {
    name: permission.name,
    display_name: permission.display_name,
    description: permission.description || '',
    module: permission.module || '',
    active: permission.active
  }
  showPermissionModal.value = true
}

function closePermissionModal() {
  showPermissionModal.value = false
  editingPermission.value = null
}

async function savePermission() {
  try {
    if (editingPermission.value) {
      await rolesStore.updatePermission(editingPermission.value.id, permissionForm.value)
    } else {
      await rolesStore.createPermission(permissionForm.value)
    }
    closePermissionModal()
  } catch (error) {
    console.error('Erro ao salvar permissão:', error)
  }
}

async function openRolesModal(permission: Permission) {
  selectedPermission.value = permission
  selectedRoles.value = permission.roles?.map(r => r.id) || []
  showRolesModal.value = true
}

function closeRolesModal() {
  showRolesModal.value = false
  selectedPermission.value = null
  selectedRoles.value = []
}

async function saveRoles() {
  if (!selectedPermission.value) return
  
  try {
    await rolesStore.assignPermissionToRoles(selectedPermission.value.id, selectedRoles.value)
    closeRolesModal()
  } catch (error) {
    console.error('Erro ao salvar roles:', error)
  }
}

async function togglePermissionStatus(permission: Permission) {
  try {
    await rolesStore.togglePermissionStatus(permission.id)
  } catch (error) {
    console.error('Erro ao alterar status:', error)
  }
}

function confirmDelete(permission: Permission) {
  if (confirm(`Tem certeza que deseja excluir a permissão "${permission.display_name}"?`)) {
    deletePermission(permission)
  }
}

async function deletePermission(permission: Permission) {
  try {
    await rolesStore.deletePermission(permission.id)
  } catch (error) {
    console.error('Erro ao excluir permissão:', error)
  }
}

// Lifecycle
onMounted(async () => {
  await Promise.all([
    rolesStore.fetchPermissions(),
    rolesStore.fetchRoles()
  ])
})
</script>