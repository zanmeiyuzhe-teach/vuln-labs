"use client"

import { useState, useCallback, useEffect, useRef } from "react"
import { api } from "@/lib/api"

interface LabState {
  status: "idle" | "starting" | "running" | "stopping" | "error"
  containerUrl: string | null
  containerId: string | null
  expiresAt: string | null
  error: string | null
}

export function useLab(labId: string | null) {
  const [state, setState] = useState<LabState>({
    status: "idle",
    containerUrl: null,
    containerId: null,
    expiresAt: null,
    error: null,
  })
  const pollRef = useRef<NodeJS.Timeout | null>(null)

  const startLab = useCallback(async () => {
    if (!labId) return
    setState((s) => ({ ...s, status: "starting", error: null }))

    const res = await api.startLab(labId)
    if (res.success && res.data) {
      setState({
        status: "running",
        containerUrl: res.data.url,
        containerId: res.data.container_id,
        expiresAt: res.data.expires_at,
        error: null,
      })
    } else {
      setState((s) => ({ ...s, status: "error", error: res.error || "Failed to start lab" }))
    }
  }, [labId])

  const stopLab = useCallback(async () => {
    if (!labId) return
    setState((s) => ({ ...s, status: "stopping" }))

    const res = await api.stopLab(labId)
    if (res.success) {
      setState({ status: "idle", containerUrl: null, containerId: null, expiresAt: null, error: null })
    } else {
      setState((s) => ({ ...s, status: "running", error: res.error || "Failed to stop lab" }))
    }
  }, [labId])

  const resetLab = useCallback(async () => {
    if (!labId) return
    const res = await api.resetLab(labId)
    if (res.success) {
      await startLab()
    }
  }, [labId, startLab])

  // Poll status when running
  useEffect(() => {
    if (state.status !== "running" || !labId) {
      if (pollRef.current) clearInterval(pollRef.current)
      return
    }

    pollRef.current = setInterval(async () => {
      const res = await api.request("GET", `/labs/${labId}/status`)
      if (res.success && res.data) {
        if (res.data.status === "not_running") {
          setState({ status: "idle", containerUrl: null, containerId: null, expiresAt: null, error: null })
        } else {
          setState((s) => ({ ...s, expiresAt: res.data.expires_at }))
        }
      }
    }, 30000)

    return () => {
      if (pollRef.current) clearInterval(pollRef.current)
    }
  }, [state.status, labId])

  return { ...state, startLab, stopLab, resetLab }
}
