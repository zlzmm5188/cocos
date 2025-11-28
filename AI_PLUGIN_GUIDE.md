# 🔌 Providence AI 插件系统使用指南

## 📋 目录

- [系统介绍](#系统介绍)
- [快速开始](#快速开始)
- [插件开发](#插件开发)
- [内置插件](#内置插件)
- [插件API](#插件api)
- [示例](#示例)

---

## 🎯 系统介绍

Providence AI 插件系统允许您扩展AI功能，无需修改核心代码。

### 核心特性

- ✅ **钩子机制** - 在关键节点插入自定义逻辑
- ✅ **热插拔** - 动态加载/卸载插件
- ✅ **隔离运行** - 插件错误不影响主系统
- ✅ **简单易用** - 只需几行代码即可创建插件

### 支持的钩子

| 钩子名称 | 触发时机 | 参数 |
|---------|---------|------|
| `beforeProcess` | AI处理消息前 | `(userMessage, userData)` |
| `afterProcess` | AI处理消息后 | `(response, userMessage, userData)` |
| `intentDetected` | 意图识别后 | `(intent, userMessage)` |
| `responseGenerated` | 响应生成后 | `(response, userMessage, userData)` |

---

## 🚀 快速开始

### 1. 引入插件系统

在 `messages.html` 中，在AI系统加载后引入：

```html
<!-- AI核心系统 -->
<script src="ai-config.js"></script>
<script src="ai-hybrid-dispatcher.js"></script>
<!-- ... 其他AI模块 ... -->

<!-- 插件系统 -->
<script src="ai-plugin-system.js"></script>

<!-- 内置插件（可选） -->
<script src="ai-plugins/weather-plugin.js"></script>
<script src="ai-plugins/calculator-plugin.js"></script>
<script src="ai-plugins/emoji-plugin.js"></script>
```

### 2. 使用插件

插件会自动注册并生效，无需额外配置！

---

## 🛠️ 插件开发

### 插件结构

```javascript
const MyPlugin = {
    name: 'my-plugin',              // 插件名称（唯一）
    version: '1.0.0',                // 版本号
    description: '插件描述',         // 描述

    // 初始化方法（可选）
    init(pluginSystem) {
        console.log('插件初始化');
    },

    // 清理方法（可选）
    destroy() {
        console.log('插件清理');
    },

    // 钩子处理函数
    hooks: {
        // 处理前钩子
        beforeProcess: async (userMessage, userData) => {
            // 可以修改 userMessage
            return userMessage;
        },

        // 响应生成后钩子
        responseGenerated: async (response, userMessage, userData) => {
            // 可以修改 response
            return response;
        },

        // 意图识别钩子
        intentDetected: async (intent, userMessage) => {
            // 可以修改 intent
            return intent;
        }
    }
};

// 注册插件
if (window.AI_PLUGIN_SYSTEM) {
    window.AI_PLUGIN_SYSTEM.register('my-plugin', MyPlugin);
}
```

### 完整示例：翻译插件

```javascript
const TranslationPlugin = {
    name: 'translation',
    version: '1.0.0',
    description: '翻译插件 - 支持中英文翻译',

    init(pluginSystem) {
        console.log('[翻译插件] ✅ 初始化完成');
    },

    hooks: {
        responseGenerated: async (response, userMessage, userData) => {
            // 如果用户要求翻译
            if (userMessage.match(/翻译|translate/i)) {
                const text = this.extractText(userMessage);
                const translation = await this.translate(text);

                return {
                    ...response,
                    message: `🌐 <strong>翻译结果</strong><br><br>` +
                           `原文：${text}<br>` +
                           `译文：${translation}`,
                    source: 'translation_plugin'
                };
            }

            return response;
        }
    },

    extractText(message) {
        // 提取要翻译的文本
        const match = message.match(/翻译[：:]\s*(.+)/i);
        return match ? match[1] : '';
    },

    async translate(text) {
        // 这里可以调用翻译API
        // 例如：Google Translate API, 百度翻译API等
        return '翻译结果（示例）';
    }
};

// 注册
if (window.AI_PLUGIN_SYSTEM) {
    window.AI_PLUGIN_SYSTEM.register('translation', TranslationPlugin);
}
```

---

## 📦 内置插件

### 1. 天气查询插件 (`weather-plugin.js`)

**功能**：让AI可以查询天气信息

**使用**：
```
用户：北京天气怎么样？
AI：🌤️ 北京天气
     温度：15°C
     天气：多云
     ...
```

### 2. 计算器插件 (`calculator-plugin.js`)

**功能**：让AI可以执行数学计算

**使用**：
```
用户：计算 123 + 456
AI：🧮 计算结果
     表达式：123+456
     结果：579
```

### 3. 表情符号增强插件 (`emoji-plugin.js`)

**功能**：自动为AI回复添加合适的表情符号

**使用**：自动生效，无需手动调用

---

## 🔧 插件API

### 注册插件

```javascript
AI_PLUGIN_SYSTEM.register(name, plugin)
```

### 卸载插件

```javascript
AI_PLUGIN_SYSTEM.unregister(name)
```

### 启用/禁用插件

```javascript
AI_PLUGIN_SYSTEM.toggle(name, enabled)  // enabled: true/false
```

### 获取所有插件

```javascript
const plugins = AI_PLUGIN_SYSTEM.getPlugins();
// 返回: [{name, enabled, version, description}, ...]
```

### 获取插件信息

```javascript
const plugin = AI_PLUGIN_SYSTEM.getPlugin(name);
```

---

## 💡 最佳实践

1. **插件命名**：使用小写字母和连字符，如 `my-plugin`
2. **错误处理**：在钩子函数中使用 try-catch
3. **性能优化**：避免在钩子中执行耗时操作
4. **兼容性**：确保插件不影响其他插件
5. **文档**：为插件编写清晰的注释和文档

---

## 🎨 插件示例集合

### 示例1：时间查询插件

```javascript
const TimePlugin = {
    name: 'time',
    version: '1.0.0',
    hooks: {
        responseGenerated: async (response, userMessage) => {
            if (userMessage.match(/现在几点|当前时间|time/i)) {
                const now = new Date();
                return {
                    ...response,
                    message: `🕐 <strong>当前时间</strong><br><br>` +
                           `北京时间：${now.toLocaleString('zh-CN')}<br>` +
                           `星期：${['日','一','二','三','四','五','六'][now.getDay()]}`
                };
            }
            return response;
        }
    }
};
```

### 示例2：股票查询插件

```javascript
const StockPlugin = {
    name: 'stock',
    version: '1.0.0',
    hooks: {
        intentDetected: async (intent, userMessage) => {
            if (userMessage.match(/股票|股价|行情/i)) {
                return {
                    type: 'stock_query',
                    confidence: 0.9,
                    entities: { symbol: this.extractStockCode(userMessage) }
                };
            }
            return intent;
        },
        responseGenerated: async (response, userMessage) => {
            if (userMessage.match(/股票|股价/i)) {
                const code = this.extractStockCode(userMessage);
                const price = await this.getStockPrice(code);
                return {
                    ...response,
                    message: `📈 <strong>${code} 股价</strong><br><br>当前价格：¥${price}`
                };
            }
            return response;
        }
    },
    extractStockCode(message) {
        const match = message.match(/([0-9]{6})/);
        return match ? match[1] : '';
    },
    async getStockPrice(code) {
        // 调用股票API
        return '100.00';
    }
};
```

---

## 📝 总结

插件系统让AI功能扩展变得简单：

- ✅ **无需修改核心代码**
- ✅ **热插拔，随时启用/禁用**
- ✅ **钩子机制，灵活扩展**
- ✅ **内置插件，开箱即用**

开始创建您的第一个插件吧！🚀
