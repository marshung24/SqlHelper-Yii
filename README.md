# SqlHelper for Yii2

Provides assistance in using functions to handle SQL grammar construction.

[![Latest Stable Version](https://poser.pugx.org/marsapp/sqlhelper/v/stable)](https://packagist.org/packages/marsapp/sqlhelper) [![Total Downloads](https://poser.pugx.org/marsapp/sqlhelper/downloads)](https://packagist.org/packages/marsapp/sqlhelper) [![Latest Unstable Version](https://poser.pugx.org/marsapp/sqlhelper/v/unstable)](https://packagist.org/packages/marsapp/sqlhelper) [![License](https://poser.pugx.org/marsapp/sqlhelper/license)](https://packagist.org/packages/marsapp/sqlhelper)
<!-- TOC -->

- [SqlHelper for Yii2](#sqlhelper-for-yii2)
  - [Installation](#installation)
    - [Composer Install](#composer-install)
  - [API Reference](#api-reference)
    - [batchUpdate](#batchupdate)
      - [Description](#description)
      - [Usage](#usage)
      - [SQL syntax](#sql-syntax)
    - [whereInChunk](#whereinchunk)
      - [Description](#description-1)
      - [Usage](#usage-1)
      - [SQL syntax](#sql-syntax-1)
    - [timeIntersect](#timeintersect)
      - [Description](#description-2)
      - [Usage](#usage-2)
      - [SQL syntax](#sql-syntax-2)

<!-- /TOC -->

## Installation
### Composer Install
```
# composer require marsapp/sqlhelper-yii2
```

## API Reference

### batchUpdate
#### Description

Help construct the query syntax for batch updates by using the functions.

```php
batchUpdate($table, $columns, $rows, $conditionArray, $conditionColumn) : \yii\db\Command
```
> Parameters
> - $table: table name
> - $columns: Fields to be processed
> - $rows: The values to be processed
> - $conditionArray: The value of the target field
> - $conditionColumn: The name of the target field
> 
> Return Values
> - Returns \yii\db\Command

#### Usage

We can use batchUpdate via SqlHelper as follows:

```php
// Create SqlHelper Object
$sqlHelper = new \marsapp\helper\sql\SqlHelper();

// Setting batch update data
$query = $sqlHelper->batchUpdate('account', ['c_name', 'age'], [
   ['Mars', 35],
   ['Gunter', 24],
   ['Molly', 25],
 ],[ 1, 2, 3],'id');

// Run batch update
$data = $query->execute();

// Echo last query statement
echo $quiery->getRawSql();
echo "\n";
var_export($data);
```

#### SQL syntax

The database syntax constructed by batchUpdate is as follows:

```SQL
UPDATE `account` SET
`c_name` = CASE `id`
     WHEN 1 THEN 'Mars'
     WHEN 2 THEN 'Gunter'
     WHEN 3 THEN 'Molly'
END,
`age` = CASE `id`
     WHEN 1 THEN 35
     WHEN 2 THEN 24
     WHEN 3 THEN 25
END
WHERE `id` IN (1,2,3);
```

### whereInChunk
#### Description

Help us split the array by using the functions.

```php
whereInChunk($fieldName, $fieldList, $query, $size) : ActiveQuery
```
> Parameters
> - $fieldName: Field to be processed
> - $fieldList: The values to be processed
> - $query: ActiveQuery
> - $size: Cutting length
> 
> Return Values
> - Returns ActiveQuery


#### Usage

We can use whereInChunk via SqlHelper as follows:

```PHP
// Setting Arguments
$fieldName = 'pkey';
$fieldList = [1, 2, 3, 4];
$query = ActiveRecord::find();
$size = 3;
$data = \marsapp\helpers\sql\SqlHelper::whereInChunk($fieldName, $fieldList, $query, $size)
    ->asArray()
    ->all();

// Print Query Syntax
echo $query->createCommand()->sql;
echo "\n";
echo $query->createCommand()->getRawSql();
echo "\n";

// Print Data
var_export($data);
```


#### SQL syntax

The database syntax constructed by batchUpdate is as follows:

```
SELECT * FROM `TABLE_NAME`
WHERE
  (`pkey` IN(1, 2, 3)) OR (`pkey` = 4)
```


### timeIntersect
#### Description

Help construct the query syntax for the intersection of time periods by using the functions.

```php
timeIntersect($sCol, $eCol, $sDate, $eDate, $query) : ActiveQuery
```
> Parameters
> - $sCol: Field name for start date
> - $eCol: Field namd for end date
> - $sDate: Value for start date
> - $eDate: Value for end date
> - $query: ActiveQuery
> 
> Return Values
> - Returns ActiveQuery

#### Usage

We can use timeIntersect via SqlHelper as follows:

```PHP
// Setting Arguments
$sCol = 'start_date';
$eCol = 'endd_date';
$sDate = '2020-04-01';
$eDate = '2020-04-30';

// Sql Builder
$query = ActiveRecord::find();
$data = \marsapp\helpers\sql\SqlHelper::timeIntersect($sCol, $eCol, $sDate, $eDate, $query)
    ->asArray()
    ->all();

// Print Query Syntax
echo $query->createCommand()->sql;
echo "\n";
echo $query->createCommand()->getRawSql();
echo "\n";

// Print Data
var_export($data);
```


#### SQL syntax

The database syntax constructed by batchUpdate is as follows:

```
SELECT * FROM `TABLE_NAME`
WHERE NOT
  (
    (`start_date` > '2020-04-30') OR(
      (`endd_date` < '2020-04-01') AND(`endd_date` <> '0000-00-00')
    )
  )
```


