<?php

namespace oop\Core\Table;

use oop\Core\Database\MysqlDatabase;

class Table{

	protected $db;

	public $table;

	public $countPaging = null;
	public $countStatement = null; // compte nombre résultat d'une requette;
	public $_paginate_number; // nombre retour pagination
	public $_paginate_currentPage = null; // Current page of pagination
	private $where; // for paginate

	private $statement = null;

	const SELECT   = 'SELECT ';
	const FROM     = 'FROM ';
	const WHERE    = 'WHERE ';
	const ORDER_BY = 'ORDER BY ';

	public function __construct(){

		$this->db = new MysqlDatabase();	
	}

	private function countStatement($query, $attributes = null){

		$statement = "SELECT count(*) AS rowCount ".$query;

		$countStatement =  $this->query($statement, $attributes); // appel ou function query

		$this->countStatement .= $countStatement[0]->rowCount.';'; // compter le nombre de page
	}


	/**
	 * Recovers all the data, and accepts the paging system
	 * @param $paginate boolean  true:pagination enabled 
	 * @param $nbPage int
	 * @param $params array
	 * @param $attributes array
	 * @return array
	 */
	public function all($paginate = false, $nbPage = 4, array $params = null, $attributes = null){

		$table = self::FROM.$this->table;

		$statement = "SELECT * {$table}";

		//Param conditions and Order by
		$whereOrderBy = $this->whereOrderBy($params);

		if($paginate){ // paging enabled

			//count the number obtained results
			$this->countStatement($table.$whereOrderBy, $attributes); 

			//we pass many results to display
			$limit = $this->limit($nbPage);

			$datas = $this->query($statement.$whereOrderBy.$limit, $attributes);

		}else{ // paging disabled

			$this->countStatement($table, $attributes); // on compte nombre de page
			$datas = $this->query($statement);
		}

		return $datas;
	}

	/**
	 * function that handles the limit of a SELECT query, it is enabled for paging
	 * @param $nbPage int
	 * @return array
	 */
	private function limit($nbPage){

		$perPage = $nbPage;
		$nbPage = ceil($this->countStatement/$perPage);
		$this->_paginate_number = $nbPage;

		// check param GET in url and make sur > 0
		$this->_paginate_currentPage = (isset($_GET['page']) && ($_GET['page'] > 0) && ($_GET['page'] <= $nbPage)) ? $_GET['page'] : 1;
		
		return $limit = " LIMIT ".(($this->_paginate_currentPage-1)*$perPage)." , {$perPage}";

		return $datas;
	}

	/**
	 * Function to select the data via the id only
	 * @param $id int
	 * @param $fetch_mode string ('num', 'both', 'assoc', 'obj') // by default it takes FETCH_OBJ
	 * @return array
	 */
	public function findById(int $id, $fetch_mode = null){

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
	public function find(array $params, $attributes = null, $one = false, $fetch_mode = null){
		
		$joins = null;

		// Type of requete (Query = false or prepare = true)
		$type_statement = (empty($attributes)) ? false: true; 

		//Param FIELDS
		$fields = (empty($params['fields'])) ? '* ' : implode(',', $params['fields']).' ';
		
		//Param Table
		if(empty($params['table'])){

			$table = !empty($this->table) ? self::FROM.$this->table : 'table undefined';

		}else{

			if(is_array($params['table'])){

				foreach($params['table'] as $tableName => $aliasTable){

					$this->table = $tableName;
					$tableWithAlias = $tableName." AS ".$aliasTable;
				}

				$table = self::FROM.$tableWithAlias;

			}else{

				$this->table = $params['table'];
				$table .= self::FROM.$this->table;
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
								
				if(is_numeric(key($condition))){
					
					for($i=0;$i<$count;$i++){

						$joins = " ".$type[$i]." JOIN ".$tables[$i]." AS ".$alias[$i]." ON ".$condition[$i]." ";
					}

				}else{

					$i=0;
					foreach($condition as $key => $val){

						$joins = " ".$type[$i]." JOIN ".$tables[$i]." AS ".$alias[$i]." ON ".$key." = ".$val." ";
						$i++;
					}
				}
			}
		}

		//Param conditions and Order by
		$whereOrderBy = $this->whereOrderBy($params);
		
		// all query chained
		$statement = self::SELECT.$fields.$table.$joins.$whereOrderBy;

		// on compte nombre de page
		$this->countStatement($table.$joins.$whereOrderBy, $attributes); 
		
		return $this->query($statement, $attributes, $one, $fetch_mode );
	}

	/**
	 * function that handles the WHERE and ORDER BY called a query
	 * @param $params array
	 * @return string
	 */
	private function whereOrderBy($params){

		$datas = null;

		if(isset($params['conditions']) && is_array($params['conditions'])){

			foreach($params['conditions'] as $key => $val){

				if(is_numeric($key)){

					$conditions[] = $val;

				}else{

					$conditions[] = $key.' = '.$val;
				}
				
			}

			$datas = ' WHERE '.implode(' AND ', $conditions);
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
	public function create($fields){

		$sql_parts = [];

		$attributes = [];

		foreach($fields as $k => $v){

			$sql_parts[] = "$k = ?";

			$attributes[] = $v;
		}

		$sql_parts = implode(', ', $sql_parts);

		$statement = "INSERT INTO {$this->table} SET $sql_parts"; // my requette

		$this->countStatement .= '1;';
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
		$this->countStatement .= '1;';
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
		$this->countStatement .= '1;';
		return $this->query($statement, $attributes, true); 
	}
	
	/**
	 * function to delete the data from the id only
	 * @param $id int
	 * @return boolean
	 */
	public function deleteById(int $id){

		$statement = "DELETE FROM {$this->table} WHERE id = ?";
		$this->countStatement .= '1;';
		return $this->query($statement, [$id], true); 
	}
	
	/**
	 * function to delete the data with more options
	 * @param $fields array
	 * @return boolean
	 */
	public function delete(array $fields){

		$sql_parts = [];
		$attributes = [];

		//Param FIELDS
		foreach($fields as $k => $v){

			$sql_parts[] = "$k = ?";
			$attributes[] = $v;
		}

		$sql_parts = implode(' AND ', $sql_parts);

		$statement = "DELETE FROM {$this->table} WHERE $sql_parts";
		$this->countStatement .= '1;';
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

		$this->statement .= $statement.';';

		if($attributes !== null){

			$execute = $this->db->prepare($statement, $attributes, $fetch_mode, $one ); // requette prepare

		}else{

			$execute = $this->db->query($statement, $fetch_mode, $one); // requette query
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
	 * it displays a table of queries executed
	 * @return string
	 */
	public function getSQL(){

		$statements = explode(';', $this->statement);

		$nb_results = explode(';', $this->countStatement);

		$i = 0;

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
		
		foreach($statements as $statement){

			if(!empty($statement)){

				if(!stripos($statement, 'count(*) AS RowCount')){
					echo '<tr>
					<td style="padding:0.5em">'.$statement.'</td>
					<td align="center" style="padding:0.5em">'.$nb_results[$i].'</td>
					</tr>';

					$i++;
				}
				
			}
		}

		echo '
			</tbody>
			</table>';	
			echo 'Number of query :'.$i.'<br/>';		
		echo '</pre>';
		echo '<hr/>';
	}
}