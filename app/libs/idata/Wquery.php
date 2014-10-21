<?php
/**
 * 数据库接口封装类
 * Enter description here ...
 * @author Administrator
 *
 */
class Wquery
{
	var $db;
	//	function iInsert($Table) {
	//		$dbTable = $this->db->table ( $Table );
	//		return $this->db->idata->dataInsert ( $dbTable );
	//	}
	function __construct(db $db)
	{
		$this->db = $db;
	}
	// 修改数据库记录
	public function update($dbTable, $condition, $arrUpdate = null, $strUpdate = null, $limit = 0)
	{
		if ($condition != '')
		{
			$condition = "WHERE $condition";
		}
		while (list($key, $item) = @each($arrUpdate)) # 连接要更新的字段
		{
			$updateFieldArr[] = " {$this->db->charSplit}$key{$this->db->charSplit} = '$item'";
		}
		$updateFields = @join(',', $updateFieldArr);
		if ($strUpdate)
		{
			if ($updateFields)
				$strUpdate .= ', ' . $updateFields;
		}
		else
		{
			$strUpdate = $updateFields;
		}
		$strSql = " UPDATE $dbTable SET $strUpdate $condition";
		if ($limit > 0)
		{
			$strSql .= " LIMIT $limit";
		}
		return $this->db->query($strSql);
	}
	
	// 插入记录
	public function insert($dbTable, $arrInsert)
	{
		if (is_array(current($arrInsert)))
		{
			$insertfields = $this->db->charSplit . join($this->db->charSplit . ', ' . $this->db->charSplit, array_keys(current($arrInsert))) . $this->db->charSplit;
			while (list($key, $item) = @each($arrInsert))
			{
				$insertValuesArr[] = "( '" . join("', '", $item) . "' )";
			}
			$insertValues = join(', ', $insertValuesArr);
		}
		else
		{
			$insertfields = $this->db->charSplit . join($this->db->charSplit . ', ' . $this->db->charSplit, array_keys($arrInsert)) . $this->db->charSplit;
			$insertValues = "( '" . join("', '", $arrInsert) . "' )";
		}
		
		$strSql = "INSERT INTO $dbTable ( $insertfields ) VALUES $insertValues";
		
		return $this->db->query($strSql);
	}
	
	// 插入记录
	public function replace($dbTable, $arrInsert)
	{
		if (is_array(current($arrInsert)))
		{
			$insertfields = $this->db->charSplit . join($this->db->charSplit . ', ' . $this->db->charSplit, array_keys(current($arrInsert))) . $this->db->charSplit;
			while (list($key, $item) = @each($arrInsert))
			{
				$insertValuesArr[] = "( '" . join("', '", $item) . "' )";
			}
			$insertValues = join(', ', $insertValuesArr);
		}
		else
		{
			$insertfields = $this->db->charSplit . join($this->db->charSplit . ', ' . $this->db->charSplit, array_keys($arrInsert)) . $this->db->charSplit;
			$insertValues = "( '" . join("', '", $arrInsert) . "' )";
		}
		
		$strSql = "REPLACE INTO $dbTable ( $insertfields ) VALUES $insertValues";
		
		return $this->db->query($strSql);
	}
	
	// 删除数据库记录
	public function delete($dbTable, $condition = '', $orderBy = '', $limit = 0, $offset = 0)
	{
		if ($condition != '')
		{
			$condition = "WHERE $condition";
		}
		
		$orderBy = trim($orderBy);
		if ($orderBy != '' && !strstr(strtoupper($orderBy), 'ORDER BY'))
		{
			$orderBy = "ORDER BY $orderBy";
		}
		
		$strSql = "DELETE FROM $dbTable $condition $orderBy";
		
		$limit = intval($limit);
		$offset = intval($offset);
		if ($limit)
		{
			$strSql .= " LIMIT $limit";
		}
		if ($offset)
		{
			$strSql .= " OFFSET $offset";
		}
		
		return $this->db->query($strSql);
	}
	
	function get($dbTable, $condition = '')
	{
		$this->select($dbTable, $condition);
	}
	// 查询数据库记录
	function select($dbTable, $condition = '', $orderBy = '', $limit = 0, $offset = 0, $fields = '*', $groupBy = '')
	{
		if (is_array($fields))
		{
			$fieldList = @implode(',', $fields);
		}
		else
		{
			$fieldList = $fields;
		}
		if ($condition != '')
		{
			$condition = "WHERE $condition";
		}
		$orderBy = trim($orderBy);
		if ($orderBy != '' && !strstr(strtoupper($orderBy), 'ORDER BY'))
		{
			$orderBy = "ORDER BY $orderBy";
		}
		
		$groupBy = trim($groupBy);
		if ($groupBy != '' && !strstr(strtoupper($groupBy), 'GROUP BY'))
		{
			$groupBy = "GROUP BY $groupBy";
		}
		
		$strSql = " SELECT $fieldList FROM $dbTable $condition $groupBy $orderBy";
		
		$limit = intval($limit);
		$offset = intval($offset);
		if ($limit)
		{
			$strSql .= " LIMIT $limit";
		}
		if ($offset)
		{
			$strSql .= " OFFSET $offset";
		}
		$res = $this->db->query($strSql);
		
		if ($res)
		{
			$records = $res->fetchAll(PDO::FETCH_ASSOC);
			return $records;
		}
		else
		{
			return false;
		}
	}
}
/**
 * uCache::create($uuId)->cache()
 */