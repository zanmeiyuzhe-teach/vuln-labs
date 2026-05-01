-- CyberRange Database Schema
CREATE EXTENSION IF NOT EXISTS "pgcrypto";

CREATE TABLE users (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'user',
    avatar_url TEXT,
    settings JSONB DEFAULT '{}',
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE categories (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    icon VARCHAR(50),
    color VARCHAR(20),
    sort_order INT DEFAULT 0
);

CREATE TABLE labs (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    category_id UUID REFERENCES categories(id) ON DELETE CASCADE,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    difficulty VARCHAR(20) NOT NULL CHECK (difficulty IN ('easy', 'simple', 'hard', 'hell')),
    description TEXT,
    learning_objectives TEXT[],
    docker_image VARCHAR(200),
    docker_config JSONB DEFAULT '{}',
    default_port INT,
    timeout_minutes INT DEFAULT 60,
    knowledge_points TEXT[],
    sort_order INT DEFAULT 0
);

CREATE TABLE lessons (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    category_id UUID REFERENCES categories(id) ON DELETE CASCADE,
    lab_id UUID REFERENCES labs(id) ON DELETE SET NULL,
    title VARCHAR(200) NOT NULL,
    content_md TEXT NOT NULL,
    sort_order INT DEFAULT 0,
    reading_time_minutes INT DEFAULT 5
);

CREATE TABLE user_progress (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id UUID REFERENCES users(id) ON DELETE CASCADE,
    lab_id UUID REFERENCES labs(id) ON DELETE CASCADE,
    status VARCHAR(20) DEFAULT 'not_started' CHECK (status IN ('not_started', 'in_progress', 'completed')),
    started_at TIMESTAMPTZ,
    completed_at TIMESTAMPTZ,
    attempts INT DEFAULT 0,
    hints_used INT DEFAULT 0,
    ai_solved BOOLEAN DEFAULT FALSE,
    time_spent_seconds INT DEFAULT 0,
    UNIQUE(user_id, lab_id)
);

CREATE TABLE traffic_records (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id UUID REFERENCES users(id) ON DELETE CASCADE,
    lab_id UUID REFERENCES labs(id) ON DELETE CASCADE,
    session_id VARCHAR(100),
    step_number INT,
    request_method VARCHAR(10),
    request_url TEXT,
    request_headers JSONB DEFAULT '{}',
    request_body TEXT,
    response_status INT,
    response_headers JSONB DEFAULT '{}',
    response_body TEXT,
    notes TEXT,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE ai_sessions (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id UUID REFERENCES users(id) ON DELETE CASCADE,
    lab_id UUID REFERENCES labs(id) ON DELETE CASCADE,
    mode VARCHAR(20) NOT NULL CHECK (mode IN ('hint', 'auto-solve')),
    model VARCHAR(50),
    messages JSONB DEFAULT '[]',
    tokens_used INT DEFAULT 0,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE plugins (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(100) UNIQUE NOT NULL,
    version VARCHAR(20) NOT NULL,
    description TEXT,
    author VARCHAR(100),
    config JSONB DEFAULT '{}',
    enabled BOOLEAN DEFAULT TRUE,
    installed_at TIMESTAMPTZ DEFAULT NOW()
);

-- Seed: 9 vulnerability categories
INSERT INTO categories (name, slug, description, icon, color, sort_order) VALUES
('暴力破解', 'brute-force', '表单暴力破解、验证码绕过、Token 防爆破', 'Shield', '#ef4444', 1),
('XSS 跨站脚本', 'xss', '反射型、存储型、DOM 型、XSS 盲打', 'Code', '#f59e0b', 2),
('CSRF 跨站请求伪造', 'csrf', 'GET 型、POST 型、带 Token 的 CSRF', 'ArrowRightLeft', '#8b5cf6', 3),
('SQL 注入', 'sqli', '数字型、字符型、搜索型、盲注、宽字节、Header 注入', 'Database', '#3b82f6', 4),
('命令执行', 'rce', 'Ping 执行、代码执行（eval）', 'Terminal', '#10b981', 5),
('文件包含', 'file-inclusion', '本地文件包含（LFI）、远程文件包含（RFI）', 'FolderOpen', '#06b6d4', 6),
('文件上传/下载', 'file-upload', '不安全文件上传、任意文件下载', 'Upload', '#ec4899', 7),
('越权', 'privilege', '水平越权、垂直越权、未授权访问', 'Lock', '#f97316', 8),
('其他高频漏洞', 'other', '目录遍历、信息泄露、反序列化、XXE、SSRF、URL 重定向', 'Bug', '#6b7280', 9);

-- Seed: SQL injection labs (4 difficulties)
INSERT INTO labs (category_id, name, slug, difficulty, description, learning_objectives, docker_image, default_port, timeout_minutes, knowledge_points, sort_order)
SELECT id, 'SQL注入入门：数字型注入', 'sqli-numeric', 'easy',
    '一个产品查询页面，用户输入产品ID查看详情。参数直接拼接到SQL语句中，没有任何过滤。',
    ARRAY['理解SQL注入基本原理', '掌握UNION联合查询', '学会获取数据库信息'],
    'cyberrange/sqli-easy', 8081, 60,
    ARRAY['数字型注入', 'UNION查询', '信息收集'], 1
FROM categories WHERE slug = 'sqli';

INSERT INTO labs (category_id, name, slug, difficulty, description, learning_objectives, docker_image, default_port, timeout_minutes, knowledge_points, sort_order)
SELECT id, 'SQL注入进阶：字符型与搜索型', 'sqli-string', 'simple',
    '登录表单和搜索框使用字符型拼接，需要闭合引号才能注入。',
    ARRAY['理解字符型注入', '掌握引号闭合技巧', '学会搜索型注入'],
    'cyberrange/sqli-simple', 8082, 60,
    ARRAY['字符型注入', '引号闭合', '搜索型注入', '万能密码'], 2
FROM categories WHERE slug = 'sqli';

INSERT INTO labs (category_id, name, slug, difficulty, description, learning_objectives, docker_image, default_port, timeout_minutes, knowledge_points, sort_order)
SELECT id, 'SQL注入高级：盲注技术', 'sqli-blind', 'hard',
    '页面不显示查询结果，不返回报错信息。需要通过布尔判断和时间延迟来逐字符提取数据。',
    ARRAY['掌握布尔盲注', '掌握延时盲注', '学会报错注入'],
    'cyberrange/sqli-hard', 8083, 60,
    ARRAY['布尔盲注', '延时盲注', '报错注入', 'substr/ascii'], 3
FROM categories WHERE slug = 'sqli';

INSERT INTO labs (category_id, name, slug, difficulty, description, learning_objectives, docker_image, default_port, timeout_minutes, knowledge_points, sort_order)
SELECT id, 'SQL注入地狱：WAF绕过与堆叠注入', 'sqli-waf-bypass', 'hell',
    '有WAF防护的搜索接口，过滤了常见关键字。需要综合运用编码绕过、双写绕过、堆叠注入等技术。',
    ARRAY['掌握WAF绕过技术', '理解宽字节注入', '学会堆叠注入', '掌握带外数据传输'],
    'cyberrange/sqli-hell', 8084, 60,
    ARRAY['WAF绕过', '双写绕过', '大小写绕过', '宽字节注入', '堆叠注入', 'DNS外带'], 4
FROM categories WHERE slug = 'sqli';

-- Seed: XSS labs
INSERT INTO labs (category_id, name, slug, difficulty, description, learning_objectives, docker_image, default_port, timeout_minutes, knowledge_points, sort_order)
SELECT id, 'XSS入门：反射型XSS (GET)', 'xss-reflected-get', 'easy',
    '搜索框将用户输入直接反射到页面，没有任何过滤或编码。',
    ARRAY['理解XSS基本原理', '掌握反射型XSS', '学会弹窗验证'],
    'cyberrange/xss-easy', 8085, 60,
    ARRAY['反射型XSS', 'GET参数注入', 'script标签'], 1
FROM categories WHERE slug = 'xss';

INSERT INTO labs (category_id, name, slug, difficulty, description, learning_objectives, docker_image, default_port, timeout_minutes, knowledge_points, sort_order)
SELECT id, 'XSS进阶：存储型与DOM型', 'xss-stored-dom', 'simple',
    '留言板功能存在存储型XSS，前端JS存在DOM型XSS漏洞。',
    ARRAY['理解存储型XSS', '理解DOM型XSS', '学会Cookie窃取'],
    'cyberrange/xss-simple', 8086, 60,
    ARRAY['存储型XSS', 'DOM型XSS', 'Cookie窃取', '事件处理'], 2
FROM categories WHERE slug = 'xss';

INSERT INTO labs (category_id, name, slug, difficulty, description, learning_objectives, docker_image, default_port, timeout_minutes, knowledge_points, sort_order)
SELECT id, 'XSS高级：盲打与键盘记录', 'xss-blind', 'hard',
    'XSS盲打平台，注入的脚本在管理员后台执行。需要构造键盘记录器。',
    ARRAY['掌握XSS盲打', '学会构造键盘记录器', '理解CSP绕过基础'],
    'cyberrange/xss-hard', 8087, 60,
    ARRAY['XSS盲打', '键盘记录', 'Cookie发送', 'fetch请求'], 3
FROM categories WHERE slug = 'xss';

INSERT INTO labs (category_id, name, slug, difficulty, description, learning_objectives, docker_image, default_port, timeout_minutes, knowledge_points, sort_order)
SELECT id, 'XSS地狱：CSP绕过与钓鱼攻击', 'xss-csp-bypass', 'hell',
    '有CSP防护的页面，需要绕过内容安全策略。构造钓鱼页面窃取凭证。',
    ARRAY['理解CSP机制', '掌握CSP绕过技术', '学会构造钓鱼攻击'],
    'cyberrange/xss-hell', 8088, 60,
    ARRAY['CSP绕过', '钓鱼攻击', 'base-uri利用', 'JSONP利用'], 4
FROM categories WHERE slug = 'xss';

-- Seed: CSRF labs
INSERT INTO labs (category_id, name, slug, difficulty, description, learning_objectives, docker_image, default_port, timeout_minutes, knowledge_points, sort_order)
SELECT id, 'CSRF入门：GET型CSRF', 'csrf-get', 'easy',
    '修改密码功能使用GET请求，攻击者可以构造恶意链接诱骗用户点击。',
    ARRAY['理解CSRF基本原理', '掌握GET型CSRF利用', '学会构造POC'],
    'cyberrange/csrf-easy', 8089, 60,
    ARRAY['GET型CSRF', '恶意链接', 'POC构造'], 1
FROM categories WHERE slug = 'csrf';

INSERT INTO labs (category_id, name, slug, difficulty, description, learning_objectives, docker_image, default_port, timeout_minutes, knowledge_points, sort_order)
SELECT id, 'CSRF进阶：POST型CSRF', 'csrf-post', 'simple',
    '修改资料功能使用POST请求，需要构造自动提交的表单。',
    ARRAY['掌握POST型CSRF', '学会自动提交表单', '理解Referer检查'],
    'cyberrange/csrf-simple', 8090, 60,
    ARRAY['POST型CSRF', '自动表单', 'Referer绕过'], 2
FROM categories WHERE slug = 'csrf';

INSERT INTO labs (category_id, name, slug, difficulty, description, learning_objectives, docker_image, default_port, timeout_minutes, knowledge_points, sort_order)
SELECT id, 'CSRF高级：Token绕过', 'csrf-token-bypass', 'hard',
    '应用使用CSRF Token防护，但存在Token泄露或验证缺陷。',
    ARRAY['理解CSRF Token机制', '掌握Token绕过技巧', '学会利用SameSite缺陷'],
    'cyberrange/csrf-hard', 8091, 60,
    ARRAY['CSRF Token', 'Token泄露', 'SameSite绕过'], 3
FROM categories WHERE slug = 'csrf';

INSERT INTO labs (category_id, name, slug, difficulty, description, learning_objectives, docker_image, default_port, timeout_minutes, knowledge_points, sort_order)
SELECT id, 'CSRF地狱：组合攻击', 'csrf-combo', 'hell',
    'CSRF + XSS组合攻击，利用XSS绕过CSRF防护，构造完整的攻击链。',
    ARRAY['掌握CSRF+XSS组合攻击', '理解攻击链构造', '学会绕过多种防护'],
    'cyberrange/csrf-hell', 8092, 60,
    ARRAY['CSRF+XSS组合', '攻击链', '多层绕过'], 4
FROM categories WHERE slug = 'csrf';

-- Seed: RCE labs
INSERT INTO labs (category_id, name, slug, difficulty, description, learning_objectives, docker_image, default_port, timeout_minutes, knowledge_points, sort_order)
SELECT id, '命令执行入门：Ping功能', 'rce-ping', 'easy',
    'Ping工具页面，用户输入IP直接执行ping命令，没有过滤特殊字符。',
    ARRAY['理解命令注入原理', '掌握命令拼接技巧', '学会执行任意命令'],
    'cyberrange/rce-easy', 8093, 60,
    ARRAY['命令注入', '管道符', '分号拼接'], 1
FROM categories WHERE slug = 'rce';

INSERT INTO labs (category_id, name, slug, difficulty, description, learning_objectives, docker_image, default_port, timeout_minutes, knowledge_points, sort_order)
SELECT id, '命令执行进阶：绕过过滤', 'rce-bypass', 'simple',
    '过滤了部分危险字符，需要使用编码或特殊写法绕过。',
    ARRAY['掌握空格绕过', '学会关键字绕过', '理解命令编码'],
    'cyberrange/rce-simple', 8094, 60,
    ARRAY['空格绕过', '$IFS', 'base64编码', '通配符'], 2
FROM categories WHERE slug = 'rce';

INSERT INTO labs (category_id, name, slug, difficulty, description, learning_objectives, docker_image, default_port, timeout_minutes, knowledge_points, sort_order)
SELECT id, '命令执行高级：代码执行(eval)', 'rce-eval', 'hard',
    'PHP eval函数直接执行用户输入的代码，需要构造Webshell。',
    ARRAY['理解eval代码执行', '学会构造Webshell', '掌握反弹Shell'],
    'cyberrange/rce-hard', 8095, 60,
    ARRAY['eval执行', 'Webshell', '反弹Shell', 'system函数'], 3
FROM categories WHERE slug = 'rce';

INSERT INTO labs (category_id, name, slug, difficulty, description, learning_objectives, docker_image, default_port, timeout_minutes, knowledge_points, sort_order)
SELECT id, '命令执行地狱：综合利用', 'rce-exploit', 'hell',
    '多层防护的命令执行漏洞，需要结合信息收集、权限提升、持久化。',
    ARRAY['掌握多层绕过', '学会提权技术', '理解持久化后门'],
    'cyberrange/rce-hell', 8096, 60,
    ARRAY['多层绕过', 'SUID提权', 'crontab持久化', '隐藏后门'], 4
FROM categories WHERE slug = 'rce';

-- Seed: File Inclusion labs
INSERT INTO labs (category_id, name, slug, difficulty, description, learning_objectives, docker_image, default_port, timeout_minutes, knowledge_points, sort_order)
SELECT id, '文件包含入门：LFI本地包含', 'lfi-basic', 'easy',
    '页面包含功能直接使用用户输入的文件路径，可以读取任意本地文件。',
    ARRAY['理解文件包含原理', '掌握目录穿越', '学会读取敏感文件'],
    'cyberrange/fi-easy', 8097, 60,
    ARRAY['LFI', '目录穿越', '/etc/passwd'], 1
FROM categories WHERE slug = 'file-inclusion';

INSERT INTO labs (category_id, name, slug, difficulty, description, learning_objectives, docker_image, default_port, timeout_minutes, knowledge_points, sort_order)
SELECT id, '文件包含进阶：RFI远程包含', 'rfi-remote', 'simple',
    'allow_url_include开启，可以包含远程URL上的恶意文件。',
    ARRAY['理解RFI原理', '学会远程文件包含', '掌握伪协议利用'],
    'cyberrange/fi-simple', 8098, 60,
    ARRAY['RFI', '远程包含', 'php://filter', 'data://'], 2
FROM categories WHERE slug = 'file-inclusion';

INSERT INTO labs (category_id, name, slug, difficulty, description, learning_objectives, docker_image, default_port, timeout_minutes, knowledge_points, sort_order)
SELECT id, '文件包含高级：伪协议与日志投毒', 'lfi-advanced', 'hard',
    '过滤了部分关键字，需要使用PHP伪协议和日志投毒技术。',
    ARRAY['掌握PHP伪协议', '学会日志投毒', '理解Session文件包含'],
    'cyberrange/fi-hard', 8099, 60,
    ARRAY['php://filter', 'php://input', '日志投毒', 'Session包含'], 3
FROM categories WHERE slug = 'file-inclusion';

INSERT INTO labs (category_id, name, slug, difficulty, description, learning_objectives, docker_image, default_port, timeout_minutes, knowledge_points, sort_order)
SELECT id, '文件包含地狱：LFI到RCE完整链', 'lfi-to-rce', 'hell',
    '从文件包含漏洞出发，最终实现远程代码执行的完整攻击链。',
    ARRAY['掌握LFI到RCE的完整利用链', '学会PearCmd利用', '理解文件包含的终极利用'],
    'cyberrange/fi-hell', 8100, 60,
    ARRAY['LFI2RCE', 'PearCmd', 'LD_PRELOAD', '完整攻击链'], 4
FROM categories WHERE slug = 'file-inclusion';

-- Seed: File Upload labs
INSERT INTO labs (category_id, name, slug, difficulty, description, learning_objectives, docker_image, default_port, timeout_minutes, knowledge_points, sort_order)
SELECT id, '文件上传入门：客户端校验绕过', 'upload-client', 'easy',
    '上传功能仅在前端JavaScript校验文件类型，可以通过禁用JS或抓包绕过。',
    ARRAY['理解客户端校验弱点', '掌握抓包改包技巧', '学会上传Webshell'],
    'cyberrange/upload-easy', 8101, 60,
    ARRAY['客户端校验', '抓包绕过', 'Webshell上传'], 1
FROM categories WHERE slug = 'file-upload';

INSERT INTO labs (category_id, name, slug, difficulty, description, learning_objectives, docker_image, default_port, timeout_minutes, knowledge_points, sort_order)
SELECT id, '文件上传进阶：MIME与图片头绕过', 'upload-mime', 'simple',
    '服务端校验MIME类型和文件头，需要修改Content-Type和添加图片头。',
    ARRAY['理解MIME校验', '掌握文件头伪造', '学会图片马制作'],
    'cyberrange/upload-simple', 8102, 60,
    ARRAY['MIME绕过', 'Content-Type', 'GIF89a', '图片马'], 2
FROM categories WHERE slug = 'file-upload';

INSERT INTO labs (category_id, name, slug, difficulty, description, learning_objectives, docker_image, default_port, timeout_minutes, knowledge_points, sort_order)
SELECT id, '文件上传高级：二次渲染绕过', 'upload-rerender', 'hard',
    '上传的图片会被重新渲染，需要在渲染后仍保留恶意代码。',
    ARRAY['理解二次渲染机制', '掌握渲染绕过技巧', '学会.htaccess利用'],
    'cyberrange/upload-hard', 8103, 60,
    ARRAY['二次渲染', '.htaccess', 'user.ini', '竞争条件'], 3
FROM categories WHERE slug = 'file-upload';

INSERT INTO labs (category_id, name, slug, difficulty, description, learning_objectives, docker_image, default_port, timeout_minutes, knowledge_points, sort_order)
SELECT id, '文件上传地狱：综合Getshell', 'upload-getshell', 'hell',
    '多层防护的上传功能，结合文件包含实现最终Getshell。',
    ARRAY['掌握多层绕过', '学会文件包含组合利用', '理解完整Getshell链'],
    'cyberrange/upload-hell', 8104, 60,
    ARRAY['多层绕过', '文件包含组合', 'Getshell', '完整攻击链'], 4
FROM categories WHERE slug = 'file-upload';

-- Seed: Privilege labs
INSERT INTO labs (category_id, name, slug, difficulty, description, learning_objectives, docker_image, default_port, timeout_minutes, knowledge_points, sort_order)
SELECT id, '越权入门：水平越权', 'priv-horizontal', 'easy',
    '查看个人信息时修改ID参数可以查看其他用户的数据。',
    ARRAY['理解水平越权原理', '掌握IDOR漏洞', '学会参数篡改'],
    'cyberrange/priv-easy', 8105, 60,
    ARRAY['水平越权', 'IDOR', '参数篡改'], 1
FROM categories WHERE slug = 'privilege';

INSERT INTO labs (category_id, name, slug, difficulty, description, learning_objectives, docker_image, default_port, timeout_minutes, knowledge_points, sort_order)
SELECT id, '越权进阶：垂直越权', 'priv-vertical', 'simple',
    '普通用户可以通过修改请求直接访问管理后台功能。',
    ARRAY['理解垂直越权原理', '掌握权限提升技巧', '学会未授权访问'],
    'cyberrange/priv-simple', 8106, 60,
    ARRAY['垂直越权', '权限提升', '管理后台'], 2
FROM categories WHERE slug = 'privilege';

INSERT INTO labs (category_id, name, slug, difficulty, description, learning_objectives, docker_image, default_port, timeout_minutes, knowledge_points, sort_order)
SELECT id, '越权高级：IDOR与权限提升', 'priv-idor', 'hard',
    'API接口存在IDOR漏洞，结合JWT伪造实现完整权限提升。',
    ARRAY['掌握API级IDOR', '学会JWT伪造', '理解权限模型缺陷'],
    'cyberrange/priv-hard', 8107, 60,
    ARRAY['API IDOR', 'JWT伪造', 'RS256→HS256', '权限模型'], 3
FROM categories WHERE slug = 'privilege';

INSERT INTO labs (category_id, name, slug, difficulty, description, learning_objectives, docker_image, default_port, timeout_minutes, knowledge_points, sort_order)
SELECT id, '越权地狱：越权链与未授权访问', 'priv-chain', 'hell',
    '从信息收集到水平越权再到垂直越权，构造完整的权限攻击链。',
    ARRAY['掌握完整越权攻击链', '学会多步权限提升', '理解企业级权限漏洞'],
    'cyberrange/priv-hell', 8108, 60,
    ARRAY['越权攻击链', '多步提权', 'RBAC绕过', '企业级漏洞'], 4
FROM categories WHERE slug = 'privilege';

-- Seed: Other vulnerabilities labs
INSERT INTO labs (category_id, name, slug, difficulty, description, learning_objectives, docker_image, default_port, timeout_minutes, knowledge_points, sort_order)
SELECT id, '目录遍历与信息泄露', 'other-dir-traversal', 'easy',
    '文件下载功能存在目录遍历，配置文件泄露敏感信息。',
    ARRAY['理解目录遍历', '学会../穿越', '掌握信息收集'],
    'cyberrange/other-easy', 8109, 60,
    ARRAY['目录遍历', '../穿越', '信息泄露', '.git泄露'], 1
FROM categories WHERE slug = 'other';

INSERT INTO labs (category_id, name, slug, difficulty, description, learning_objectives, docker_image, default_port, timeout_minutes, knowledge_points, sort_order)
SELECT id, 'PHP反序列化漏洞', 'other-deserialization', 'simple',
    'PHP unserialize函数处理用户输入，构造恶意对象链实现代码执行。',
    ARRAY['理解PHP反序列化', '掌握魔术方法', '学会POP链构造'],
    'cyberrange/other-simple', 8110, 60,
    ARRAY['反序列化', 'unserialize', '魔术方法', 'POP链'], 2
FROM categories WHERE slug = 'other';

INSERT INTO labs (category_id, name, slug, difficulty, description, learning_objectives, docker_image, default_port, timeout_minutes, knowledge_points, sort_order)
SELECT id, 'XXE注入漏洞', 'other-xxe', 'hard',
    'XML解析功能未禁用外部实体，可以读取服务器文件或发起SSRF。',
    ARRAY['理解XXE原理', '掌握外部实体注入', '学会XXE+SSRF组合'],
    'cyberrange/other-hard', 8111, 60,
    ARRAY['XXE', '外部实体', 'SYSTEM实体', '盲注XXE'], 3
FROM categories WHERE slug = 'other';

INSERT INTO labs (category_id, name, slug, difficulty, description, learning_objectives, docker_image, default_port, timeout_minutes, knowledge_points, sort_order)
SELECT id, 'SSRF与URL重定向综合利用', 'other-ssrf-redirect', 'hell',
    'SSRF漏洞结合URL重定向，可以访问内网服务并构造钓鱼跳转。',
    ARRAY['掌握SSRF高级利用', '学会协议利用', '理解URL重定向钓鱼'],
    'cyberrange/other-hell', 8112, 60,
    ARRAY['SSRF', 'gopher协议', 'file协议', 'URL重定向', '钓鱼'], 4
FROM categories WHERE slug = 'other';

-- Create indexes
CREATE INDEX idx_labs_category ON labs(category_id);
CREATE INDEX idx_labs_difficulty ON labs(difficulty);
CREATE INDEX idx_lessons_category ON lessons(category_id);
CREATE INDEX idx_lessons_lab ON lessons(lab_id);
CREATE INDEX idx_progress_user ON user_progress(user_id);
CREATE INDEX idx_progress_lab ON user_progress(lab_id);
CREATE INDEX idx_traffic_user ON traffic_records(user_id);
CREATE INDEX idx_traffic_lab ON traffic_records(lab_id);
CREATE INDEX idx_ai_sessions_user ON ai_sessions(user_id);
