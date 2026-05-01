"use client"

import { useState, useEffect } from "react"
import { api } from "@/lib/api"

export default function CategoriesPage() {
  const [categories, setCategories] = useState<any[]>([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    async function fetchCategories() {
      const res = await api.getCategories()
      if (res.success && res.data) {
        setCategories(res.data)
      }
      setLoading(false)
    }
    fetchCategories()
  }, [])

  if (loading) {
    return (
      <div className="flex items-center justify-center h-full">
        <div className="w-6 h-6 border-2 border-accent border-t-transparent rounded-full animate-spin" />
      </div>
    )
  }

  return (
    <div className="p-8 max-w-7xl mx-auto animate-fade-in">
      <div className="mb-8">
        <h1 className="text-3xl font-bold tracking-tight">漏洞分类</h1>
        <p className="text-muted-foreground mt-1">{categories.length} 大类漏洞，每类 4 个难度，共 36 个靶场</p>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        {categories.map((cat) => (
          <a
            key={cat.id}
            href={`/labs/${cat.slug}`}
            className="group block p-6 rounded-xl border border-border bg-card hover:border-border-hover transition-all duration-200 hover:scale-[1.02]"
          >
            <div
              className="w-12 h-12 rounded-xl flex items-center justify-center mb-4"
              style={{ background: `${cat.color}15`, color: cat.color }}
            >
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
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
            <h3 className="text-lg font-semibold group-hover:text-accent transition-colors mb-1">{cat.name}</h3>
            <p className="text-sm text-muted-foreground mb-4">{cat.description}</p>
            <div className="flex items-center justify-between text-xs text-muted-foreground">
              <span>4 个难度</span>
              <span className="group-hover:text-accent transition-colors">进入 →</span>
            </div>
          </a>
        ))}
      </div>
    </div>
  )
}
