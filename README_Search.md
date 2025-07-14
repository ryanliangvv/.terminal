# 高级搜索逻辑实现

本项目实现了一个支持多种搜索语法的PHP搜索解析器，支持空格分隔、双引号精确匹配和减号排除等高级搜索功能。

## 功能特性

### 1. 空格分隔的多关键词搜索（AND逻辑）
- **语法**：`关键词A 关键词B`
- **功能**：搜索同时包含所有关键词的内容
- **示例**：`苹果 香蕉` → 搜索同时包含"苹果"和"香蕉"的内容

### 2. 双引号精确短语匹配
- **语法**：`"精确短语"`
- **功能**：搜索包含完整短语的内容
- **示例**：`"苹果 香蕉"` → 精确搜索包含"苹果 香蕉"这个完整短语的内容

### 3. 减号排除关键词
- **语法**：`关键词A -关键词B`
- **功能**：搜索包含关键词A但不包含关键词B的内容
- **示例**：`苹果 -香蕉` → 搜索包含"苹果"但不包含"香蕉"的内容

### 4. 组合搜索
- **语法**：支持以上功能的任意组合
- **示例**：`"新鲜水果" 苹果 梨子 -腐烂` → 搜索包含"新鲜水果"短语和"苹果"、"梨子"关键词，但排除"腐烂"的内容

## 文件结构

```
/workspace/
├── AdvancedSearchParser.php    # 核心搜索解析器类
├── search_examples.php         # 使用示例和演示
└── README_Search.md           # 本文档
```

## 核心类说明

### AdvancedSearchParser
主要的搜索解析器类，提供以下方法：

- `parseSearchString($searchString)` - 解析搜索字符串并生成SQL WHERE条件
- `buildSelectQuery($searchString, $selectColumns, $orderBy, $limit)` - 生成完整的SELECT查询
- `getDebugInfo($searchString)` - 获取解析调试信息

### SearchHelper
提供便捷的静态方法：

- `quickSearch($searchString, $tableName, $searchColumn)` - 快速搜索
- `getWhereCondition($searchString, $searchColumn)` - 获取WHERE条件

## 使用示例

### 基本使用

```php
<?php
require_once 'AdvancedSearchParser.php';

// 创建搜索解析器
$parser = new AdvancedSearchParser('articles', 'content');

// 解析搜索字符串
$searchString = '苹果 香蕉 -腐烂';
$result = $parser->parseSearchString($searchString);

echo $result['sql'];     // SQL WHERE条件
print_r($result['params']); // 参数数组
?>
```

### PDO数据库查询

```php
<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=your_database', $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 创建搜索解析器
    $parser = new AdvancedSearchParser('articles', 'content');

    // 用户输入的搜索字符串
    $userSearch = '苹果 香蕉 -腐烂';

    // 生成查询
    $queryResult = $parser->buildSelectQuery($userSearch, ['id', 'title', 'content'], 'created_at DESC', 10);

    // 执行查询
    $stmt = $pdo->prepare($queryResult['sql']);
    $stmt->execute($queryResult['params']);

    // 获取结果
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $row) {
        echo "标题: " . $row['title'] . "\n";
        echo "内容: " . $row['content'] . "\n\n";
    }

} catch (PDOException $e) {
    echo "数据库错误: " . $e->getMessage();
}
?>
```

### 静态方法快速使用

```php
<?php
// 快速搜索
$result = SearchHelper::quickSearch('苹果 香蕉', 'products', 'name');

// 只获取WHERE条件
$whereCondition = SearchHelper::getWhereCondition('"新鲜水果"', 'description');
?>
```

## 生成的SQL示例

### 场景1：空格分隔搜索
**输入**：`苹果 香蕉`

**生成SQL**：
```sql
SELECT * FROM articles WHERE (content LIKE :include_0 AND content LIKE :include_1)
```

**参数**：
```php
[
    ':include_0' => '%苹果%',
    ':include_1' => '%香蕉%'
]
```

### 场景2：精确短语匹配
**输入**：`"苹果 香蕉"`

**生成SQL**：
```sql
SELECT * FROM articles WHERE (content LIKE :phrase_0)
```

**参数**：
```php
[
    ':phrase_0' => '%苹果 香蕉%'
]
```

### 场景3：排除关键词
**输入**：`苹果 -香蕉`

**生成SQL**：
```sql
SELECT * FROM articles WHERE (content LIKE :include_0 AND content NOT LIKE :exclude_1)
```

**参数**：
```php
[
    ':include_0' => '%苹果%',
    ':exclude_1' => '%香蕉%'
]
```

### 场景4：复合搜索
**输入**：`"新鲜水果" 苹果 梨子 -腐烂`

**生成SQL**：
```sql
SELECT * FROM articles WHERE (content LIKE :phrase_0 AND content LIKE :include_1 AND content LIKE :include_2 AND content NOT LIKE :exclude_3)
```

**参数**：
```php
[
    ':phrase_0' => '%新鲜水果%',
    ':include_1' => '%苹果%',
    ':include_2' => '%梨子%',
    ':exclude_3' => '%腐烂%'
]
```

## 运行示例

```bash
# 运行完整示例演示
php search_examples.php
```

## 安全特性

1. **SQL注入防护**：使用PDO预处理语句和参数绑定
2. **参数化查询**：所有用户输入都通过参数绑定传递
3. **输入验证**：对搜索字符串进行适当的解析和验证

## 扩展功能

### MySQL全文搜索支持
如果需要更好的搜索性能，可以结合MySQL的全文搜索功能：

```php
class FullTextSearchParser extends AdvancedSearchParser
{
    protected function buildSqlConditions($conditions)
    {
        // 如果只有包含词且没有排除词和精确短语，使用全文搜索
        if (!empty($conditions['include_words']) && 
            empty($conditions['exclude_words']) && 
            empty($conditions['include_phrases'])) {

            $keywords = implode(' ', $conditions['include_words']);
            $paramName = 'fulltext_0';
            $sql = "MATCH({$this->searchColumn}) AGAINST(:{$paramName} IN BOOLEAN MODE)";
            $params = [$paramName => '+' . str_replace(' ', ' +', $keywords)];
            
            return ['sql' => $sql, 'params' => $params];
        } else {
            // 使用父类的LIKE搜索方法
            return parent::buildSqlConditions($conditions);
        }
    }
}
```

## 优缺点分析

### 优点
- **低学习门槛**：语法简单直观，用户容易理解
- **功能丰富**：支持精确匹配、排除搜索、组合搜索
- **安全可靠**：防SQL注入，使用参数化查询
- **易于集成**：可以轻松集成到现有项目中
- **灵活可扩展**：支持自定义表名、列名和查询条件

### 缺点
- **性能考虑**：对于大数据量，LIKE查询可能性能较差
- **空格歧义**：搜索内容本身包含空格时可能产生歧义
- **功能限制**：相比专业搜索引擎功能较为基础

## 适用场景

- 文章内容搜索
- 产品信息搜索
- 用户评论搜索
- 日志信息搜索
- 中小型网站搜索功能

## 参考资料

- [谷歌高级搜索符](https://blog.google/products/search/how-were-improving-search-results-when-you-use-quotes/)
- [谷歌搜索运算符支持列表](https://support.google.com/websearch/answer/2466433?hl=zh-Hans)
- [搜索功能设计参考](https://zhuanlan.zhihu.com/p/140063449)