"use client"

import { useState, useEffect } from "react"
import { api } from "@/lib/api"
import { difficultyConfig, type Difficulty } from "@/lib/utils"
import { DifficultyBadge } from "@/components/lab/DifficultyBadge"

const categoryMeta: Record<string, { name: string; color: string; desc: string }> = {
  "brute-force": { name: "暴力破解", color: "#ef4444", desc: "表单暴力破解、验证码绕过、Token 防爆破" },
  "xss": { name: "XSS 跨站脚本", color: "#f59e0b", desc: "反射型、存储型、DOM 型、XSS 盲打" },
  "csrf": { name: "CSRF 跨站请求伪造", color: "#8b5cf6", desc: "GET 型、POST 型、带 Token 的 CSRF" },
  "sqli": { name: "SQL 注入", color: "#3b82f6", desc: "数字型、字符型、搜索型、盲注、宽字节" },
  "rce": { name: "命令执行", color: "#10b981", desc: "Ping 执行、代码执行（eval）" },
  "file-inclusion": { name: "文件包含", color: "#06b6d4", desc: "LFI 本地包含、RFI 远程包含" },
  "file-upload": { name: "文件上传/下载", color: "#ec4899", desc: "不安全文件上传、任意文件下载" },
  "privilege": { name: "越权", color: "#f97316", desc: "水平越权、垂直越权、未授权访问" },
  "other": { name: "其他高频漏洞", color: "#6b7280", desc: "目录遍历、信息泄露、反序列化、XXE、SSRF" },
}

export default function LabsPage({ params }: { params: { category: string } }) {
  const [activeTab, setActiveTab] = useState<Difficulty>("easy")
  const [labs, setLabs] = useState<any[]>([])
  const [loading, setLoading] = useState(true)
  const cat = categoryMeta[params.category] || { name: params.category, color: "#6b7280", desc: "" }

  useEffect(() => {
    async function fetchLabs() {
      setLoading(true)
      const res = await api.getLabs({ category: params.category, difficulty: activeTab })
      if (res.success && res.data) {
        setLabs(res.data)
      } else {
        setLabs([])
      }
      setLoading(false)
    }
    fetchLabs()
  }, [params.category, activeTab])

  const tabs: Difficulty[] = ["easy", "simple", "hard", "hell"]

  return (
    <div className="p-8 max-w-7xl mx-auto animate-fade-in">
      {/* Category Header */}
      <div className="mb-8">
        <div className="flex items-center gap-3 mb-2">
          <a href="/categories" className="text-muted-foreground hover:text-foreground text-sm">漏洞分类</a>
          <span className="text-muted-foreground">/</span>
          <span className="text-sm" style={{ color: cat.color }}>{cat.name}</span>
        </div>
        <h1 className="text-3xl font-bold tracking-tight" style={{ color: cat.color }}>{cat.name}</h1>
        <p className="text-muted-foreground mt-1">{cat.desc}</p>
      </div>

      {/* Difficulty Tabs */}
      <div className="flex gap-2 mb-6">
        {tabs.map((tab) => {
          const cfg = difficultyConfig[tab]
          return (
            <button
              key={tab}
              onClick={() => setActiveTab(tab)}
              className={`px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 border ${
                activeTab === tab
                  ? cfg.color + " border-current"
                  : "text-muted-foreground border-border hover:border-border-hover hover:text-foreground"
              }`}
            >
              {cfg.label}
            </button>
          )
        })}
      </div>

      {/* Loading */}
      {loading && (
        <div className="flex items-center justify-center py-12">
          <div className="w-6 h-6 border-2 border-accent border-t-transparent rounded-full animate-spin" />
        </div>
      )}

      {/* Lab Cards */}
      {!loading && (
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          {labs.map((lab) => (
            <div
              key={lab.id}
              className="p-5 rounded-xl border border-border bg-card hover:border-border-hover transition-all duration-200 hover:scale-[1.01]"
            >
              <div className="flex items-start justify-between mb-3">
                <h3 className="font-semibold text-lg">{lab.name}</h3>
                <DifficultyBadge difficulty={lab.difficulty as Difficulty} />
              </div>
              <p className="text-sm text-muted-foreground mb-4">{lab.description}</p>
              <div className="flex flex-wrap gap-1.5 mb-4">
                {(lab.knowledge_points || []).map((p: string, j: number) => (
                  <span key={j} className="px-2 py-0.5 rounded text-xs bg-muted text-muted-foreground">
                    {p}
                  </span>
                ))}
              </div>
              <div className="flex gap-2">
                <a
                  href={`/labs/${params.category}/${lab.id}`}
                  className="flex-1 px-4 py-2 rounded-lg bg-accent text-white text-sm font-medium hover:bg-accent-hover transition-colors text-center"
                >
                  启动靶场
                </a>
                <button className="px-4 py-2 rounded-lg border border-border text-sm text-muted-foreground hover:text-foreground hover:border-border-hover transition-colors">
                  学习文档
                </button>
              </div>
            </div>
          ))}

          {labs.length === 0 && (
            <div className="col-span-2 text-center py-12 text-muted-foreground">
              <p className="text-lg mb-2">该难度暂无靶场</p>
              <p className="text-sm">请尝试其他难度</p>
            </div>
          )}
        </div>
      )}
    </div>
  )
}
