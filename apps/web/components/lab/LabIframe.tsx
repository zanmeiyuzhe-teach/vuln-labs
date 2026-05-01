"use client"

import { useState } from "react"
import { cn } from "@/lib/utils"

interface LabIframeProps {
  url: string | null
  status: "idle" | "starting" | "running" | "stopping" | "error"
  onStart: () => void
  onStop: () => void
  onReset: () => void
  className?: string
}

export function LabIframe({ url, status, onStart, onStop, onReset, className }: LabIframeProps) {
  const [iframeLoaded, setIframeLoaded] = useState(false)

  return (
    <div className={cn("flex flex-col h-full", className)}>
      {/* Toolbar */}
      <div className="flex items-center justify-between px-4 h-10 border-b border-border bg-card shrink-0">
        <div className="flex items-center gap-2">
          <div className={`w-2 h-2 rounded-full ${
            status === "running" ? "bg-success animate-pulse" :
            status === "starting" ? "bg-warning animate-pulse" :
            status === "error" ? "bg-danger" : "bg-muted-foreground"
          }`} />
          <span className="text-xs text-muted-foreground">
            {status === "idle" && "未启动"}
            {status === "starting" && "启动中..."}
            {status === "running" && (url || "运行中")}
            {status === "stopping" && "停止中..."}
            {status === "error" && "错误"}
          </span>
        </div>
        <div className="flex items-center gap-1.5">
          {status === "idle" && (
            <button
              onClick={onStart}
              className="px-3 py-1 rounded text-xs font-medium bg-success text-white hover:bg-success/90 transition-colors"
            >
              启动靶场
            </button>
          )}
          {status === "running" && (
            <>
              <button
                onClick={onReset}
                className="px-3 py-1 rounded text-xs font-medium bg-muted text-muted-foreground hover:text-foreground transition-colors"
              >
                重置
              </button>
              <button
                onClick={onStop}
                className="px-3 py-1 rounded text-xs font-medium bg-danger text-white hover:bg-danger/90 transition-colors"
              >
                停止
              </button>
            </>
          )}
          {status === "starting" && (
            <div className="flex items-center gap-1.5">
              <div className="w-3 h-3 border-2 border-warning border-t-transparent rounded-full animate-spin" />
              <span className="text-xs text-warning">启动中...</span>
            </div>
          )}
        </div>
      </div>

      {/* Content */}
      <div className="flex-1 relative bg-background">
        {status === "idle" && (
          <div className="flex flex-col items-center justify-center h-full text-muted-foreground">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5" className="mb-4 opacity-50">
              <circle cx="12" cy="12" r="10"/>
              <circle cx="12" cy="12" r="6"/>
              <circle cx="12" cy="12" r="2"/>
            </svg>
            <p className="text-sm mb-2">靶场未启动</p>
            <p className="text-xs opacity-70">点击上方「启动靶场」开始练习</p>
          </div>
        )}

        {status === "starting" && (
          <div className="flex flex-col items-center justify-center h-full">
            <div className="w-8 h-8 border-3 border-accent border-t-transparent rounded-full animate-spin mb-4" />
            <p className="text-sm text-muted-foreground">正在启动漏洞环境...</p>
            <p className="text-xs text-muted-foreground mt-1 opacity-70">首次启动可能需要 10-30 秒</p>
          </div>
        )}

        {status === "running" && url && (
          <iframe
            src={url}
            className="w-full h-full border-0"
            onLoad={() => setIframeLoaded(true)}
            sandbox="allow-scripts allow-forms allow-same-origin"
          />
        )}

        {status === "error" && (
          <div className="flex flex-col items-center justify-center h-full text-danger">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5" className="mb-4">
              <circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
            </svg>
            <p className="text-sm mb-2">启动失败</p>
            <button onClick={onStart} className="text-xs text-accent hover:underline">重试</button>
          </div>
        )}
      </div>
    </div>
  )
}
