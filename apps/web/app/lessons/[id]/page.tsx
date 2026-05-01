"use client"

import { useState, useEffect } from "react"
import { api } from "@/lib/api"

export default function LessonPage({ params }: { params: { id: string } }) {
  const [lesson, setLesson] = useState<any>(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    async function fetchLesson() {
      setLoading(true)
      const res = await api.getLesson(params.id)
      if (res.success) setLesson(res.data)
      setLoading(false)
    }
    fetchLesson()
  }, [params.id])

  if (loading) {
    return (
      <div className="flex items-center justify-center h-full">
        <div className="w-6 h-6 border-2 border-accent border-t-transparent rounded-full animate-spin" />
      </div>
    )
  }

  if (!lesson) {
    return (
      <div className="flex flex-col items-center justify-center h-full text-muted-foreground">
        <p className="text-lg mb-2">文档未找到</p>
      </div>
    )
  }

  return (
    <div className="p-8 max-w-3xl mx-auto animate-fade-in">
      {/* Header */}
      <div className="mb-8">
        <div className="flex items-center gap-2 text-sm text-muted-foreground mb-3">
          <span>学习文档</span>
          <span>/</span>
          <span>{lesson.title}</span>
        </div>
        <h1 className="text-3xl font-bold tracking-tight mb-2">{lesson.title}</h1>
        <div className="flex items-center gap-4 text-sm text-muted-foreground">
          <span>预计阅读 {lesson.reading_time_minutes || 5} 分钟</span>
        </div>
      </div>

      {/* Content */}
      <article className="prose prose-invert max-w-none">
        <div
          className="text-foreground leading-relaxed"
          dangerouslySetInnerHTML={{ __html: renderMarkdown(lesson.content_md) }}
        />
      </article>
    </div>
  )
}

// Simple markdown renderer — will be replaced with react-markdown
function renderMarkdown(md: string): string {
  if (!md) return ""
  return md
    .replace(/^### (.+)$/gm, '<h3 class="text-xl font-semibold mt-8 mb-3">$1</h3>')
    .replace(/^## (.+)$/gm, '<h2 class="text-2xl font-bold mt-10 mb-4">$1</h2>')
    .replace(/^# (.+)$/gm, '<h1 class="text-3xl font-bold mt-12 mb-6">$1</h1>')
    .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
    .replace(/\*(.+?)\*/g, '<em>$1</em>')
    .replace(/`(.+?)`/g, '<code class="bg-muted px-1.5 py-0.5 rounded text-sm font-mono">$1</code>')
    .replace(/^- (.+)$/gm, '<li class="ml-4 mb-1">$1</li>')
    .replace(/\n\n/g, '</p><p class="mb-4">')
    .replace(/\n/g, '<br/>')
}
