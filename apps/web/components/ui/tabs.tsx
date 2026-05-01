"use client"

import { useState, createContext, useContext, type ReactNode } from "react"
import { cn } from "@/lib/utils"

interface TabsContextValue {
  activeTab: string
  setActiveTab: (value: string) => void
}

const TabsContext = createContext<TabsContextValue>({
  activeTab: "",
  setActiveTab: () => {},
})

interface TabsProps {
  defaultValue: string
  value?: string
  onValueChange?: (value: string) => void
  children: ReactNode
  className?: string
}

function Tabs({ defaultValue, value, onValueChange, children, className }: TabsProps) {
  const [internal, setInternal] = useState(defaultValue)
  const activeTab = value ?? internal
  const setActiveTab = (v: string) => {
    setInternal(v)
    onValueChange?.(v)
  }

  return (
    <TabsContext.Provider value={{ activeTab, setActiveTab }}>
      <div className={className}>{children}</div>
    </TabsContext.Provider>
  )
}

function TabsList({ children, className }: { children: ReactNode; className?: string }) {
  return (
    <div className={cn("inline-flex h-10 items-center justify-center rounded-lg bg-muted p-1", className)}>
      {children}
    </div>
  )
}

function TabsTrigger({ value, children, className }: { value: string; children: ReactNode; className?: string }) {
  const { activeTab, setActiveTab } = useContext(TabsContext)
  const isActive = activeTab === value

  return (
    <button
      onClick={() => setActiveTab(value)}
      className={cn(
        "inline-flex items-center justify-center whitespace-nowrap rounded-md px-3 py-1.5 text-sm font-medium transition-all",
        isActive
          ? "bg-card text-foreground shadow-sm"
          : "text-muted-foreground hover:text-foreground",
        className
      )}
    >
      {children}
    </button>
  )
}

function TabsContent({ value, children, className }: { value: string; children: ReactNode; className?: string }) {
  const { activeTab } = useContext(TabsContext)
  if (activeTab !== value) return null
  return <div className={className}>{children}</div>
}

export { Tabs, TabsList, TabsTrigger, TabsContent }
