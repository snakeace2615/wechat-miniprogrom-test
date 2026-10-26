# 项目说明

- 本项目是微信小程序的测试后端，使用 PHP 8.5。
- 项目不在本地运行，实际运行环境是 VPS。本地仅进行代码编辑和静态检查。
- 不要在本地启动任何服务，包括 PHP 开发服务器、Web 服务、数据库或容器服务。

## 部署方式

- 通过 GitHub Actions 自动部署到 VPS，工作流见 `.github/workflows/deploy.yml`。
- 推送到 `main` 分支会触发自动部署，也支持 `workflow_dispatch` 手动触发。
- GitHub Actions 安装依赖、检查 PHP 语法后，使用 Deployer 部署；配置见 `deploy.php`。
- VPS 部署目录为 `/var/www/miniapp-api`，Web 根目录为 `/var/www/miniapp-api/current/public`。
- 部署后的健康检查由工作流通过 SSH 在 VPS 上请求 `http://127.0.0.1:8080/health` 完成。
- 部署行为以实际工作流和 `deploy.php` 为准。
- 不要修改、重启或占用现有 sing-box REALITY 的 TCP 443 服务；API 的 Nginx 仅监听 `127.0.0.1:8080`。

## 开发约束

- 只做完成当前任务所需的最小修改，不重构、重命名、格式化或清理无关代码。
- 添加代码前先搜索现有类似实现，遵循项目已有的目录结构、代码风格和接口约定。
- 优先复用已有实现，保持逻辑直接，不为简单需求引入不必要的封装、单次使用的辅助函数或抽象。
- 必填字段不添加默认值，不兼容未经要求的多种字段名，不通过吞掉异常或伪造数据继续执行。
- 用户密码仅保存安全哈希；不得保存明文密码或将密码写入日志。
- 不提交生产环境文件、SSH 私钥、数据库凭据或其他敏感信息。
- 持久化数据不得放入会被部署替换的 release 目录，应使用 VPS 共享目录；涉及存储时同步检查 Deployer 配置。
- 修改依赖时更新并提交 `composer.lock`。

## 完成前检查

- 检查 `git diff`，确认没有无关改动，并移除不必要的默认值、封装和重复逻辑。
- 运行 `composer check`，执行项目现有 PHP 语法检查；如修改其他 PHP 文件，也需对修改文件执行 `php -l`。
- 修改 Composer 配置时，运行 `composer validate --strict --no-check-publish`。
- 不得声称未执行的检查已通过；本地静态检查通过不代表 VPS 已部署成功。
- 需要运行环境的验证在 GitHub Actions 或 VPS 上完成，不为验证而启动本地服务。
