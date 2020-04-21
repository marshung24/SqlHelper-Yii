<?php

namespace marsapp\helper\sql;

use Exception;
use Yii;

/**
 * SQL Helper for Yii2
 * 
 * @author  Mars Hung <tfaredxj@gmail.com> 2020-04-19
 */
class SqlHelper
{


    /**
     * *********************************************
     * ************** Public Function **************
     * *********************************************
     */

    /**
     * Creates a Batch-Update command.
     * 
     * @example
     * ```php
     * // Create SqlHelper Object
     * $sqlHelper = new \marsapp\helper\sql\SqlHelper();
     * // Setting batch update data
     * $query = $sqlHelper->batchUpdate('account', ['c_name', 'age'], [
     *    ['Mars', 35],
     *    ['Gunter', 24],
     *    ['Molly', 25],
     *  ],[ 1, 2, 3],'id');
     * // Run batch update
     * $data = $query->execute();
     * // Echo last query statement
     * echo $quiery->getRawSql();
     * echo "\n";
     * var_export($data);
     * ```
     *
     * ```SQL
     * UPDATE `account` SET
     * `c_name` = CASE `id`
     *      WHEN 1 THEN 'Mars'
     *      WHEN 2 THEN 'Gunter'
     *      WHEN 3 THEN 'Molly'
     * END,
     * `age` = CASE `id`
     *      WHEN 1 THEN 35
     *      WHEN 2 THEN 24
     *      WHEN 3 THEN 25
     * END
     * WHERE `id` IN (1,2,3);
     * ```
     * 
     * @author  Mars Hung <tfaredxj@gmail.com> 2020-04-19
     *
     * @param string $table the table that new rows will be inserted into.
     * @param array $columns the column names
     * @param array $rows the rows to be batch inserted into the table
     * @param array $conditionArray rows for primary key array
     * @param string $conditionColumn primary key name
     * @return $this the command object itself
     */
    public function batchUpdate($table, $columns, $rows, $conditionArray, $conditionColumn)
    {
        $columnsLength = sizeof($columns);
        // Check if the length of$rows's element is consistent with the $columns length
        foreach ($rows as $el) {
            if (!is_array($el) || sizeof($el) != $columnsLength) {
                throw new \Exception('Error! $columns,$rows inconsistent length', 400);
            }
        }
        // Check $rows,$conditionArray length, If not the same, throw exception
        if (sizeof($rows) != sizeof($conditionArray)) {
            throw new \Exception('Error! $rows,$conditionArray inconsistent length', 400);
        }


        /**
         * Quote input value
         */
        $table = Yii::$app->db->quoteSql($table);
        $columns = array_map(function ($column) {
            return Yii::$app->db->quoteSql($column);
        }, $columns);
        $rows = array_map(function ($row) {
            foreach ($row as &$i) {
                $i = Yii::$app->db->quoteValue($i);
            }
            return $row;
        }, $rows);
        $conditionArray = array_map(function ($row) {
            return Yii::$app->db->quoteValue($row);
        }, $conditionArray);
        $conditionColumn = Yii::$app->db->quoteSql($conditionColumn);


        // SQL Query Builder
        $sql = "UPDATE `" . $table . "` SET \n";
        foreach ($columns as $colKey => $column) {
            $sql .= "`" . $column . "` = CASE `" . $conditionColumn . "` \n";
            foreach ($conditionArray as $condiKey => $condi) {
                $sql .= "WHEN " . $condi . " THEN " . $rows[$condiKey][$colKey] . " \n";
            }
            $sql .= "END" . ($columnsLength > $colKey + 1 ? ',' : '') . " \n";
        }
        $sql .= "WHERE `" . $conditionColumn . "` IN (" . implode(",", $conditionArray) . ");";


        $query = Yii::$app->db->createCommand();
        $params = [];
        $query->setRawSql($sql);
        $query->bindValues($params);

        return $query;
    }


    /**
     * Handle Array Chunk for SQL
     * 
     * 當 $fieldList 為空時，會將查詢結果設為空，
     * 如需要例外處理，請自行在函式外檢查判斷。
     * 
     * Usage:
     * $query = ActiveRecord::find();
     * \marsapp\helpers\sql\SqlHelper::whereInChunk($fieldName, $fieldList, $query, $size = 300);
     * 
     * @author  Mars Hung <tfaredxj@gmail.com> 2020-04-21
     * 
     * @param string $fieldName 欄位名稱
     * @param array $fieldList 資料陣列
     * @param ActiveQuery $query
     * @param number $size 每次處理大小
     */
    public static function whereInChunk($fieldName, $fieldList, &$query, $size = 300)
    {
        // 參數處理
        $fieldList = (array) $fieldList;

        // 處理非空陣列
        if (!empty($fieldList)) {
            $chunk = array_chunk($fieldList, $size);
            $condition = ['or'];

            foreach ($chunk as $list) {
                $condition[] = ['in', $fieldName, $list];
            }

            $query->andWhere($condition);
        } else {
            // 空陣列時，將查詢結果設為空
            $query->where('1=0');
        }

        return $query;
    }

    /**
     * 協助處理時間段交集SQL指令
     * 
     * Usage:
     * $query = ActiveRecord::find();
     * \marsapp\helpers\sql\SqlHelper::timeIntersect($sCol, $eCol, $sDate, $eDate, $query)
     * 
     * @author  Mars Hung <tfaredxj@gmail.com> 2020-04-21
     * 
     * @param string $sCol 起日欄位名
     * @param string $eCol 訖日欄位名
     * @param string $sDate 起日
     * @param string $eDate 訖日
     * @param ActiveQuery $query
     */
    public static function timeIntersect($sCol, $eCol, $sDate, $eDate, &$query)
    {
        $query->andWhere(
            [
                'not',
                [
                    'or',
                    ['>', $sCol, $eDate],
                    [
                        'and',
                        ['<', $eCol, $sDate],
                        ['<>', $eCol, '0000-00-00']
                    ]
                ]
            ]
        );

        return $query;
    }
}
