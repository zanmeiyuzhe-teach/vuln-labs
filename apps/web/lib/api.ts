const API_BASE = process.env.NEXT_PUBLIC_API_URL || "http://localhost:8080/api/v1"

interface APIResponse<T = any> {
  success: boolean
  data?: T
  error?: string
  message?: string
}

class APIClient {
  private token: string | null = null

  setToken(token: string | null) {
    this.token = token
    if (token) {
      localStorage.setItem("token", token)
    } else {
      localStorage.removeItem("token")
    }
  }

  getToken(): string | null {
    if (!this.token && typeof window !== "undefined") {
      this.token = localStorage.getItem("token")
    }
    return this.token
  }

  private async request<T>(method: string, path: string, body?: any): Promise<APIResponse<T>> {
    const headers: Record<string, string> = { "Content-Type": "application/json" }
    const token = this.getToken()
    if (token) headers["Authorization"] = `Bearer ${token}`

    const res = await fetch(`${API_BASE}${path}`, {
      method,
      headers,
      body: body ? JSON.stringify(body) : undefined,
    })

    return res.json()
  }

  // Auth
  register(data: { username: string; email: string; password: string }) {
    return this.request("POST", "/auth/register", data)
  }

  login(data: { username: string; password: string }) {
    return this.request<{ token: string; user: any }>("POST", "/auth/login", data)
  }

  me() {
    return this.request("GET", "/auth/me")
  }

  // Categories
  getCategories() {
    return this.request<any[]>("GET", "/categories")
  }

  getCategory(slug: string) {
    return this.request("GET", `/categories/${slug}`)
  }

  // Labs
  getLabs(params?: { category?: string; difficulty?: string }) {
    const q = new URLSearchParams(params as any).toString()
    return this.request<any[]>("GET", `/labs${q ? `?${q}` : ""}`)
  }

  getLab(id: string) {
    return this.request("GET", `/labs/${id}`)
  }

  startLab(id: string) {
    return this.request("POST", `/labs/${id}/start`)
  }

  stopLab(id: string) {
    return this.request("POST", `/labs/${id}/stop`)
  }

  resetLab(id: string) {
    return this.request("POST", `/labs/${id}/reset`)
  }

  // Lessons
  getLessons(category?: string) {
    const q = category ? `?category=${category}` : ""
    return this.request<any[]>("GET", `/lessons${q}`)
  }

  getLesson(id: string) {
    return this.request("GET", `/lessons/${id}`)
  }

  // Progress
  getProgress() {
    return this.request("GET", "/progress")
  }

  getLabProgress(labId: string) {
    return this.request("GET", `/progress/${labId}`)
  }

  updateProgress(labId: string, data: any) {
    return this.request("POST", `/progress/${labId}`, data)
  }

  // Traffic
  getTraffic(labId: string) {
    return this.request<any[]>("GET", `/traffic/${labId}`)
  }

  recordTraffic(data: any) {
    return this.request("POST", "/traffic", data)
  }

  // AI
  async *chatStream(labId: string, messages: any[], model?: string) {
    const token = this.getToken()
    const res = await fetch(`${API_BASE}/ai/chat`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        ...(token ? { Authorization: `Bearer ${token}` } : {}),
      },
      body: JSON.stringify({ lab_id: labId, messages, model }),
    })

    if (!res.ok || !res.body) {
      yield { error: `AI request failed: ${res.status}` }
      return
    }

    const reader = res.body.getReader()
    const decoder = new TextDecoder()
    let buffer = ""

    while (true) {
      const { done, value } = await reader.read()
      if (done) break

      buffer += decoder.decode(value, { stream: true })
      const lines = buffer.split("\n")
      buffer = lines.pop() || ""

      for (const line of lines) {
        if (line.startsWith("data: ")) {
          const data = line.slice(6)
          if (data === "[DONE]") return
          try {
            yield JSON.parse(data)
          } catch {}
        }
      }
    }
  }

  getModels() {
    return this.request<any[]>("GET", "/ai/models")
  }

  saveAIConfig(data: { provider: string; api_key: string; model?: string; base_url?: string }) {
    return this.request("POST", "/ai/config", data)
  }
}

export const api = new APIClient()
