<template>
  <div class="roles-view">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Gerenciar Roles</h1>
        <p class="text-gray-600">Gerencie as roles e suas permissões do sistema</p>
      </div>
      <button
        @click="openCreateModal"
        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Nova Role
      </button>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
      <div class="flex flex-wrap gap-4">
        <div class="flex-1 min-w-64">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Buscar roles..."
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          />
        </div>
        <div class="flex gap-2">
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

    <!-- Tabela de Roles -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Role
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Descrição
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Permissões
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Usuários
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
            <tr v-for="role in filteredRoles" :key="role.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div>
                  <div class="text-sm font-medium text-gray-900">{{ role.display_name }}</div>
                  <div class="text-sm text-gray-500">{{ role.name }}</div>
                </div>
              </td>
              <td class="px-6 py-4">
                <div class="text-sm text-gray-900">{{ role.description || '-' }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                  {{ role.permissions?.length || 0 }} permissões
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                  {{ role.users_count || 0 }} usuários
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  :class="[
                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                    role.active
                      ? 'bg-green-100 text-green-800'
                      : 'bg-red-100 text-red-800'
                  ]"
                >
                  {{ role.active ? 'Ativo' : 'Inativo' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex items-center gap-2">
                  <button
                    @click="openEditModal(role)"
                    class="text-blue-600 hover:text-blue-900"
                    title="Editar"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </button>
                  <button
                    @click="openPermissionsModal(role)"
                    class="text-purple-600 hover:text-purple-900"
                    title="Gerenciar Permissões"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                  </button>
                  <button
                    @click="toggleRoleStatus(role)"
                    :class="[
                      'hover:opacity-75',
                      role.active ? 'text-red-600' : 'text-green-600'
                    ]"
                    :title="role.active ? 'Desativar' : 'Ativar'"
                  >
                    <svg v-if="role.active" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728" />
                    </svg>
                    <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                  </button>
                  <button
                    @click="confirmDelete(role)"
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
      <div v-if="filteredRoles.length === 0 && !rolesStore.loading" class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhuma role encontrada</h3>
        <p class="mt-1 text-sm text-gray-500">Comece criando uma nova role.</p>
      </div>
    </div>

    <!-- Modal Criar/Editar Role -->
    <div v-if="showRoleModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">
            {{ editingRole ? 'Editar Role' : 'Nova Role' }}
          </h3>
          
          <form @submit.prevent="saveRole" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Nome da Role</label>
              <input
                v-model="roleForm.name"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="ex: manager"
              />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Nome de Exibição</label>
              <input
                v-model="roleForm.display_name"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="ex: Gerente"
              />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
              <textarea
                v-model="roleForm.description"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="Descrição da role..."
              ></textarea>
            </div>
            
            <div class="flex items-center">
              <input
                v-model="roleForm.active"
                type="checkbox"
                id="active"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              />
              <label for="active" class="ml-2 block text-sm text-gray-900">
                Role ativa
              </label>
            </div>
            
            <div class="flex justify-end gap-3 pt-4">
              <button
                type="button"
                @click="closeRoleModal"
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

    <!-- Modal Gerenciar Permissões -->
    <div v-if="showPermissionsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-10 mx-auto p-5 border w-2/3 max-w-4xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">
            Gerenciar Permissões - {{ selectedRole?.display_name }}
          </h3>
          
          <div class="space-y-4">
            <div v-for="(modulePermissions, module) in groupedPermissions" :key="module" class="border rounded-lg p-4">
              <h4 class="font-medium text-gray-900 mb-3">{{ module }}</h4>
              <div class="grid grid-cols-2 gap-2">
                <div v-for="permission in modulePermissions" :key="permission.id" class="flex items-center">
                  <input
                    v-model="selectedPermissions"
                    :value="permission.id"
                    type="checkbox"
                    :id="`permission-${permission.id}`"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                  />
                  <label :for="`permission-${permission.id}`" class="ml-2 block text-sm text-gray-900">
                    {{ permission.display_name }}
                  </label>
                </div>
              </div>
            </div>
          </div>
          
          <div class="flex justify-end gap-3 pt-6">
            <button
              type="button"
              @click="closePermissionsModal"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg"
            >
              Cancelar
            </button>
            <button
              @click="savePermissions"
              :disabled="rolesStore.loading"
              class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg disabled:opacity-50"
            >
              {{ rolesStore.loading ? 'Salvando...' : 'Salvar Permissões' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRolesStore, type Role } from '@/stores/roles'

const rolesStore = useRolesStore()

// Reactive data
const searchQuery = ref('')
const statusFilter = ref('')
const showRoleModal = ref(false)
const showPermissionsModal = ref(false)
const editingRole = ref<Role | null>(null)
const selectedRole = ref<Role | null>(null)
const selectedPermissions = ref<number[]>([])

const roleForm = ref({
  name: '',
  display_name: '',
  description: '',
  active: true
})

// Computed
const filteredRoles = computed(() => {
  let filtered = rolesStore.roles

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(role =>
      role.name.toLowerCase().includes(query) ||
      role.display_name.toLowerCase().includes(query) ||
      (role.description && role.description.toLowerCase().includes(query))
    )
  }

  if (statusFilter.value) {
    filtered = filtered.filter(role =>
      statusFilter.value === 'active' ? role.active : !role.active
    )
  }

  return filtered
})

const groupedPermissions = computed(() => {
  return rolesStore.permissionsByModule
})

// Methods
function openCreateModal() {
  editingRole.value = null
  roleForm.value = {
    name: '',
    display_name: '',
    description: '',
    active: true
  }
  showRoleModal.value = true
}

function openEditModal(role: Role) {
  editingRole.value = role
  roleForm.value = {
    name: role.name,
    display_name: role.display_name,
    description: role.description || '',
    active: role.active
  }
  showRoleModal.value = true
}

function closeRoleModal() {
  showRoleModal.value = false
  editingRole.value = null
}

async function saveRole() {
  try {
    if (editingRole.value) {
      await rolesStore.updateRole(editingRole.value.id, roleForm.value)
    } else {
      await rolesStore.createRole(roleForm.value)
    }
    closeRoleModal()
  } catch (error) {
    console.error('Erro ao salvar role:', error)
  }
}

async function openPermissionsModal(role: Role) {
  selectedRole.value = role
  selectedPermissions.value = role.permissions?.map(p => p.id) || []
  showPermissionsModal.value = true
}

function closePermissionsModal() {
  showPermissionsModal.value = false
  selectedRole.value = null
  selectedPermissions.value = []
}

async function savePermissions() {
  if (!selectedRole.value) return
  
  try {
    await rolesStore.assignPermissionsToRole(selectedRole.value.id, selectedPermissions.value)
    closePermissionsModal()
  } catch (error) {
    console.error('Erro ao salvar permissões:', error)
  }
}

async function toggleRoleStatus(role: Role) {
  try {
    await rolesStore.toggleRoleStatus(role.id)
  } catch (error) {
    console.error('Erro ao alterar status:', error)
  }
}

function confirmDelete(role: Role) {
  if (confirm(`Tem certeza que deseja excluir a role "${role.display_name}"?`)) {
    deleteRole(role)
  }
}

async function deleteRole(role: Role) {
  try {
    await rolesStore.deleteRole(role.id)
  } catch (error) {
    console.error('Erro ao excluir role:', error)
  }
}

// Lifecycle
onMounted(async () => {
  await Promise.all([
    rolesStore.fetchRoles(),
    rolesStore.fetchPermissions()
  ])
})
</script>