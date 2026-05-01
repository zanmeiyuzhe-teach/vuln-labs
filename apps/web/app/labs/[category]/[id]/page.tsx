"use client"

import { useState, useEffect } from "react"
import { api } from "@/lib/api"
import { difficultyConfig, type Difficulty } from "@/lib/utils"
import { DifficultyBadge } from "@/components/lab/DifficultyBadge"
import { LabPlayground } from "@/components/lab/LabPlayground"

export default function LabDetailPage({ params }: { params: { category: string; id: string } }) {
  const [lab, setLab] = useState<any>(null)
  const [loading, setLoading] = useState(true)
  const [showPlayground, setShowPlayground] = useState(false)

  useEffect(() => {
    async function fetchLab() {
      setLoading(true)
      const res = await api.getLab(params.id)
      if (res.success) setLab(res.data)
      setLoading(false)
    }
    fetchLab()
  }, [params.id])

  if (loading) {
    return (
      <div className="flex items-center justify-center h-full">
        <div className="w-6 h-6 border-2 border-accent border-t-transparent rounded-full animate-spin" />
      </div>
    )
  }

  if (!lab) {
    return (
      <div className="flex flex-col items-center justify-center h-full text-muted-foreground">
        <p className="text-lg mb-2">靶场未找到</p>
        <a href={`/labs/${params.category}`} className="text-sm text-accent hover:underline">返回列表</a>
      </div>
    )
  }

  if (showPlayground) {
    return <LabPlayground labId={lab.id} labName={lab.name} />
  }

  const diff = lab.difficulty as Difficulty
  const cfg = difficultyConfig[diff]

  return (
    <div className="p-8 max-w-4xl mx-auto animate-fade-in">
      {/* Breadcrumb */}
      <div className="flex items-center gap-2 mb-6 text-sm">
        <a href="/categories" className="text-muted-foreground hover:text-foreground">漏洞分类</a>
        <span className="text-muted-foreground">/</span>
        <a href={`/labs/${params.category}`} className="text-muted-foreground hover:text-foreground">{params.category}</a>
        <span className="text-muted-foreground">/</span>
        <span className="text-foreground">{lab.name}</span>
      </div>

      {/* Header */}
      <div className="mb-8">
        <div className="flex items-center gap-3 mb-3">
          <h1 className="text-3xl font-bold tracking-tight">{lab.name}</h1>
          <DifficultyBadge difficulty={diff} />
        </div>
        <p className="text-muted-foreground">{lab.description}</p>
      </div>

      {/* Info grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
        <div className="p-5 rounded-xl border border-border bg-card">
          <h3 className="text-sm font-semibold mb-3">学习目标</h3>
          <ul className="space-y-2">
            {(lab.learning_objectives || []).map((obj: string, i: number) => (
              <li key={i} className="flex items-start gap-2 text-sm text-muted-foreground">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#10b981" strokeWidth="2" className="shrink-0 mt-0.5">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
                {obj}
              </li>
            ))}
          </ul>
        </div>

        <div className="p-5 rounded-xl border border-border bg-card">
          <h3 className="text-sm font-semibold mb-3">知识点</h3>
          <div className="flex flex-wrap gap-2">
            {(lab.knowledge_points || []).map((kp: string, i: number) => (
              <span key={i} className="px-2.5 py-1 rounded-lg text-xs bg-muted text-muted-foreground">
                {kp}
              </span>
            ))}
          </div>
        </div>
      </div>

      {/* Lab info */}
      <div className="p-5 rounded-xl border border-border bg-card mb-8">
        <h3 className="text-sm font-semibold mb-3">靶场信息</h3>
        <div className="grid grid-cols-3 gap-4 text-sm">
          <div>
            <span className="text-muted-foreground">难度</span>
            <p className="font-medium mt-0.5">{cfg.label}</p>
          </div>
          <div>
            <span className="text-muted-foreground">超时</span>
            <p className="font-medium mt-0.5">{lab.timeout_minutes} 分钟</p>
          </div>
          <div>
            <span className="text-muted-foreground">Docker 镜像</span>
            <p className="font-medium mt-0.5 font-mono text-xs">{lab.docker_image || "待构建"}</p>
          </div>
        </div>
      </div>

      {/* Actions */}
      <div className="flex gap-3">
        <button
          onClick={() => setShowPlayground(true)}
          className="px-6 py-3 rounded-xl bg-accent text-white font-medium hover:bg-accent-hover transition-colors"
        >
          进入靶场
        </button>
        <button className="px-6 py-3 rounded-xl border border-border text-muted-foreground font-medium hover:text-foreground hover:border-border-hover transition-colors">
          学习文档
        </button>
      </div>
    </div>
  )
}
