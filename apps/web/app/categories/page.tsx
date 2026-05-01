"use client"

const categories = [
  { slug: "brute-force", name: "暴力破解", color: "#ef4444", desc: "表单暴力破解、验证码绕过、Token 防爆破", labs: 4, icon: "Shield" },
  { slug: "xss", name: "XSS 跨站脚本", color: "#f59e0b", desc: "反射型、存储型、DOM 型、XSS 盲打", labs: 4, icon: "Code" },
  { slug: "csrf", name: "CSRF 跨站请求伪造", color: "#8b5cf6", desc: "GET 型、POST 型、带 Token 的 CSRF", labs: 4, icon: "ArrowRightLeft" },
  { slug: "sqli", name: "SQL 注入", color: "#3b82f6", desc: "数字型、字符型、搜索型、盲注、宽字节、Header 注入", labs: 4, icon: "Database" },
  { slug: "rce", name: "命令执行", color: "#10b981", desc: "Ping 执行、代码执行（eval）", labs: 4, icon: "Terminal" },
  { slug: "file-inclusion", name: "文件包含", color: "#06b6d4", desc: "本地文件包含（LFI）、远程文件包含（RFI）", labs: 4, icon: "FolderOpen" },
  { slug: "file-upload", name: "文件上传/下载", color: "#ec4899", desc: "不安全文件上传、任意文件下载", labs: 4, icon: "Upload" },
  { slug: "privilege", name: "越权", color: "#f97316", desc: "水平越权、垂直越权、未授权访问", labs: 4, icon: "Lock" },
  { slug: "other", name: "其他高频漏洞", color: "#6b7280", desc: "目录遍历、信息泄露、反序列化、XXE、SSRF、URL 重定向", labs: 4, icon: "Bug" },
]

export default function CategoriesPage() {
  return (
    <div className="p-8 max-w-7xl mx-auto animate-fade-in">
      <div className="mb-8">
        <h1 className="text-3xl font-bold tracking-tight">漏洞分类</h1>
        <p className="text-muted-foreground mt-1">9 大类漏洞，每类 4 个难度，共 36 个靶场</p>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        {categories.map((cat) => (
          <a
            key={cat.slug}
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
            <p className="text-sm text-muted-foreground mb-4">{cat.desc}</p>
            <div className="flex items-center justify-between text-xs text-muted-foreground">
              <span>{cat.labs} 个难度</span>
              <span className="group-hover:text-accent transition-colors">进入 →</span>
            </div>
          </a>
        ))}
      </div>
    </div>
  )
}
