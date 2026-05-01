"use client"

import { useState } from "react"
import { difficultyConfig, type Difficulty } from "@/lib/utils"

const categoryMap: Record<string, { name: string; color: string; desc: string; icon: string }> = {
  "brute-force": { name: "暴力破解", color: "#ef4444", desc: "表单暴力破解、验证码绕过、Token 防爆破", icon: "Shield" },
  "xss": { name: "XSS 跨站脚本", color: "#f59e0b", desc: "反射型、存储型、DOM 型、XSS 盲打", icon: "Code" },
  "csrf": { name: "CSRF 跨站请求伪造", color: "#8b5cf6", desc: "GET 型、POST 型、带 Token 的 CSRF", icon: "ArrowRightLeft" },
  "sqli": { name: "SQL 注入", color: "#3b82f6", desc: "数字型、字符型、搜索型、盲注、宽字节", icon: "Database" },
  "rce": { name: "命令执行", color: "#10b981", desc: "Ping 执行、代码执行（eval）", icon: "Terminal" },
  "file-inclusion": { name: "文件包含", color: "#06b6d4", desc: "LFI 本地包含、RFI 远程包含", icon: "FolderOpen" },
  "file-upload": { name: "文件上传/下载", color: "#ec4899", desc: "不安全文件上传、任意文件下载", icon: "Upload" },
  "privilege": { name: "越权", color: "#f97316", desc: "水平越权、垂直越权、未授权访问", icon: "Lock" },
  "other": { name: "其他高频漏洞", color: "#6b7280", desc: "目录遍历、信息泄露、反序列化、XXE、SSRF", icon: "Bug" },
}

const mockLabs: Record<string, Record<Difficulty, { name: string; desc: string; points: string[] }[]>> = {
  "sqli": {
    easy: [{ name: "数字型注入", desc: "产品查询页面，参数直接拼接到SQL语句", points: ["数字型注入", "UNION查询", "信息收集"] }],
    simple: [{ name: "字符型与搜索型注入", desc: "登录表单和搜索框使用字符型拼接", points: ["字符型注入", "引号闭合", "万能密码"] }],
    hard: [{ name: "盲注技术", desc: "页面不显示结果，需要布尔判断和时间延迟", points: ["布尔盲注", "延时盲注", "报错注入"] }],
    hell: [{ name: "WAF绕过与堆叠注入", desc: "有WAF防护的搜索接口，过滤常见关键字", points: ["WAF绕过", "宽字节注入", "堆叠注入", "DNS外带"] }],
  },
  "xss": {
    easy: [{ name: "反射型XSS (GET)", desc: "搜索框将用户输入直接反射到页面", points: ["反射型XSS", "GET参数注入", "script标签"] }],
    simple: [{ name: "存储型与DOM型XSS", desc: "留言板存在存储型XSS，前端JS存在DOM型漏洞", points: ["存储型XSS", "DOM型XSS", "Cookie窃取"] }],
    hard: [{ name: "XSS盲打与键盘记录", desc: "XSS盲打平台，注入脚本在管理员后台执行", points: ["XSS盲打", "键盘记录", "fetch请求"] }],
    hell: [{ name: "CSP绕过与钓鱼攻击", desc: "有CSP防护的页面，需要绕过内容安全策略", points: ["CSP绕过", "钓鱼攻击", "base-uri利用"] }],
  },
}

// Fill remaining categories with placeholder data
for (const slug of Object.keys(categoryMap)) {
  if (!mockLabs[slug]) {
    mockLabs[slug] = {
      easy: [{ name: `${categoryMap[slug].name}入门`, desc: `学习${categoryMap[slug].name}的基本原理`, points: ["基础概念", "入门利用"] }],
      simple: [{ name: `${categoryMap[slug].name}进阶`, desc: `掌握${categoryMap[slug].name}的进阶技巧`, points: ["进阶技巧", "绕过方法"] }],
      hard: [{ name: `${categoryMap[slug].name}高级`, desc: `应对${categoryMap[slug].name}的真实场景`, points: ["高级利用", "综合运用"] }],
      hell: [{ name: `${categoryMap[slug].name}地狱`, desc: `${categoryMap[slug].name}的终极挑战`, points: ["终极挑战", "完整攻击链"] }],
    }
  }
}

export default function LabsPage({ params }: { params: { category: string } }) {
  const [activeTab, setActiveTab] = useState<Difficulty>("easy")
  const cat = categoryMap[params.category] || { name: params.category, color: "#6b7280", desc: "", icon: "Bug" }
  const labs = mockLabs[params.category]?.[activeTab] || []

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

      {/* Lab Cards */}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
        {labs.map((lab, i) => (
          <div
            key={i}
            className="p-5 rounded-xl border border-border bg-card hover:border-border-hover transition-all duration-200 hover:scale-[1.01]"
          >
            <div className="flex items-start justify-between mb-3">
              <h3 className="font-semibold text-lg">{lab.name}</h3>
              <span className={`px-2.5 py-0.5 rounded-md text-xs font-medium border ${difficultyConfig[activeTab].color}`}>
                {difficultyConfig[activeTab].label}
              </span>
            </div>
            <p className="text-sm text-muted-foreground mb-4">{lab.desc}</p>
            <div className="flex flex-wrap gap-1.5 mb-4">
              {lab.points.map((p, j) => (
                <span key={j} className="px-2 py-0.5 rounded text-xs bg-muted text-muted-foreground">
                  {p}
                </span>
              ))}
            </div>
            <div className="flex gap-2">
              <button className="flex-1 px-4 py-2 rounded-lg bg-accent text-white text-sm font-medium hover:bg-accent-hover transition-colors">
                启动靶场
              </button>
              <button className="px-4 py-2 rounded-lg border border-border text-sm text-muted-foreground hover:text-foreground hover:border-border-hover transition-colors">
                学习文档
              </button>
            </div>
          </div>
        ))}
      </div>
    </div>
  )
}
