import { type ClassValue, clsx } from "clsx"
import { twMerge } from "tailwind-merge"

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs))
}

export function formatDate(date: string | Date) {
  return new Date(date).toLocaleDateString("zh-CN", {
    year: "numeric",
    month: "2-digit",
    day: "2-digit",
  })
}

export function formatDuration(seconds: number) {
  const h = Math.floor(seconds / 3600)
  const m = Math.floor((seconds % 3600) / 60)
  const s = seconds % 60
  if (h > 0) return `${h}h ${m}m`
  if (m > 0) return `${m}m ${s}s`
  return `${s}s`
}

export const difficultyConfig = {
  easy: { label: "入门", color: "bg-emerald-500/20 text-emerald-400 border-emerald-500/30", order: 1 },
  simple: { label: "简单", color: "bg-blue-500/20 text-blue-400 border-blue-500/30", order: 2 },
  hard: { label: "困难", color: "bg-orange-500/20 text-orange-400 border-orange-500/30", order: 3 },
  hell: { label: "地狱", color: "bg-red-500/20 text-red-400 border-red-500/30", order: 4 },
} as const

export type Difficulty = keyof typeof difficultyConfig
