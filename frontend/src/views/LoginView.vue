<template>
  <div class="min-h-screen bg-gradient-to-br from-primary-50 to-primary-100 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div>
        <div class="mx-auto h-12 w-12 bg-primary-600 rounded-lg flex items-center justify-center">
          <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
          </svg>
        </div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
          ClickVenda
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          Vendas Rapidas com um Click!
        </p>
      </div>
      
      <div class="bg-white py-8 px-6 shadow-xl rounded-lg">
        <form class="space-y-6" @submit.prevent="handleSubmit">
          <div>
            <label for="username" class="block text-sm font-medium text-gray-700">
              Usuário
            </label>
            <div class="mt-1">
              <input
                id="username"
                v-model="form.username"
                name="username"
                type="text"
                autocomplete="username"
                required
                class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 focus:z-10 sm:text-sm"
                placeholder="Seu nome de usuário"
              />
            </div>
          </div>

          <div>
            <label for="password" class="block text-sm font-medium text-gray-700">
              Senha
            </label>
            <div class="mt-1">
              <input
                id="password"
                v-model="form.password"
                name="password"
                type="password"
                autocomplete="current-password"
                required
                class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 focus:z-10 sm:text-sm"
                placeholder="Sua senha"
              />
            </div>
          </div>

          <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
            {{ error }}
          </div>

          <div>
            <button
              type="submit"
              :disabled="loading"
              class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="loading" class="absolute left-0 inset-y-0 flex items-center pl-3">
                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
              </span>
              {{ loading ? 'Entrando...' : 'Entrar' }}
            </button>
          </div>

          <div class="text-center">
            <button
              type="button"
              @click="showRegister = true"
              class="text-primary-600 hover:text-primary-500 text-sm"
            >
              Não tem uma conta? Registre-se
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal de Registro -->
    <div v-if="showRegister" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Criar Conta</h3>
          <form @submit.prevent="handleRegister">
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700">Nome</label>
                <input
                  v-model="registerForm.name"
                  type="text"
                  required
                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                />
              </div>
              

              
              <div>
                <label class="block text-sm font-medium text-gray-700">Senha</label>
                <input
                  v-model="registerForm.password"
                  type="password"
                  required
                  placeholder="Mínimo 8 dígitos"
                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                />
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700">Confirmar Senha</label>
                <input
                  v-model="registerForm.password_confirmation"
                  type="password"
                  required
                  placeholder="Confirme sua senha (mínimo 8 dígitos)"
                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                />
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Perfil/Função</label>
                <div class="space-y-2 max-h-32 overflow-y-auto border border-gray-300 rounded-md p-2">
                  <div
                    v-for="role in rolesStore.activeRoles"
                    :key="role.id"
                    class="flex items-center"
                  >
                    <input
                      v-model="registerForm.roles"
                      :value="role.id"
                      type="checkbox"
                      class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
                    />
                    <label class="ml-2 block text-sm text-gray-900">
                      {{ role.display_name }}
                      <span v-if="role.description" class="text-gray-500 text-xs block">{{ role.description }}</span>
                    </label>
                  </div>
                  <div v-if="rolesStore.activeRoles.length === 0" class="text-gray-500 text-sm">
                    Nenhum perfil disponível
                  </div>
                </div>
              </div>
            </div>
            
            <div v-if="registerError" class="mt-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
              {{ registerError }}
            </div>
            
            <div class="mt-6 flex space-x-3">
              <button
                type="submit"
                :disabled="registerLoading"
                class="flex-1 bg-primary-600 text-white py-2 px-4 rounded-md hover:bg-primary-700 disabled:opacity-50"
              >
                {{ registerLoading ? 'Criando...' : 'Criar Conta' }}
              </button>
              <button
                type="button"
                @click="showRegister = false"
                class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-400"
              >
                Cancelar
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useRolesStore } from '@/stores/roles'

const router = useRouter()
const authStore = useAuthStore()
const rolesStore = useRolesStore()

const form = reactive({
  username: '',
  password: ''
})

const registerForm = reactive({
  name: '',
  password: '',
  password_confirmation: '',
  roles: [] as number[]
})

const loading = ref(false)
const error = ref('')
const showRegister = ref(false)
const registerLoading = ref(false)
const registerError = ref('')

onMounted(async () => {
  // Carregar roles quando o componente for montado
  try {
    await rolesStore.fetchRoles()
  } catch (error) {
    console.error('Erro ao carregar roles:', error)
  }
})

const handleSubmit = async () => {
  loading.value = true
  error.value = ''
  
  const result = await authStore.login(form.username, form.password)
  
  if (result.success) {
    router.push('/dashboard')
  } else {
    error.value = result.error
  }
  
  loading.value = false
}

const handleRegister = async () => {
  registerLoading.value = true
  registerError.value = ''
  
  if (registerForm.password !== registerForm.password_confirmation) {
    registerError.value = 'As senhas não coincidem'
    registerLoading.value = false
    return
  }
  
  const result = await authStore.register(
    registerForm.name,
    registerForm.password,
    registerForm.password_confirmation,
    registerForm.roles
  )
  
  if (result.success) {
    showRegister.value = false
    router.push('/dashboard')
  } else {
    registerError.value = result.error
  }
  
  registerLoading.value = false
}
</script>
