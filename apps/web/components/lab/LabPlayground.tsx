"use client"

import { useState } from "react"
import { LabIframe } from "./LabIframe"
import { ChatPanel } from "@/components/ai/ChatPanel"
import { TrafficTimeline } from "@/components/traffic/TrafficTimeline"
import { useLab } from "@/hooks/use-lab"
import { cn } from "@/lib/utils"

interface LabPlaygroundProps {
  labId: string
  labName: string
  className?: string
}

type RightPanel = "ai" | "traffic"

export function LabPlayground({ labId, labName, className }: LabPlaygroundProps) {
  const [rightPanel, setRightPanel] = useState<RightPanel>("ai")
  const lab = useLab(labId)

  // Mock traffic entries — will be replaced with real data
  const trafficEntries: any[] = []

  return (
    <div className={cn("flex h-[calc(100vh-4rem)]", className)}>
      {/* Left: Lab iframe */}
      <div className="flex-1 border-r border-border">
        <LabIframe
          url={lab.containerUrl}
          status={lab.status}
          onStart={lab.startLab}
          onStop={lab.stopLab}
          onReset={lab.resetLab}
        />
      </div>

      {/* Right: AI Chat / Traffic */}
      <div className="w-[400px] flex flex-col border-r border-border">
        {/* Tab switcher */}
        <div className="flex border-b border-border bg-card">
          <button
            onClick={() => setRightPanel("ai")}
            className={cn(
              "flex-1 px-4 py-2.5 text-xs font-medium transition-colors",
              rightPanel === "ai"
                ? "text-accent border-b-2 border-accent"
                : "text-muted-foreground hover:text-foreground"
            )}
          >
            AI 辅导员
          </button>
          <button
            onClick={() => setRightPanel("traffic")}
            className={cn(
              "flex-1 px-4 py-2.5 text-xs font-medium transition-colors",
              rightPanel === "traffic"
                ? "text-accent border-b-2 border-accent"
                : "text-muted-foreground hover:text-foreground"
            )}
          >
            流量记录
            {trafficEntries.length > 0 && (
              <span className="ml-1.5 px-1.5 py-0.5 rounded-full bg-accent text-white text-[10px]">
                {trafficEntries.length}
              </span>
            )}
          </button>
        </div>

        {/* Panel content */}
        <div className="flex-1 overflow-hidden">
          {rightPanel === "ai" ? (
            <ChatPanel labId={labId} labName={labName} />
          ) : (
            <TrafficTimeline entries={trafficEntries} />
          )}
        </div>
      </div>

      {/* Resize handle (visual only) */}
      <div className="w-1 bg-border hover:bg-accent cursor-col-resize transition-colors" />
    </div>
  )
}
