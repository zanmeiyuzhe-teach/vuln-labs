"use client"

import { useState, useEffect } from "react"
import { api } from "@/lib/api"

const categories = [
  { slug: "brute-force", name: "暴力破解", icon: "Shield", color: "#ef4444", desc: "表单暴力破解、验证码绕过、Token 防爆破" },
  { slug: "xss", name: "XSS 跨站脚本", icon: "Code", color: "#f59e0b", desc: "反射型、存储型、DOM 型、XSS 盲打" },
  { slug: "csrf", name: "CSRF 跨站请求伪造", icon: "ArrowRightLeft", color: "#8b5cf6", desc: "GET 型、POST 型、带 Token 的 CSRF" },
  { slug: "sqli", name: "SQL 注入", icon: "Database", color: "#3b82f6", desc: "数字型、字符型、搜索型、盲注、宽字节" },
  { slug: "rce", name: "命令执行", icon: "Terminal", color: "#10b981", desc: "Ping 执行、代码执行（eval）" },
  { slug: "file-inclusion", name: "文件包含", icon: "FolderOpen", color: "#06b6d4", desc: "LFI 本地包含、RFI 远程包含" },
  { slug: "file-upload", name: "文件上传/下载", icon: "Upload", color: "#ec4899", desc: "不安全文件上传、任意文件下载" },
  { slug: "privilege", name: "越权", icon: "Lock", color: "#f97316", desc: "水平越权、垂直越权、未授权访问" },
  { slug: "other", name: "其他高频漏洞", icon: "Bug", color: "#6b7280", desc: "目录遍历、信息泄露、反序列化、XXE、SSRF" },
]

export default function Dashboard() {
  const [stats, setStats] = useState({ total: 36, completed: 0, inProgress: 0, aiAssists: 0 })

  useEffect(() => {
    async function fetchStats() {
      const res = await api.getProgress()
      if (res.success && res.data) {
        setStats({
          total: res.data.total || 36,
          completed: res.data.completed || 0,
          inProgress: res.data.in_progress || 0,
          aiAssists: 0,
        })
      }
    }
    fetchStats()
  }, [])

  return (
    <div className="p-8 max-w-7xl mx-auto animate-fade-in">
      {/* Header */}
      <div className="mb-8">
        <h1 className="text-3xl font-bold tracking-tight">CyberRange</h1>
        <p className="text-muted-foreground mt-1">网安基础学习靶场平台 — 在实战中掌握安全技能</p>
      </div>

      {/* Stats */}
      <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <StatCard label="靶场总数" value={stats.total} icon="target" color="#3b82f6" />
        <StatCard label="已完成" value={stats.completed} icon="check" color="#10b981" />
        <StatCard label="进行中" value={stats.inProgress} icon="play" color="#f59e0b" />
        <StatCard label="AI 辅助次数" value={stats.aiAssists} icon="bot" color="#8b5cf6" />
      </div>

      {/* Categories Grid */}
      <div className="mb-4">
        <h2 className="text-xl font-semibold mb-4">漏洞分类</h2>
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          {categories.map((cat) => (
            <a
              key={cat.slug}
              href={`/labs/${cat.slug}`}
              className="group block p-5 rounded-xl border border-border bg-card hover:border-border-hover hover:bg-card/80 transition-all duration-200 hover:scale-[1.02]"
            >
              <div className="flex items-start gap-4">
                <div
                  className="w-10 h-10 rounded-lg flex items-center justify-center shrink-0"
                  style={{ background: `${cat.color}20`, color: cat.color }}
                >
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                    {cat.icon === "Shield" && <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>}
                    {cat.icon === "Code" && <><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></>}
                    {cat.icon === "ArrowRightLeft" && <><path d="M8 3L4 7l4 4"/><path d="M4 7h16"/><path d="M16 21l4-4-4-4"/><path d="M20 17H4"/></>}
                    {cat.icon === "Database" && <><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></>}
                    {cat.icon === "Terminal" && <><polyline points="4 17 10 11 4 5"/><line x1="12" y1="19" x2="20" y2="19"/></>}
                    {cat.icon === "FolderOpen" && <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>}
                    {cat.icon === "Upload" && <><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></>}
                    {cat.icon === "Lock" && <><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></>}
                    {cat.icon === "Bug" && <><path d="M8 2l1.88 1.88M14.12 3.88L16 2"/><circle cx="12" cy="8" r="2"/><path d="M12 10v4"/><path d="M6 14h12"/></>}
                  </svg>
                </div>
                <div className="flex-1 min-w-0">
                  <h3 className="font-medium text-foreground group-hover:text-accent transition-colors">{cat.name}</h3>
                  <p className="text-sm text-muted-foreground mt-1 line-clamp-2">{cat.desc}</p>
                </div>
              </div>
              <div className="mt-4 h-1.5 rounded-full bg-muted overflow-hidden">
                <div className="h-full rounded-full bg-accent/50" style={{ width: "0%" }} />
              </div>
              <div className="mt-2 flex justify-between text-xs text-muted-foreground">
                <span>4 个难度</span>
                <span>0% 完成</span>
              </div>
            </a>
          ))}
        </div>
      </div>
    </div>
  )
}

function StatCard({ label, value, icon, color }: { label: string; value: number; icon: string; color: string }) {
  return (
    <div className="p-4 rounded-xl border border-border bg-card">
      <div className="flex items-center justify-between">
        <span className="text-sm text-muted-foreground">{label}</span>
        <div className="w-8 h-8 rounded-lg flex items-center justify-center" style={{ background: `${color}15` }}>
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke={color} strokeWidth="2">
            {icon === "target" && <><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></>}
            {icon === "check" && <polyline points="20 6 9 17 4 12"/>}
            {icon === "play" && <polygon points="5 3 19 12 5 21 5 3"/>}
            {icon === "bot" && <><rect x="3" y="11" width="18" height="10" rx="2"/><circle cx="12" cy="5" r="2"/><path d="M12 7v4"/><line x1="8" y1="16" x2="8" y2="16"/><line x1="16" y1="16" x2="16" y2="16"/></>}
          </svg>
        </div>
      </div>
      <div className="mt-2 text-2xl font-bold" style={{ color }}>{value}</div>
    </div>
  )
}
