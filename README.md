# 游梦体育 后端 API

基于 ThinkPHP 6.1 开发的 RESTful API 服务。

## 环境要求

- PHP >= 8.0
- MySQL >= 5.7
- Composer
- Nginx 或 Apache

## 快速开始

1. 安装依赖

```bash
composer install
```

2. 配置环境变量

复制 `.example.env` 为 `.env`，填写数据库配置：

```env
[DATABASE]
TYPE = mysql
HOSTNAME = 127.0.0.1
DATABASE = sports
USERNAME = 你的用户名
PASSWORD = 你的密码
HOSTPORT = 3306
CHARSET = utf8mb4
```

3. 导入数据库

```bash
mysql -u root -p sports < database/sports.sql
```

4. 配置网站根目录指向 `public/` 目录

## API 说明

所有接口除登录外均需在请求头携带 Token：
Authorization: Bearer <token>


| 模块 | 接口 |
|------|------|
| 登录 | POST /api/login |
| 比赛 | GET/POST /api/matches |
| 球队 | GET/POST /api/teams |
| 球员 | GET/POST /api/players |
| 用户 | GET/POST /api/users |
| 数据采集 | GET /api/DataCrawler/matches |