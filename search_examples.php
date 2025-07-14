<?php

require_once 'AdvancedSearchParser.php';

/**
 * 高级搜索功能使用示例
 * 演示各种搜索场景和生成的SQL语句
 */

echo "=== 高级搜索功能演示 ===\n\n";

// 创建搜索解析器实例
$parser = new AdvancedSearchParser('articles', 'content');

// 测试用例
$testCases = [
    // 场景1：空格分隔的多关键词搜索
    [
        'description' => '场景1：空格分隔的多关键词搜索',
        'input' => '苹果 香蕉',
        'explanation' => '搜索同时包含"苹果"和"香蕉"的内容'
    ],
    
    // 场景2：双引号精确短语匹配
    [
        'description' => '场景2：双引号精确短语匹配',
        'input' => '"苹果 香蕉"',
        'explanation' => '精确搜索包含"苹果 香蕉"这个完整短语的内容'
    ],
    
    // 场景3：减号排除关键词
    [
        'description' => '场景3：减号排除关键词',
        'input' => '苹果 -香蕉',
        'explanation' => '搜索包含"苹果"但不包含"香蕉"的内容'
    ],
    
    // 场景4：复合搜索（组合使用）
    [
        'description' => '场景4：复合搜索（组合使用）',
        'input' => '"新鲜水果" 苹果 梨子 -腐烂',
        'explanation' => '搜索包含"新鲜水果"短语和"苹果"、"梨子"关键词，但排除"腐烂"的内容'
    ],
    
    // 场景5：多个排除词
    [
        'description' => '场景5：多个排除词',
        'input' => '水果 -苹果 -香蕉 -橘子',
        'explanation' => '搜索包含"水果"但排除"苹果"、"香蕉"、"橘子"的内容'
    ],
    
    // 场景6：多个精确短语
    [
        'description' => '场景6：多个精确短语',
        'input' => '"红苹果" "黄香蕉" 水果',
        'explanation' => '搜索同时包含"红苹果"和"黄香蕉"精确短语以及"水果"关键词的内容'
    ]
];

// 执行测试用例
foreach ($testCases as $index => $testCase) {
    echo "----------------------------------------\n";
    echo $testCase['description'] . "\n";
    echo "输入：" . $testCase['input'] . "\n";
    echo "说明：" . $testCase['explanation'] . "\n\n";
    
    // 生成SQL查询
    $queryResult = $parser->buildSelectQuery($testCase['input']);
    echo "生成的SQL语句：\n";
    echo $queryResult['sql'] . "\n\n";
    
    if (!empty($queryResult['params'])) {
        echo "参数绑定：\n";
        foreach ($queryResult['params'] as $param => $value) {
            echo "  :{$param} => '{$value}'\n";
        }
        echo "\n";
    }
    
    // 显示调试信息
    $debugInfo = $parser->getDebugInfo($testCase['input']);
    echo "解析详情：\n";
    echo "  精确短语：" . json_encode($debugInfo['parsed_conditions']['include_phrases'], JSON_UNESCAPED_UNICODE) . "\n";
    echo "  包含关键词：" . json_encode($debugInfo['parsed_conditions']['include_words'], JSON_UNESCAPED_UNICODE) . "\n";
    echo "  排除关键词：" . json_encode($debugInfo['parsed_conditions']['exclude_words'], JSON_UNESCAPED_UNICODE) . "\n\n";
}

echo "=== 使用SearchHelper静态方法的快速示例 ===\n\n";

// 使用SearchHelper的快速方法
$quickSearchResults = [
    '苹果 香蕉' => SearchHelper::quickSearch('苹果 香蕉', 'products', 'name'),
    '"新鲜水果"' => SearchHelper::getWhereCondition('"新鲜水果"', 'description')
];

foreach ($quickSearchResults as $search => $result) {
    echo "搜索：{$search}\n";
    echo "SQL：" . $result['sql'] . "\n";
    if (!empty($result['params'])) {
        echo "参数：" . json_encode($result['params'], JSON_UNESCAPED_UNICODE) . "\n";
    }
    echo "\n";
}

echo "=== PDO使用示例 ===\n\n";

/**
 * PDO使用示例代码
 */
echo "<?php\n";
echo "// PDO使用示例\n";
echo "try {\n";
echo "    \$pdo = new PDO('mysql:host=localhost;dbname=your_database', \$username, \$password);\n";
echo "    \$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);\n\n";
echo "    // 创建搜索解析器\n";
echo "    \$parser = new AdvancedSearchParser('articles', 'content');\n\n";
echo "    // 用户输入的搜索字符串\n";
echo "    \$userSearch = '苹果 香蕉 -腐烂';\n\n";
echo "    // 生成查询\n";
echo "    \$queryResult = \$parser->buildSelectQuery(\$userSearch, ['id', 'title', 'content'], 'created_at DESC', 10);\n\n";
echo "    // 执行查询\n";
echo "    \$stmt = \$pdo->prepare(\$queryResult['sql']);\n";
echo "    \$stmt->execute(\$queryResult['params']);\n\n";
echo "    // 获取结果\n";
echo "    \$results = \$stmt->fetchAll(PDO::FETCH_ASSOC);\n\n";
echo "    foreach (\$results as \$row) {\n";
echo "        echo \"标题: \" . \$row['title'] . \"\\n\";\n";
echo "        echo \"内容: \" . \$row['content'] . \"\\n\\n\";\n";
echo "    }\n\n";
echo "} catch (PDOException \$e) {\n";
echo "    echo \"数据库错误: \" . \$e->getMessage();\n";
echo "}\n";
echo "?>\n\n";

echo "=== MySQL全文搜索增强版示例 ===\n\n";

/**
 * 如果需要更好的搜索性能，可以结合MySQL的全文搜索功能
 */
echo "<?php\n";
echo "// 结合MySQL全文搜索的增强版\n";
echo "class FullTextSearchParser extends AdvancedSearchParser\n";
echo "{\n";
echo "    protected function buildSqlConditions(\$conditions)\n";
echo "    {\n";
echo "        \$sqlParts = [];\n";
echo "        \$params = [];\n";
echo "        \$paramIndex = 0;\n\n";
echo "        // 如果只有包含词且没有排除词和精确短语，使用全文搜索\n";
echo "        if (!empty(\$conditions['include_words']) && \n";
echo "            empty(\$conditions['exclude_words']) && \n";
echo "            empty(\$conditions['include_phrases'])) {\n\n";
echo "            \$keywords = implode(' ', \$conditions['include_words']);\n";
echo "            \$paramName = 'fulltext_' . \$paramIndex++;\n";
echo "            \$sqlParts[] = \"MATCH({\$this->searchColumn}) AGAINST(:{\$paramName} IN BOOLEAN MODE)\";\n";
echo "            \$params[\$paramName] = '+' . str_replace(' ', ' +', \$keywords);\n";
echo "        } else {\n";
echo "            // 使用父类的LIKE搜索方法\n";
echo "            return parent::buildSqlConditions(\$conditions);\n";
echo "        }\n\n";
echo "        \$sql = empty(\$sqlParts) ? '' : '(' . implode(' AND ', \$sqlParts) . ')';\n";
echo "        return ['sql' => \$sql, 'params' => \$params];\n";
echo "    }\n";
echo "}\n";
echo "?>\n";

?>