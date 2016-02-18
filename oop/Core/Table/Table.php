<?php
/**
* create-find-findById,Delete-deleteById-query
*
* PHP version 5
*
* Copyright (C) 2015  Mehdi ROCHDI / Beone Advertising
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
* 
* @category   Database
* @package    CRUD (Create Read Update Delete) V2
* @author     Mehdi RCHDI <mehdi.rochdi@gmail.com>
* @copyright  2009-2015 Mehdi ROCHDI / beone.ma
* @license    http://www.gnu.org/licenses/  GNU General Public License
* @link       https://github.com/mehdirochdi/Advanced-CRUD-PHP-PDO-V2
* @since      File available since Release 2.0.0
*
* The purpose of this class is to create an interface for a more fluid and simple interaction with MYSQL 
* database it use a PDO Driver which is more intuitive.
* is using insertion methods (create), update (update) and passes the id as key parameter, data search (find, findById),
* and finally join supportation complex data deletion (delete) (deleteById) and read data with function (read, readById)
* the New in this release 2 is the pagination is accepted
*/
/**
* Creates an instance of the CRUD object and opens or reuses a connection
* to a database.
* 
* 
*/
namespace oop\Core\Table;

use oop\Core\Database\MysqlDatabase;

class Table{

	protected $db;

	public $table;

	public $countStatement = null; // count the actual number of a query with pagination 
	public $_paginate_number; // nombre retour pagination
	public $_paginate_currentPage = null; // Current page of pagination

	private $statement = []; // count number of queries
	private $countResults = []; // count number of resultat queries

	const SELECT   = 'SELECT ';
	const FROM     = 'FROM ';
	const WHERE    = 'WHERE ';
	const ORDER_BY = 'ORDER BY ';

	public function __construct(){

		//require __DIR__.'/../Database/MysqlDatabase.php';

		$this->db = new MysqlDatabase();	
	}

	private function countStatement($query, $attributes = null){

		$statement = "SELECT count(*) AS rowCount ".$query;

		$countStatement =  $this->query($statement, $attributes); // appel ou function query

		$this->countStatement = $countStatement[0]->rowCount; // compter le nombre de page
	}

	/**
	* Recovers all the data, and accepts the paging system
	* @param $paginate boolean  true:pagination enabled 
	* @param $nbPage int
	* @param $params array
	* @param $attributes array
	* @return array
	*/
	public function all($paginate = false, $nbPage = null, array $parameters = null, $attributes = null){

		$nbPage = ($nbPage == null) ? 4 : $nbPage; // default is display 4 results in paging page

		$table = self::FROM.$this->table;

		$statement = "SELECT * {$table}";

		if(is_array($parameters) && array_key_exists('order', $parameters)){

			$params = $parameters;
		}

		//Param Order by only
		$whereOrderBy = $this->whereOrderBy($parameters);

		if($paginate){ // paging enabled

			//count the number obtained results
			$this->countStatement($table.$whereOrderBy, $attributes); 
		}

		//we pass many results to display
		$limit = $this->limit($nbPage);

		return $this->query($statement.$whereOrderBy.$limit, $attributes);
	}

	/**
	* function that handles the limit of a SELECT query, it is enabled for paging
	* @param $nbPage int
	* @return array
	*/
	private function limit($nbPage){

		$perPage = $nbPage;

		//assign number of result to $this->_paginate_number
		$this->_paginate_number = $nbPage = ceil($this->countStatement/$perPage);

		// check param GET in url and make sur > 0
		$this->_paginate_currentPage = (isset($_GET['p']) && ($_GET['p'] > 0) && ($_GET['p'] <= $nbPage)) ? $_GET['p'] : 1;
		
		return $limit = " LIMIT ".(($this->_paginate_currentPage-1)*$perPage)." , {$perPage}";
	}

	/**
	* Function to select the data via the id only
	* @param $id int
	* @param $fetch_mode string ('num', 'both', 'assoc', 'obj') // by default it takes FETCH_OBJ
	* @return array
	*/
	public function findById($id, $fetch_mode = null){

		return $this->query("SELECT * FROM {$this->table} WHERE id = ?", [$id], true, $fetch_mode); // true retrieve a single record
	}
	
	/**
	* function to list the data with several possibilities.
	* he accepts the conditions, joints and other
	* @param $params array
	* @param $attributes array
	* @param $one boolean // true:fetch || false:fetchAll
	* @param $fetch_mode string ('num', 'both', 'assoc', 'obj') // by default it takes FETCH_OBJ
	* @return array
	*/
	public function find($genre = null, array $params, $attributes = null, $fetch_mode = null, $nbPage = 4){
		
		$joins = null;
		$joinstype = null;	
		$limit = null;

		$nbPage = ($nbPage === null) ? 4 : $nbPage; // default is display 4 results in paging page

		$genres = ['first', 'all', 'pagination'];
		
		if(in_array($genre, $genres)){

			if(($genre === 'all') || ($genre === 'pagination')){

				$one = false;

			}else if($genre === 'first'){

				$one = true;
			}

		}else{

			$one = false;
		}

		// Type of requete (Query = false or prepare = true)
		$type_statement = (empty($attributes)) ? false: true; 

		//Param FIELDS
		$fields = (empty($params['fields'])) ? '* ' : implode(',', $params['fields']).' ';
		
		//Param Table
		if(empty($params['table'])){

			$table = !empty($this->table) ? self::FROM.$this->table : 'table undefined';

			$table = (!empty($params['alias'])) ? $table.' AS '.$params['alias'] : $table;

		}else{

			if(is_array($params['table'])){

				foreach($params['table'] as $tableName => $aliasTable){

					//$this->table = $tableName;
					$tableWithAlias = $tableName." AS ".$aliasTable;
				}

				$table = self::FROM.$tableWithAlias;

			}else{

				$this->table = $params['table'];
				$table = self::FROM.$this->table;
			}
		}

		//Param JOINS
		if(!empty($params['joins'])){

			$joins 	   	= $params['joins'];
			$tables     = (!empty($joins['tables']) && is_array($joins['tables'])) ? $joins['tables'] : null;
			$alias     	= (!empty($joins['alias']) && is_array($joins['alias'])) ? $joins['alias'] : null;
			$type     	= (!empty($joins['type']) && is_array($joins['type'])) ? $joins['type'] : null;
			$condition  = (!empty($joins['condition']) && is_array($joins['condition'])) ? $joins['condition'] : null;

			$count = count($tables);
			
			if(is_array($joins)){				
				
				$i=0;
				$joinstype = null;
				foreach($condition as $key => $val){

					$joinstype .= " ".$type[$i]." JOIN ".$tables[$i]." AS ".$alias[$i]." ON ".$key." = ".$val." ";
					$i++;
				}

				// if(is_numeric(key($condition))){

				// 	for($i=0;$i<$count;$i++){

				// 		$joins = " ".$type[$i]." JOIN ".$tables[$i]." AS ".$alias[$i]." ON ".$condition[$i]." ";
				// 	}

				// }else{

				// 	$i=0;
				// 	foreach($condition as $key => $val){

				// 		echo $joins = " ".$type[$i]." JOIN ".$tables[$i]." AS ".$alias[$i]." ON ".$key." = ".$val." ";
				// 		$i++;
				// 	}
				// }
			}
			//echo $joins;
		}

		//Param conditions and Order by
		$whereOrderBy = $this->whereOrderBy($params);
		
		if($genre === 'pagination'){

			// on compte nombre de page
			$this->countStatement($table.$joinstype.$whereOrderBy, $attributes);

			//we pass many results to display
			$limit = $this->limit($nbPage);

		}		
		
			// all query chained
			$statement = self::SELECT.$fields.$table.$joinstype.$whereOrderBy.$limit;
			return $datas = $this->query($statement, $attributes, $one, $fetch_mode );
	}

	/**
	 * function that handles the WHERE and ORDER BY called a query
	 * @param $params array
	 * @return string
	 */
	private function whereOrderBy($params){

		$datas = null;
		$pourcent = '%';
		$mywhere = (isset($params['conditions'])) ? null : ' WHERE ';
		if(isset($params['conditions']) && is_array($params['conditions'])){
			$AND = ' AND ';
			foreach($params['conditions'] as $key => $val){

				if(is_numeric($key)){

					$conditions[] = $val;

				}else{

					$conditions[] = $key.' = '.$val;
				}
				
			}

			$datas = ' WHERE '.implode($AND, $conditions);
		}

		//Like Params for search
		if(isset($params['likes']) && is_array($params['likes'])){

			foreach($params['likes'] as $key => $val){

				if(is_numeric($key)){

					$likes[] = $mywhere.$val;

				}else{

					$mylike = $key.' LIKE "%'.$val.'%"';

					if($val == '?'){

						$mylike = $key.' LIKE '.$val;
					}

					$likes[] = $mywhere.$mylike;
				}
				
			}

			$and = (isset($AND)) ? $AND : null;

			$datas .= $and.implode(' AND ', $likes);

		}else if(isset($params['likesor']) && is_array($params['likesor'])){

			foreach($params['likesor'] as $key => $val){

				if(is_numeric($key)){

					$likes[] = $mywhere.$val;

				}else{

					$mylike = $key.' LIKE "%'.$val.'%"';

					if($val == '?'){

						$mylike = $key.' LIKE '.$val;
					}

					$likes[] = $mywhere.$mylike;
				}
				
			}

			$and = (isset($AND)) ? $AND : null;

			$datas .= $and.implode(' OR ', $likes);
		}

		if(isset($params['order']) && is_array($params['order'])){

			foreach($params['order'] as $key => $val){
				
				if(is_numeric($key)){

					$orders[] = $val;
				}else{

					$orders[] = $key.' '.$val;
				}
				
			}

			$datas .=' ORDER BY '.implode(',', $orders);
		}

		return $datas;
	}

	/**
	 * function to insert data
	 * @param $fields array
	 * @return boolean
	 */
	public function create($fields, $table = null){

		($table !== null) ? $this->table = $table : null;

		$sql_parts = [];

		$attributes = [];

		foreach($fields as $k => $v){

			$sql_parts[] = "$k = ?";

			$attributes[] = $v;
		}

		$sql_parts = implode(', ', $sql_parts);

		$statement = "INSERT INTO {$this->table} SET $sql_parts"; // my requette

		return $this->query($statement, $attributes, true);
	}

	/**
	 * function to update the data with more options
	 * @param $params array
	 * @param $attributes array
	 * @return boolean
	 */
	public function update(array $params, $attributes = null){

		$sql_parts = [];
		$Allattributes = [];

		//Param FIELDS
		foreach($params['fields'] as $k => $v){

			$sql_parts[] = "$k = ?";
			$Allattributes[] = $v;
		}

		if($attributes !== null){

			foreach($attributes as $val){

				array_push($Allattributes, $val);
			}
		}
		
		$sql_parts = implode(', ', $sql_parts);
		 
		 //Param conditions and Order by
		$whereOrderBy = $this->whereOrderBy($params);

		$statement = "UPDATE {$this->table} SET $sql_parts $whereOrderBy";

		return $this->query($statement, $Allattributes, true);
	}

	/**
	 * function to change the data, he accepted the id as a condition
	 * @param $fields array
	 * @param $id array
	 * @return boolean
	 */
	public function updateById(array $fields, array $id = null){

		$sql_parts = [];
		$attributes = [];

		foreach($fields as $k => $v){

			if($k == 'id'){

				$id = [$v];
			}

			else{

				$sql_parts[] = "$k = ?";
				$attributes[] = $v;
			}
		}

		array_push($attributes, current($id));

		$sql_parts = implode(', ', $sql_parts);

		$statement = "UPDATE {$this->table} SET $sql_parts WHERE id = ?";

		return $this->query($statement, $attributes, true); 
	}
	
	/**
	 * function to delete the data from the id only
	 * @param $id int
	 * @return boolean
	 */
	public function deleteById($id){

		$statement = "DELETE FROM {$this->table} WHERE id = ?";

		return $this->query($statement, [$id], true); 
	}
	
	/**
	 * function to delete the data with more options
	 * @param $fields array
	 * @return boolean
	 */
	public function delete($fields){

		$sql_parts = [];
		$attributes = [];

		//Param FIELDS
		foreach($fields as $k => $v){

			$sql_parts[] = "$k = ?";
			$attributes[] = $v;
		}

		$sql_parts = implode(' AND ', $sql_parts);

		$statement = "DELETE FROM {$this->table} WHERE $sql_parts";

		return $this->query($statement, $attributes);
	}

	/**
	 * function to retrieve the data key and value
	 * @param $key string fieldname
	 * @param $value string fieldname
	 * @return array
	 */
	public function extract($key, $value){

		$records = $this->all();

		$return = [];

		foreach($records as $v){

			$return[$v->$key] = $v->$value;
		}

		return $return;
	}

	/**
	 * fonction pour traiter vos requête directement
	 * @param $statement string
	 * @param $attributes array
	 * @param $one boolean // true:fetch || false:fetchAll
	 * @param $fetch_mode string ('num', 'both', 'assoc', 'obj') // by default it takes FETCH_OBJ
	 * @return array
	 */
	public function query($statement, $attributes = null, $one = false, $fetch_mode = null ){


		if(!stripos($statement, 'count(*) AS rowCount')){

			$this->statement[] = $statement;
		}
		

		if($attributes !== null){

			$execute = $this->db->prepare($statement, $attributes, $one, $fetch_mode); // requette prepare

		}else{

			$execute = $this->db->query($statement, $one, $fetch_mode); // requette query
		}

		if(!stripos($statement, 'count(*) AS rowCount')){

			$this->countResults[] = $this->rowCount();
		}

		return $execute;
	}

	/**
	 * counts the number of matches for each query called
	 * @return array
	 */
	public function rowCount(){

		return $this->db->getRowCount();
	}

	/**
	*  Last insert id (after create)
	* @return int
	*/
	public function lastInsertId(){

		return $this->db->lastInsertId();
	}

	/**
	*  transaction
	*/

	public function transact($type){

		if(isset($type)){
			switch($type){

				case"transaction":
					$this->db->transaction();
					break;
				case"commit":
					$this->db->commit();
					break;
				case"rollback":
					$this->db->rollback();
					break;
			}
		}else{

			return false;
		}
	}

	// public function transaction(){

	// 	$this->db->transaction();
	// }

	// public function commit(){

	// 	$this->db->commit();
	// }

	// public function rollback(){

	// 	$this->db->rollback();
	// }


	/**
	* it displays a table of queries executed
	* @return string
	*/
	public function SQL_dump(){

		$statements = $this->statement; // count total query

		$nb_results = $this->countStatement; // for pagination

		$countResults = $this->countResults; // all count results query

		echo '<pre>';
		echo '
			<table border="1" style="border-collapse:collapse; border-spacing:0; border:1px solid #DDDDDD;">
				<thead>
					<tr>
						<th>Query</th>
						<th>Number result</th>
					</tr>
				</thead>
				<tbody>';
		for($i=0;$i<count($statements);$i++){

			echo '<tr>
			<td style="padding:0.5em">'.$statements[$i].'</td>
			<td align="center" style="padding:0.5em">'.$countResults[$i].'</td>
			</tr>';
		}

		echo '
			</tbody>
			</table>';	
			echo 'Number of query :'.count($countResults).'<br/>';		
		echo '</pre>';
		echo '<hr/>';
	}

}