"use client"

import { useState } from "react"
import { api } from "@/lib/api"

export default function RegisterPage() {
  const [username, setUsername] = useState("")
  const [email, setEmail] = useState("")
  const [password, setPassword] = useState("")
  const [confirmPassword, setConfirmPassword] = useState("")
  const [error, setError] = useState("")
  const [loading, setLoading] = useState(false)

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    setError("")

    if (password !== confirmPassword) {
      setError("两次输入的密码不一致")
      return
    }

    if (password.length < 6) {
      setError("密码长度至少 6 位")
      return
    }

    setLoading(true)
    const res = await api.register({ username, email, password })
    if (res.success) {
      // Auto-login after registration
      const loginRes = await api.login({ username, password })
      if (loginRes.success && loginRes.data) {
        api.setToken(loginRes.data.token)
        window.location.href = "/"
      } else {
        window.location.href = "/login"
      }
    } else {
      setError(res.error || "注册失败")
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
          <h1 className="text-2xl font-bold text-center mb-2">注册</h1>
          <p className="text-sm text-muted-foreground text-center mb-6">创建你的 CyberRange 账户</p>

          <form onSubmit={handleSubmit} className="space-y-4">
            <div>
              <label className="block text-sm font-medium mb-1.5">用户名</label>
              <input
                type="text"
                value={username}
                onChange={(e) => setUsername(e.target.value)}
                className="w-full px-3 py-2 rounded-lg bg-muted border border-border text-foreground text-sm focus:outline-none focus:border-accent transition-colors"
                placeholder="3-50 个字符"
                required
              />
            </div>

            <div>
              <label className="block text-sm font-medium mb-1.5">邮箱</label>
              <input
                type="email"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                className="w-full px-3 py-2 rounded-lg bg-muted border border-border text-foreground text-sm focus:outline-none focus:border-accent transition-colors"
                placeholder="your@email.com"
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
                placeholder="至少 6 位"
                required
              />
            </div>

            <div>
              <label className="block text-sm font-medium mb-1.5">确认密码</label>
              <input
                type="password"
                value={confirmPassword}
                onChange={(e) => setConfirmPassword(e.target.value)}
                className="w-full px-3 py-2 rounded-lg bg-muted border border-border text-foreground text-sm focus:outline-none focus:border-accent transition-colors"
                placeholder="再次输入密码"
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
              {loading ? "注册中..." : "注册"}
            </button>
          </form>

          <p className="text-sm text-muted-foreground text-center mt-4">
            已有账户？{" "}
            <a href="/login" className="text-accent hover:underline">登录</a>
          </p>
        </div>
      </div>
    </div>
  )
}
