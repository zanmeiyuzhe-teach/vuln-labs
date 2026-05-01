"use client"

import { useState, useCallback, useRef } from "react"
import { api } from "@/lib/api"

export interface ChatMessage {
  role: "user" | "assistant" | "system"
  content: string
}

interface UseAIChatOptions {
  labId: string
  model?: string
  mode?: "hint" | "solve"
}

export function useAIChat({ labId, model, mode = "hint" }: UseAIChatOptions) {
  const [messages, setMessages] = useState<ChatMessage[]>([])
  const [isStreaming, setIsStreaming] = useState(false)
  const [error, setError] = useState<string | null>(null)
  const abortRef = useRef<AbortController | null>(null)

  const sendMessage = useCallback(async (content: string) => {
    if (!content.trim() || isStreaming) return

    const userMessage: ChatMessage = { role: "user", content }
    const newMessages = [...messages, userMessage]
    setMessages(newMessages)
    setIsStreaming(true)
    setError(null)

    const assistantMessage: ChatMessage = { role: "assistant", content: "" }
    setMessages([...newMessages, assistantMessage])

    try {
      const stream = api.chatStream(labId, newMessages, model)

      let fullContent = ""
      for await (const chunk of stream) {
        if (chunk.error) {
          setError(chunk.error)
          break
        }

        const delta = chunk.choices?.[0]?.delta?.content
        if (delta) {
          fullContent += delta
          setMessages((prev) => {
            const updated = [...prev]
            updated[updated.length - 1] = { role: "assistant", content: fullContent }
            return updated
          })
        }
      }

      if (!fullContent && !error) {
        setMessages((prev) => prev.slice(0, -1))
      }
    } catch (err: any) {
      setError(err.message || "AI request failed")
      setMessages((prev) => prev.slice(0, -1))
    } finally {
      setIsStreaming(false)
    }
  }, [labId, model, messages, isStreaming, error])

  const clearMessages = useCallback(() => {
    setMessages([])
    setError(null)
  }, [])

  const stopStreaming = useCallback(() => {
    abortRef.current?.abort()
    setIsStreaming(false)
  }, [])

  return { messages, isStreaming, error, sendMessage, clearMessages, stopStreaming }
}
