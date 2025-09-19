import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

export interface Role {
  id: number
  name: string
  display_name: string
  description?: string
  active: boolean
  created_at: string
  updated_at: string
  permissions?: Permission[]
  users_count?: number
}

export interface Permission {
  id: number
  name: string
  display_name: string
  description?: string
  module?: string
  active: boolean
  created_at: string
  updated_at: string
  roles?: Role[]
}

export const useRolesStore = defineStore('roles', () => {
  const roles = ref<Role[]>([])
  const permissions = ref<Permission[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)

  // Computed
  const activeRoles = computed(() => roles.value.filter(role => role.active))
  const activePermissions = computed(() => permissions.value.filter(permission => permission.active))
  
  const permissionsByModule = computed(() => {
    const grouped: Record<string, Permission[]> = {}
    permissions.value.forEach(permission => {
      const module = permission.module || 'Geral'
      if (!grouped[module]) {
        grouped[module] = []
      }
      grouped[module].push(permission)
    })
    return grouped
  })

  // Actions - Roles
  async function fetchRoles() {
    loading.value = true
    error.value = null
    try {
      const response = await api.get('/roles/public')
      roles.value = response.data
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao carregar roles'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function createRole(roleData: Partial<Role>) {
    loading.value = true
    error.value = null
    try {
      const response = await api.post('/roles', roleData)
      roles.value.push(response.data.role)
      return response.data.role
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao criar role'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function updateRole(id: number, roleData: Partial<Role>) {
    loading.value = true
    error.value = null
    try {
      const response = await api.put(`/roles/${id}`, roleData)
      const index = roles.value.findIndex(role => role.id === id)
      if (index !== -1) {
        roles.value[index] = response.data.role
      }
      return response.data.role
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao atualizar role'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function deleteRole(id: number) {
    loading.value = true
    error.value = null
    try {
      await api.delete(`/roles/${id}`)
      roles.value = roles.value.filter(role => role.id !== id)
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao excluir role'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function toggleRoleStatus(id: number) {
    loading.value = true
    error.value = null
    try {
      const response = await api.post(`/roles/${id}/toggle-status`)
      const index = roles.value.findIndex(role => role.id === id)
      if (index !== -1) {
        roles.value[index] = response.data.role
      }
      return response.data.role
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao alterar status da role'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function assignPermissionsToRole(roleId: number, permissionIds: number[]) {
    loading.value = true
    error.value = null
    try {
      const response = await api.post(`/roles/${roleId}/assign-permissions`, {
        permissions: permissionIds
      })
      const index = roles.value.findIndex(role => role.id === roleId)
      if (index !== -1) {
        roles.value[index] = response.data.role
      }
      return response.data.role
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao atribuir permissões'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function getRoleUsers(roleId: number) {
    loading.value = true
    error.value = null
    try {
      const response = await api.get(`/roles/${roleId}/users`)
      return response.data
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao carregar usuários da role'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function getRolePermissions(roleId: number) {
    loading.value = true
    error.value = null
    try {
      const response = await api.get(`/roles/${roleId}/permissions`)
      return response.data
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao carregar permissões da role'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Actions - Permissions
  async function fetchPermissions() {
    loading.value = true
    error.value = null
    try {
      const response = await api.get('/permissions')
      permissions.value = response.data
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao carregar permissões'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function createPermission(permissionData: Partial<Permission>) {
    loading.value = true
    error.value = null
    try {
      const response = await api.post('/permissions', permissionData)
      permissions.value.push(response.data.permission)
      return response.data.permission
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao criar permissão'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function updatePermission(id: number, permissionData: Partial<Permission>) {
    loading.value = true
    error.value = null
    try {
      const response = await api.put(`/permissions/${id}`, permissionData)
      const index = permissions.value.findIndex(permission => permission.id === id)
      if (index !== -1) {
        permissions.value[index] = response.data.permission
      }
      return response.data.permission
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao atualizar permissão'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function deletePermission(id: number) {
    loading.value = true
    error.value = null
    try {
      await api.delete(`/permissions/${id}`)
      permissions.value = permissions.value.filter(permission => permission.id !== id)
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao excluir permissão'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function togglePermissionStatus(id: number) {
    loading.value = true
    error.value = null
    try {
      const response = await api.post(`/permissions/${id}/toggle-status`)
      const index = permissions.value.findIndex(permission => permission.id === id)
      if (index !== -1) {
        permissions.value[index] = response.data.permission
      }
      return response.data.permission
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao alterar status da permissão'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function getPermissionsByModule(module: string) {
    loading.value = true
    error.value = null
    try {
      const response = await api.get(`/permissions/module/${module}`)
      return response.data
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao carregar permissões do módulo'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function getModules() {
    loading.value = true
    error.value = null
    try {
      const response = await api.get('/permissions/modules')
      return response.data
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao carregar módulos'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function assignPermissionToRoles(permissionId: number, roleIds: number[]) {
    loading.value = true
    error.value = null
    try {
      const response = await api.post(`/permissions/${permissionId}/assign-to-roles`, {
        roles: roleIds
      })
      const index = permissions.value.findIndex(permission => permission.id === permissionId)
      if (index !== -1) {
        permissions.value[index] = response.data.permission
      }
      return response.data.permission
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao atribuir permissão às roles'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Utility functions
  function clearError() {
    error.value = null
  }

  function reset() {
    roles.value = []
    permissions.value = []
    loading.value = false
    error.value = null
  }

  return {
    // State
    roles,
    permissions,
    loading,
    error,
    
    // Computed
    activeRoles,
    activePermissions,
    permissionsByModule,
    
    // Actions - Roles
    fetchRoles,
    createRole,
    updateRole,
    deleteRole,
    toggleRoleStatus,
    assignPermissionsToRole,
    getRoleUsers,
    getRolePermissions,
    
    // Actions - Permissions
    fetchPermissions,
    createPermission,
    updatePermission,
    deletePermission,
    togglePermissionStatus,
    getPermissionsByModule,
    getModules,
    assignPermissionToRoles,
    
    // Utility
    clearError,
    reset
  }
})