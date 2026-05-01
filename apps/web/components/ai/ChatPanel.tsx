"use client"

import { useState, useRef, useEffect } from "react"
import { useAIChat, type ChatMessage } from "@/hooks/use-ai-chat"
import { MessageBubble } from "./MessageBubble"
import { cn } from "@/lib/utils"

interface ChatPanelProps {
  labId: string
  labName?: string
  className?: string
}

export function ChatPanel({ labId, labName, className }: ChatPanelProps) {
  const [input, setInput] = useState("")
  const messagesEndRef = useRef<HTMLDivElement>(null)
  const { messages, isStreaming, error, sendMessage, clearMessages } = useAIChat({
    labId,
    mode: "hint",
  })

  useEffect(() => {
    messagesEndRef.current?.scrollIntoView({ behavior: "smooth" })
  }, [messages])

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    if (!input.trim() || isStreaming) return
    const msg = input
    setInput("")
    await sendMessage(msg)
  }

  const suggestedPrompts = [
    "这道题的解题思路是什么？",
    "给我一个提示，不要直接给答案",
    "这个漏洞的原理是什么？",
    "我应该用什么工具？",
  ]

  return (
    <div className={cn("flex flex-col h-full", className)}>
      {/* Header */}
      <div className="flex items-center justify-between px-4 h-10 border-b border-border bg-card shrink-0">
        <div className="flex items-center gap-2">
          <div className="w-5 h-5 rounded bg-purple flex items-center justify-center">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
              <rect x="3" y="11" width="18" height="10" rx="2"/>
              <circle cx="12" cy="5" r="2"/>
              <path d="M12 7v4"/>
            </svg>
          </div>
          <span className="text-xs font-medium">AI 辅导员</span>
        </div>
        {messages.length > 0 && (
          <button
            onClick={clearMessages}
            className="text-xs text-muted-foreground hover:text-foreground transition-colors"
          >
            清空对话
          </button>
        )}
      </div>

      {/* Messages */}
      <div className="flex-1 overflow-y-auto p-4">
        {messages.length === 0 ? (
          <div className="flex flex-col items-center justify-center h-full text-center">
            <div className="w-12 h-12 rounded-xl bg-purple/20 flex items-center justify-center mb-4">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#8b5cf6" strokeWidth="2">
                <rect x="3" y="11" width="18" height="10" rx="2"/>
                <circle cx="12" cy="5" r="2"/>
                <path d="M12 7v4"/>
              </svg>
            </div>
            <p className="text-sm font-medium mb-1">AI 辅导员</p>
            <p className="text-xs text-muted-foreground mb-6 max-w-[200px]">
              {labName ? `正在学习「${labName}」` : "卡住了？让 AI 给你提示"}
            </p>
            <div className="space-y-2 w-full">
              {suggestedPrompts.map((prompt, i) => (
                <button
                  key={i}
                  onClick={() => sendMessage(prompt)}
                  className="w-full text-left px-3 py-2 rounded-lg text-xs bg-muted text-muted-foreground hover:text-foreground hover:bg-muted/80 transition-colors"
                >
                  {prompt}
                </button>
              ))}
            </div>
          </div>
        ) : (
          <>
            {messages.map((msg, i) => (
              <MessageBubble
                key={i}
                message={msg}
                isStreaming={isStreaming && i === messages.length - 1}
              />
            ))}
            <div ref={messagesEndRef} />
          </>
        )}
      </div>

      {/* Error */}
      {error && (
        <div className="px-4 py-2 bg-danger/10 border-t border-danger/20">
          <p className="text-xs text-danger">{error}</p>
        </div>
      )}

      {/* Input */}
      <form onSubmit={handleSubmit} className="p-3 border-t border-border">
        <div className="flex gap-2">
          <input
            value={input}
            onChange={(e) => setInput(e.target.value)}
            placeholder="输入你的问题..."
            disabled={isStreaming}
            className="flex-1 h-9 px-3 rounded-lg bg-muted border border-border text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:border-accent transition-colors disabled:opacity-50"
          />
          <button
            type="submit"
            disabled={!input.trim() || isStreaming}
            className="h-9 w-9 rounded-lg bg-accent text-white flex items-center justify-center hover:bg-accent-hover transition-colors disabled:opacity-50"
          >
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
              <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
            </svg>
          </button>
        </div>
      </form>
    </div>
  )
}
