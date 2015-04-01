<?php
namespace oop\Core\Database;
use \oop\Core\Table\Table;
use \PDO;

class MysqlDatabase{

	private $db_name;
	private $db_user;
	private $db_pass;
	private $db_host;
	private $pdo;

	private $rowcount;

	private $fetch_mode_array =[
				
		'num'   => PDO::FETCH_NUM,
		'both'  => PDO::FETCH_BOTH,
		'assoc' => PDO::FETCH_ASSOC,
		'obj'   => PDO::FETCH_OBJ
	];

	public function __construct(){

		$require = require 'oop/config/config.php';

		$this->db_name = $require['db_name'];
		$this->db_user = $require['db_user'];
		$this->db_pass = $require['db_pass'];
		$this->db_host = $require['db_host'];

	}

	private function getPDO(){

		if($this->pdo === NULL){

			$pdo = new PDO('mysql:dbname='.$this->db_name.';host='.$this->db_host, $this->db_user, $this->db_pass);
			$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			$this->pdo = $pdo;
		}

		return $this->pdo;	

	}

	public function query($statement, $one = false, $fetch = null ){

		$req = $this->getPDO()->query($statement);

		$this->rowcount = $req->rowCount(); // count number of results

		if(
			strpos($statement, 'UPDATE') === 0 ||
			strpos($statement, 'INSERT') === 0 ||
			strpos($statement, 'DELETE') === 0
		){

			return $req;
		}

		if($fetch == null){

			$fetch_mode = 'obj';

		}else{

			if(array_key_exists($fetch, $this->fetch_mode_array )){

				$fetch_mode = $fetch;

			}else{

				$fetch_mode = 'obj';
			}
		}

		if($one){

			$datas = $req->fetch($this->fetch_mode_array[$fetch_mode]);

		} else {

			$datas = $req->fetchAll($this->fetch_mode_array[$fetch_mode]);
		}
		
		return $datas;

	}

	public function prepare($statement, $attributes, $one = false, $fetch = null){

		$req = $this->getPDO()->prepare($statement);

		$res = $req->execute($attributes);

		$this->rowcount = $req->rowCount(); // count number of results

		if(
			strpos($statement, 'UPDATE') === 0 ||
			strpos($statement, 'INSERT') === 0 ||
			strpos($statement, 'DELETE') === 0
		){

			return $res;
		}

		if($fetch == null){

			$fetch_mode = 'obj';

		}else{

			if(array_key_exists($fetch, $this->fetch_mode_array )){

				$fetch_mode = $fetch;

			}else{

				$fetch_mode = 'obj';
			}
		}

		if($one){

			$datas = $req->fetch($this->fetch_mode_array[$fetch_mode]);

		} else {

			$datas = $req->fetchAll($this->fetch_mode_array[$fetch_mode]);
		}
		
		//$this->rowcount = count($datas); // nombre resultat
		return $datas;
	}

	public function lastInsertId(){

		return $this->getPDO()->lastInsertId();
	}

	public function getRowCount(){

		return $this->rowcount;
	}
}