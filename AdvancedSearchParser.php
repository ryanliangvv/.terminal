<?php

/**
 * 高级搜索解析器
 * 支持以下搜索语法：
 * 1. 空格分隔的多关键词搜索（AND逻辑）
 * 2. 双引号精确短语匹配
 * 3. 减号排除关键词
 */
class AdvancedSearchParser
{
    private $searchColumn;
    private $tableName;
    
    public function __construct($tableName = 'articles', $searchColumn = 'content')
    {
        $this->tableName = $tableName;
        $this->searchColumn = $searchColumn;
    }
    
    /**
     * 解析搜索字符串并生成SQL WHERE条件
     * 
     * @param string $searchString 用户输入的搜索字符串
     * @return array ['sql' => SQL条件字符串, 'params' => 参数数组]
     */
    public function parseSearchString($searchString)
    {
        if (empty(trim($searchString))) {
            return ['sql' => '', 'params' => []];
        }
        
        // 解析搜索条件
        $conditions = $this->extractSearchConditions($searchString);
        
        // 生成SQL条件
        return $this->buildSqlConditions($conditions);
    }
    
    /**
     * 提取搜索条件
     * 
     * @param string $searchString
     * @return array
     */
    private function extractSearchConditions($searchString)
    {
        $conditions = [
            'include_phrases' => [],  // 双引号精确匹配的短语
            'include_words' => [],    // 必须包含的关键词
            'exclude_words' => []     // 排除的关键词
        ];
        
        // 先提取双引号内的精确短语
        $searchString = $this->extractQuotedPhrases($searchString, $conditions);
        
        // 然后处理剩余的关键词和排除词
        $this->extractWordsAndExclusions($searchString, $conditions);
        
        return $conditions;
    }
    
    /**
     * 提取双引号内的精确短语
     * 
     * @param string $searchString
     * @param array &$conditions
     * @return string 移除双引号短语后的字符串
     */
    private function extractQuotedPhrases($searchString, &$conditions)
    {
        // 匹配双引号内的内容
        $pattern = '/"([^"]+)"/';
        preg_match_all($pattern, $searchString, $matches);
        
        if (!empty($matches[1])) {
            $conditions['include_phrases'] = $matches[1];
            // 从原字符串中移除已匹配的双引号短语
            $searchString = preg_replace($pattern, '', $searchString);
        }
        
        return $searchString;
    }
    
    /**
     * 提取关键词和排除词
     * 
     * @param string $searchString
     * @param array &$conditions
     */
    private function extractWordsAndExclusions($searchString, &$conditions)
    {
        // 分割字符串为单词
        $words = preg_split('/\s+/', trim($searchString), -1, PREG_SPLIT_NO_EMPTY);
        
        foreach ($words as $word) {
            if (strpos($word, '-') === 0 && strlen($word) > 1) {
                // 排除词（以减号开头）
                $conditions['exclude_words'][] = substr($word, 1);
            } else {
                // 包含词
                $conditions['include_words'][] = $word;
            }
        }
    }
    
    /**
     * 构建SQL条件
     * 
     * @param array $conditions
     * @return array
     */
    private function buildSqlConditions($conditions)
    {
        $sqlParts = [];
        $params = [];
        $paramIndex = 0;
        
        // 处理精确短语匹配
        foreach ($conditions['include_phrases'] as $phrase) {
            $paramName = 'phrase_' . $paramIndex++;
            $sqlParts[] = "{$this->searchColumn} LIKE :{$paramName}";
            $params[$paramName] = '%' . $phrase . '%';
        }
        
        // 处理包含的关键词
        foreach ($conditions['include_words'] as $word) {
            $paramName = 'include_' . $paramIndex++;
            $sqlParts[] = "{$this->searchColumn} LIKE :{$paramName}";
            $params[$paramName] = '%' . $word . '%';
        }
        
        // 处理排除的关键词
        foreach ($conditions['exclude_words'] as $word) {
            $paramName = 'exclude_' . $paramIndex++;
            $sqlParts[] = "{$this->searchColumn} NOT LIKE :{$paramName}";
            $params[$paramName] = '%' . $word . '%';
        }
        
        $sql = empty($sqlParts) ? '' : '(' . implode(' AND ', $sqlParts) . ')';
        
        return ['sql' => $sql, 'params' => $params];
    }
    
    /**
     * 生成完整的SELECT查询语句
     * 
     * @param string $searchString 搜索字符串
     * @param array $selectColumns 要查询的列，默认为['*']
     * @param string $orderBy 排序条件，默认为''
     * @param int $limit 限制条数，默认为0（不限制）
     * @return array
     */
    public function buildSelectQuery($searchString, $selectColumns = ['*'], $orderBy = '', $limit = 0)
    {
        $searchResult = $this->parseSearchString($searchString);
        
        $sql = "SELECT " . implode(', ', $selectColumns) . " FROM {$this->tableName}";
        
        if (!empty($searchResult['sql'])) {
            $sql .= " WHERE " . $searchResult['sql'];
        }
        
        if (!empty($orderBy)) {
            $sql .= " ORDER BY " . $orderBy;
        }
        
        if ($limit > 0) {
            $sql .= " LIMIT " . intval($limit);
        }
        
        return [
            'sql' => $sql,
            'params' => $searchResult['params']
        ];
    }
    
    /**
     * 设置搜索的表名
     * 
     * @param string $tableName
     */
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;
    }
    
    /**
     * 设置搜索的列名
     * 
     * @param string $searchColumn
     */
    public function setSearchColumn($searchColumn)
    {
        $this->searchColumn = $searchColumn;
    }
    
    /**
     * 获取调试信息
     * 
     * @param string $searchString
     * @return array
     */
    public function getDebugInfo($searchString)
    {
        $conditions = $this->extractSearchConditions($searchString);
        $sqlResult = $this->buildSqlConditions($conditions);
        
        return [
            'original_search' => $searchString,
            'parsed_conditions' => $conditions,
            'generated_sql' => $sqlResult['sql'],
            'sql_params' => $sqlResult['params']
        ];
    }
}

/**
 * 搜索助手类 - 提供便捷的静态方法
 */
class SearchHelper
{
    /**
     * 快速搜索方法
     * 
     * @param string $searchString 搜索字符串
     * @param string $tableName 表名
     * @param string $searchColumn 搜索列名
     * @return array
     */
    public static function quickSearch($searchString, $tableName = 'articles', $searchColumn = 'content')
    {
        $parser = new AdvancedSearchParser($tableName, $searchColumn);
        return $parser->buildSelectQuery($searchString);
    }
    
    /**
     * 获取WHERE条件
     * 
     * @param string $searchString 搜索字符串
     * @param string $searchColumn 搜索列名
     * @return array
     */
    public static function getWhereCondition($searchString, $searchColumn = 'content')
    {
        $parser = new AdvancedSearchParser('', $searchColumn);
        return $parser->parseSearchString($searchString);
    }
}

?>