import { cn } from "@/lib/utils"
import type { ChatMessage } from "@/hooks/use-ai-chat"

interface MessageBubbleProps {
  message: ChatMessage
  isStreaming?: boolean
}

export function MessageBubble({ message, isStreaming }: MessageBubbleProps) {
  const isUser = message.role === "user"

  return (
    <div className={cn("flex gap-3 mb-4", isUser ? "justify-end" : "justify-start")}>
      {!isUser && (
        <div className="w-7 h-7 rounded-lg bg-purple flex items-center justify-center shrink-0">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
            <rect x="3" y="11" width="18" height="10" rx="2"/>
            <circle cx="12" cy="5" r="2"/>
            <path d="M12 7v4"/>
          </svg>
        </div>
      )}

      <div
        className={cn(
          "max-w-[85%] rounded-xl px-4 py-2.5 text-sm",
          isUser
            ? "bg-accent text-white"
            : "bg-muted text-foreground"
        )}
      >
        <div className="whitespace-pre-wrap break-words">
          {message.content}
          {isStreaming && !isUser && (
            <span className="inline-block w-1.5 h-4 ml-0.5 bg-foreground animate-pulse" />
          )}
        </div>
      </div>

      {isUser && (
        <div className="w-7 h-7 rounded-lg bg-accent flex items-center justify-center shrink-0">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
            <circle cx="12" cy="7" r="4"/>
          </svg>
        </div>
      )}
    </div>
  )
}
