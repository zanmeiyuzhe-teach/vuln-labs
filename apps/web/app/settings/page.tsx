"use client"

import { useState } from "react"

export default function SettingsPage() {
  const [apiKey, setApiKey] = useState("")
  const [apiBase, setApiBase] = useState("https://dashscope.aliyuncs.com/compatible-mode/v1")
  const [model, setModel] = useState("qwen3.5-plus")
  const [saved, setSaved] = useState(false)

  const handleSave = () => {
    localStorage.setItem("ai_config", JSON.stringify({ apiKey, apiBase, model }))
    setSaved(true)
    setTimeout(() => setSaved(false), 2000)
  }

  return (
    <div className="p-8 max-w-3xl mx-auto animate-fade-in">
      <h1 className="text-3xl font-bold tracking-tight mb-8">设置</h1>

      {/* AI Configuration */}
      <section className="p-6 rounded-xl border border-border bg-card mb-6">
        <h2 className="text-lg font-semibold mb-4">AI 模型配置</h2>
        <p className="text-sm text-muted-foreground mb-6">
          配置 AI 大模型 API，用于辅导员模式（给提示）和 Agent 模式（自动做题）。所有请求从本地发出，不经过第三方服务器。
        </p>

        <div className="space-y-4">
          <div>
            <label className="block text-sm font-medium mb-1.5">API 地址</label>
            <input
              type="text"
              value={apiBase}
              onChange={(e) => setApiBase(e.target.value)}
              className="w-full px-3 py-2 rounded-lg bg-muted border border-border text-foreground text-sm focus:outline-none focus:border-accent transition-colors"
              placeholder="https://api.openai.com/v1"
            />
          </div>

          <div>
            <label className="block text-sm font-medium mb-1.5">API Key</label>
            <input
              type="password"
              value={apiKey}
              onChange={(e) => setApiKey(e.target.value)}
              className="w-full px-3 py-2 rounded-lg bg-muted border border-border text-foreground text-sm focus:outline-none focus:border-accent transition-colors"
              placeholder="sk-..."
            />
            <p className="text-xs text-muted-foreground mt-1">密钥仅存储在本地浏览器，不会上传到任何服务器</p>
          </div>

          <div>
            <label className="block text-sm font-medium mb-1.5">模型</label>
            <select
              value={model}
              onChange={(e) => setModel(e.target.value)}
              className="w-full px-3 py-2 rounded-lg bg-muted border border-border text-foreground text-sm focus:outline-none focus:border-accent transition-colors"
            >
              <optgroup label="DashScope (阿里云)">
                <option value="qwen3.5-plus">Qwen 3.5 Plus</option>
                <option value="kimi-k2.6">Kimi K2.6</option>
              </optgroup>
              <optgroup label="Anthropic">
                <option value="claude-sonnet-4-20250514">Claude Sonnet 4</option>
              </optgroup>
              <optgroup label="OpenAI">
                <option value="gpt-4o">GPT-4o</option>
              </optgroup>
            </select>
          </div>

          <button
            onClick={handleSave}
            className="px-4 py-2 rounded-lg bg-accent text-white text-sm font-medium hover:bg-accent-hover transition-colors"
          >
            {saved ? "已保存" : "保存配置"}
          </button>
        </div>
      </section>

      {/* Theme */}
      <section className="p-6 rounded-xl border border-border bg-card mb-6">
        <h2 className="text-lg font-semibold mb-4">外观</h2>
        <div className="flex gap-3">
          <button className="flex-1 p-4 rounded-lg border-2 border-accent bg-[#0a0a0a] text-center">
            <div className="w-8 h-8 rounded-full bg-accent mx-auto mb-2" />
            <span className="text-sm font-medium">深色主题</span>
          </button>
          <button className="flex-1 p-4 rounded-lg border border-border bg-[#ffffff] text-[#0a0a0a] text-center opacity-50">
            <div className="w-8 h-8 rounded-full bg-[#0a0a0a] mx-auto mb-2" />
            <span className="text-sm font-medium">浅色主题</span>
          </button>
        </div>
        <p className="text-xs text-muted-foreground mt-3">浅色主题即将推出</p>
      </section>

      {/* About */}
      <section className="p-6 rounded-xl border border-border bg-card">
        <h2 className="text-lg font-semibold mb-2">关于 CyberRange</h2>
        <p className="text-sm text-muted-foreground">
          开源网安基础学习靶场平台 v0.1.0 — 在实战中掌握安全技能
        </p>
      </section>
    </div>
  )
}
