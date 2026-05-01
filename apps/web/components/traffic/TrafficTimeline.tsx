"use client"

import { cn } from "@/lib/utils"

interface TrafficEntry {
  id: string
  step_number: number
  request_method: string
  request_url: string
  response_status: number
  notes?: string
  created_at: string
}

interface TrafficTimelineProps {
  entries: TrafficEntry[]
  className?: string
}

const methodColors: Record<string, string> = {
  GET: "bg-success",
  POST: "bg-accent",
  PUT: "bg-warning",
  DELETE: "bg-danger",
  PATCH: "bg-info",
}

const statusColors: Record<string, string> = {
  "2": "text-success",
  "3": "text-info",
  "4": "text-warning",
  "5": "text-danger",
}

export function TrafficTimeline({ entries, className }: TrafficTimelineProps) {
  if (entries.length === 0) {
    return (
      <div className={cn("flex flex-col items-center justify-center h-full text-muted-foreground", className)}>
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5" className="mb-3 opacity-50">
          <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
        </svg>
        <p className="text-xs">暂无流量记录</p>
        <p className="text-xs opacity-70">操作靶场时会自动记录请求</p>
      </div>
    )
  }

  return (
    <div className={cn("overflow-y-auto p-4 space-y-3", className)}>
      {entries.map((entry) => (
        <div
          key={entry.id}
          className="flex items-start gap-3 p-3 rounded-lg bg-muted/50 hover:bg-muted transition-colors"
        >
          {/* Step number */}
          <div className="w-6 h-6 rounded-full bg-card border border-border flex items-center justify-center shrink-0">
            <span className="text-xs font-mono">{entry.step_number}</span>
          </div>

          {/* Content */}
          <div className="flex-1 min-w-0">
            <div className="flex items-center gap-2 mb-1">
              <span className={cn("px-1.5 py-0.5 rounded text-[10px] font-bold text-white", methodColors[entry.request_method] || "bg-muted-foreground")}>
                {entry.request_method}
              </span>
              <span className={cn("text-xs font-mono", statusColors[String(entry.response_status)?.[0]] || "text-muted-foreground")}>
                {entry.response_status}
              </span>
            </div>
            <p className="text-xs text-muted-foreground truncate font-mono">{entry.request_url}</p>
            {entry.notes && (
              <p className="text-xs text-muted-foreground mt-1 italic">{entry.notes}</p>
            )}
          </div>
        </div>
      ))}
    </div>
  )
}
