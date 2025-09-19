<template>
  <AppLayout>
    <!-- Header da Página -->
    <div class="flex justify-between items-center mb-8">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Usuários</h1>
        <p class="text-gray-600">Gerencie usuários e suas permissões</p>
      </div>
      
      <button
        @click="createUser"
        class="bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700 transition-colors flex items-center space-x-2"
      >
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg>
        <span>Novo Usuário</span>
      </button>
    </div>

    <!-- Search and Filters -->
    <div class="mb-6">
      <div class="flex flex-col sm:flex-row gap-4">
        <div class="flex-1">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Buscar usuários por nome ou email..."
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            @input="handleSearch"
          />
        </div>
        <div class="flex gap-2">
          <select
            v-model="statusFilter"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
          >
            <option value="">Todos os status</option>
            <option value="active">Ativos</option>
            <option value="inactive">Inativos</option>
          </select>
          <select
            v-model="roleFilter"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
          >
            <option value="">Todas as roles</option>
            <option value="admin">Administradores</option>
            <option value="user">Usuários</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center py-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
    </div>

    <!-- Error State -->
    <div v-else-if="usersStore.error" class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
      <p class="text-red-800">{{ usersStore.error }}</p>
    </div>

    <!-- Users Table -->
    <div v-else class="bg-white shadow rounded-lg overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Usuário
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Status
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Roles
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Criado em
              </th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                Ações
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="user in filteredUsers" :key="user.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-10 w-10">
                    <div class="h-10 w-10 rounded-full bg-primary-100 flex items-center justify-center">
                      <span class="text-sm font-medium text-primary-700">
                        {{ user.name.charAt(0).toUpperCase() }}
                      </span>
                    </div>
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                    <div class="text-sm text-gray-500">{{ user.email }}</div>
                    <div v-if="user.username" class="text-xs text-gray-400">@{{ user.username }}</div>
                    <div v-if="user.cpf" class="text-xs text-gray-400">CPF: {{ formatCPF(user.cpf) }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex flex-col space-y-1">
                  <span
                    :class="{
                      'bg-green-100 text-green-800': user.status === 'active',
                      'bg-red-100 text-red-800': user.status === 'inactive'
                    }"
                    class="inline-flex px-2 py-1 text-xs font-medium rounded-full"
                  >
                    {{ user.status === 'active' ? 'Ativo' : 'Inativo' }}
                  </span>
                  <span
                    v-if="user.is_admin"
                    class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-purple-100 text-purple-800"
                  >
                    Admin
                  </span>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex flex-wrap gap-1">
                  <span
                    v-for="role in user.roles"
                    :key="role.id"
                    class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800"
                  >
                    {{ role.display_name }}
                  </span>
                  <span v-if="!user.roles || user.roles.length === 0" class="text-sm text-gray-400">
                    Nenhuma role
                  </span>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ formatDate(user.created_at) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex justify-end space-x-2">
                  <button
                    @click="manageRoles(user)"
                    class="text-blue-600 hover:text-blue-900"
                    title="Gerenciar Roles"
                  >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                  </button>
                  <button
                    @click="editUser(user)"
                    class="text-gray-600 hover:text-gray-900"
                    title="Editar"
                  >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                  </button>
                  <button
                    @click="deleteUser(user.id)"
                    class="text-red-600 hover:text-red-900"
                    title="Excluir"
                  >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <!-- Empty State -->
      <div v-if="filteredUsers.length === 0" class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum usuário encontrado</h3>
        <p class="mt-1 text-sm text-gray-500">Comece criando um novo usuário.</p>
      </div>
    </div>

    <!-- Modal de Criação/Edição -->
    <div v-if="showUserModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">
            {{ editingUser ? 'Editar Usuário' : 'Novo Usuário' }}
          </h3>
          
          <form @submit.prevent="submitUser" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Nome</label>
              <input
                v-model="form.name"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
              />
            </div>
            

            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Nome de Usuário</label>
              <input
                v-model="form.username"
                type="text"
                required
                placeholder="username"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
              />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">CPF</label>
              <input
                v-model="form.cpf"
                type="text"
                placeholder="000.000.000-00"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
              />
            </div>
            
            <div v-if="!editingUser">
              <label class="block text-sm font-medium text-gray-700 mb-1">Senha</label>
              <input
                v-model="form.password"
                type="password"
                required
                placeholder="Mínimo 8 dígitos"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
              />
            </div>
            
            <div v-if="!editingUser">
              <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar Senha</label>
              <input
                v-model="form.password_confirmation"
                type="password"
                required
                placeholder="Confirme sua senha (mínimo 8 dígitos)"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
              />
            </div>
            
            <!-- Campo de senha para edição -->
            <div v-if="editingUser">
              <label class="block text-sm font-medium text-gray-700 mb-1">Nova Senha (opcional)</label>
              <input
                v-model="form.password"
                type="password"
                placeholder="Deixe em branco para manter a senha atual (mínimo 8 dígitos se preenchido)"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
              />
            </div>
            
            <div v-if="editingUser && form.password">
              <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar Nova Senha</label>
              <input
                v-model="form.password_confirmation"
                type="password"
                placeholder="Confirme sua nova senha (mínimo 8 dígitos)"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
              />
            </div>
            
            <div class="flex items-center">
              <input
                v-model="form.is_admin"
                type="checkbox"
                class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
              />
              <label class="ml-2 block text-sm text-gray-900">
                Administrador
              </label>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
              <select
                v-model="form.status"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
              >
                <option value="active">Ativo</option>
                <option value="inactive">Inativo</option>
              </select>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Perfis de Acesso</label>
              <div class="space-y-2 max-h-40 overflow-y-auto border border-gray-200 rounded-md p-3">
                <!-- Debug info -->
                <div v-if="rolesStore.loading" class="text-blue-500 text-sm">
                  Carregando perfis...
                </div>
                <div v-else-if="rolesStore.error" class="text-red-500 text-sm">
                  Erro: {{ rolesStore.error }}
                </div>
                <div v-else-if="rolesStore.roles.length === 0" class="text-yellow-500 text-sm">
                  Nenhum perfil encontrado (total: {{ rolesStore.roles.length }})
                </div>
                
                <div
                  v-for="role in rolesStore.roles"
                  :key="role.id"
                  class="flex items-center"
                >
                  <input
                    v-model="form.roles"
                    :value="role.id"
                    type="checkbox"
                    class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
                  />
                  <label class="ml-2 block text-sm text-gray-900">
                    {{ role.display_name }}
                    <span v-if="role.description" class="text-gray-500 text-xs block">{{ role.description }}</span>
                  </label>
                </div>
                
                <div v-if="!rolesStore.loading && rolesStore.roles.length === 0" class="text-gray-500 text-sm">
                  Nenhum perfil disponível
                </div>
              </div>
            </div>
            
            <div class="flex justify-end space-x-3 pt-4">
              <button
                type="button"
                @click="closeUserModal"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200"
              >
                Cancelar
              </button>
              <button
                type="submit"
                :disabled="submitting"
                class="px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-md hover:bg-primary-700 disabled:opacity-50"
              >
                {{ submitting ? 'Salvando...' : (editingUser ? 'Atualizar' : 'Criar') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Modal de Gerenciamento de Roles -->
    <div v-if="showRolesModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">
            Gerenciar Roles - {{ selectedUser?.name }}
          </h3>
          
          <form @submit.prevent="submitRoles" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Roles Disponíveis</label>
              <div class="space-y-2 max-h-60 overflow-y-auto">
                <div
                  v-for="role in usersStore.roles"
                  :key="role.id"
                  class="flex items-center"
                >
                  <input
                    v-model="selectedRoles"
                    :value="role.id"
                    type="checkbox"
                    class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
                  />
                  <label class="ml-2 block text-sm text-gray-900">
                    {{ role.display_name }}
                    <span v-if="role.description" class="text-gray-500 text-xs block">
                      {{ role.description }}
                    </span>
                  </label>
                </div>
              </div>
            </div>
            
            <div class="flex justify-end space-x-3 pt-4">
              <button
                type="button"
                @click="closeRolesModal"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200"
              >
                Cancelar
              </button>
              <button
                type="submit"
                :disabled="submitting"
                class="px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-md hover:bg-primary-700 disabled:opacity-50"
              >
                {{ submitting ? 'Salvando...' : 'Salvar Roles' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useUsersStore, type User } from '@/stores/users'
import { useRolesStore } from '@/stores/roles'
import AppLayout from '@/components/AppLayout.vue'

const usersStore = useUsersStore()
const rolesStore = useRolesStore()

const loading = ref(false)
const searchQuery = ref('')
const statusFilter = ref('')
const roleFilter = ref('')
const showUserModal = ref(false)
const showRolesModal = ref(false)
const editingUser = ref<User | null>(null)
const selectedUser = ref<User | null>(null)
const submitting = ref(false)
const selectedRoles = ref<number[]>([])

const form = ref({
  name: '',
  username: '',
  cpf: '',
  password: '',
  password_confirmation: '',
  is_admin: false,
  status: 'active' as 'active' | 'inactive',
  roles: [] as number[]
})

const filteredUsers = computed(() => {
  let users = usersStore.users

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    users = users.filter(user => 
      user.name.toLowerCase().includes(query) ||
      (user.username && user.username.toLowerCase().includes(query))
    )
  }

  if (statusFilter.value) {
    users = users.filter(user => user.status === statusFilter.value)
  }

  if (roleFilter.value) {
    if (roleFilter.value === 'admin') {
      users = users.filter(user => user.is_admin)
    } else if (roleFilter.value === 'user') {
      users = users.filter(user => !user.is_admin)
    }
  }

  return users
})

const handleSearch = () => {
  // Implementar debounce se necessário
}

const createUser = () => {
  editingUser.value = null
  form.value = {
    name: '',
    username: '',
    cpf: '',
    password: '',
    password_confirmation: '',
    is_admin: false,
    status: 'active',
    roles: []
  }
  showUserModal.value = true
}

const editUser = (user: User) => {
  editingUser.value = user
  form.value = {
    name: user.name,
    username: user.username || '',
    cpf: user.cpf || '',
    password: '',
    password_confirmation: '',
    is_admin: user.is_admin,
    status: user.status,
    roles: user.roles?.map(role => role.id) || []
  }
  showUserModal.value = true
}

const closeUserModal = () => {
  showUserModal.value = false
  editingUser.value = null
}

const submitUser = async () => {
  submitting.value = true
  
  try {
    let result
    if (editingUser.value) {
      // Para edição, remover campos de senha vazios
      const updateData = { ...form.value }
      if (!updateData.password) {
        delete updateData.password
        delete updateData.password_confirmation
      }
      result = await usersStore.updateUser(editingUser.value.id, updateData)
    } else {
      // Para criação, garantir que todos os campos obrigatórios estejam presentes
      const createData = {
        name: form.value.name,
        username: form.value.username,
        cpf: form.value.cpf || null, // Enviar null se vazio
        password: form.value.password,
        password_confirmation: form.value.password_confirmation,
        is_admin: form.value.is_admin || false,
        status: form.value.status || 'active',
        terms_accepted: true,
        roles: form.value.roles || []
      }
      
      // Validação básica no frontend
      if (!createData.name || !createData.username || !createData.password) {
        alert('Por favor, preencha todos os campos obrigatórios: Nome, Username e Senha')
        return
      }
      
      if (createData.password !== createData.password_confirmation) {
        alert('As senhas não coincidem')
        return
      }
      
      result = await usersStore.createUser(createData)
    }
    
    if (result.success) {
      closeUserModal()
      // Recarregar a lista de usuários
      await usersStore.fetchUsers()
    } else {
      // Exibir erro específico
      console.error('Erro ao salvar usuário:', result.error)
      
      if (result.validationErrors) {
        // Mostrar erros de validação específicos com tradução
        const errorMessages = []
        const fieldTranslations = {
          'cpf': 'CPF',
          'username': 'Nome de usuário',
          'name': 'Nome',
          'password': 'Senha'
        }
        
        for (const [field, messages] of Object.entries(result.validationErrors)) {
          if (Array.isArray(messages)) {
            const fieldName = fieldTranslations[field] || field
            const translatedMessages = messages.map(msg => {
              // Traduzir mensagens comuns
              if (msg.includes('has already been taken')) {
                return `${fieldName} já está em uso`
              }
              if (msg.includes('required')) {
                return `${fieldName} é obrigatório`
              }
              if (msg.includes('invalid')) {
                return `${fieldName} é inválido`
              }
              return msg
            })
            errorMessages.push(`• ${translatedMessages.join(', ')}`)
          }
        }
        alert('Erros encontrados:\n\n' + errorMessages.join('\n'))
      } else {
        alert(result.error || 'Erro ao salvar usuário')
      }
    }
  } catch (error) {
    console.error('Erro inesperado:', error)
    alert('Erro inesperado ao salvar usuário')
  } finally {
    submitting.value = false
  }
}

const manageRoles = async (user: User) => {
  selectedUser.value = user
  selectedRoles.value = user.roles?.map(role => role.id) || []
  showRolesModal.value = true
}

const closeRolesModal = () => {
  showRolesModal.value = false
  selectedUser.value = null
  selectedRoles.value = []
}

const submitRoles = async () => {
  if (!selectedUser.value) return
  
  submitting.value = true
  
  try {
    const result = await usersStore.assignRoles(selectedUser.value.id, selectedRoles.value)
    if (result.success) {
      closeRolesModal()
    }
  } finally {
    submitting.value = false
  }
}

const deleteUser = async (id: number) => {
  if (confirm('Tem certeza que deseja excluir este usuário?')) {
    await usersStore.deleteUser(id)
  }
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('pt-BR')
}

const formatCPF = (cpf: string) => {
  return cpf.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4')
}

onMounted(async () => {
  loading.value = true
  await Promise.all([
    usersStore.fetchUsers(),
    usersStore.fetchPermissions(),
    rolesStore.fetchRoles()
  ])
  loading.value = false
})
</script>