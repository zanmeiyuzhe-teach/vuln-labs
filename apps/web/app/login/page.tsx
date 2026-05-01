"use client"

import { useState } from "react"
import { api } from "@/lib/api"

export default function LoginPage() {
  const [username, setUsername] = useState("")
  const [password, setPassword] = useState("")
  const [error, setError] = useState("")
  const [loading, setLoading] = useState(false)

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    setError("")
    setLoading(true)

    const res = await api.login({ username, password })
    if (res.success && res.data) {
      api.setToken(res.data.token)
      window.location.href = "/"
    } else {
      setError(res.error || "登录失败")
    }
    setLoading(false)
  }

  return (
    <div className="min-h-screen flex items-center justify-center bg-background p-4">
      <div className="w-full max-w-md">
        {/* Logo */}
        <div className="flex items-center justify-center gap-2.5 mb-8">
          <div className="w-10 h-10 rounded-lg bg-accent flex items-center justify-center">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
            </svg>
          </div>
          <span className="text-xl font-semibold tracking-tight">CyberRange</span>
        </div>

        <div className="p-6 rounded-xl border border-border bg-card">
          <h1 className="text-2xl font-bold text-center mb-2">登录</h1>
          <p className="text-sm text-muted-foreground text-center mb-6">登录你的 CyberRange 账户</p>

          <form onSubmit={handleSubmit} className="space-y-4">
            <div>
              <label className="block text-sm font-medium mb-1.5">用户名</label>
              <input
                type="text"
                value={username}
                onChange={(e) => setUsername(e.target.value)}
                className="w-full px-3 py-2 rounded-lg bg-muted border border-border text-foreground text-sm focus:outline-none focus:border-accent transition-colors"
                placeholder="输入用户名"
                required
              />
            </div>

            <div>
              <label className="block text-sm font-medium mb-1.5">密码</label>
              <input
                type="password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                className="w-full px-3 py-2 rounded-lg bg-muted border border-border text-foreground text-sm focus:outline-none focus:border-accent transition-colors"
                placeholder="输入密码"
                required
              />
            </div>

            {error && (
              <p className="text-sm text-danger bg-danger/10 px-3 py-2 rounded-lg">{error}</p>
            )}

            <button
              type="submit"
              disabled={loading}
              className="w-full px-4 py-2.5 rounded-lg bg-accent text-white text-sm font-medium hover:bg-accent-hover transition-colors disabled:opacity-50"
            >
              {loading ? "登录中..." : "登录"}
            </button>
          </form>

          <p className="text-sm text-muted-foreground text-center mt-4">
            没有账户？{" "}
            <a href="/register" className="text-accent hover:underline">注册</a>
          </p>
        </div>
      </div>
    </div>
  )
}
