"use client"

import { useState, useCallback } from "react"
import { api } from "@/lib/api"

interface UseApiState<T> {
  data: T | null
  loading: boolean
  error: string | null
}

export function useApi<T = any>() {
  const [state, setState] = useState<UseApiState<T>>({
    data: null,
    loading: false,
    error: null,
  })

  const execute = useCallback(async (apiCall: () => Promise<any>) => {
    setState((s) => ({ ...s, loading: true, error: null }))
    try {
      const res = await apiCall()
      if (res.success) {
        setState({ data: res.data, loading: false, error: null })
        return res.data
      } else {
        setState({ data: null, loading: false, error: res.error || "Unknown error" })
        return null
      }
    } catch (err: any) {
      setState({ data: null, loading: false, error: err.message || "Network error" })
      return null
    }
  }, [])

  const reset = useCallback(() => {
    setState({ data: null, loading: false, error: null })
  }, [])

  return { ...state, execute, reset }
}
