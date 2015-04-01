<?php
require 'vendor/autoload.php';

use \oop\Core\Table\Table;

$db = new Table();
$db->table = 'articles';

//////////////////////////////////////////////////////////////////////////////////////////////
										//EXTRACT()//
/////////////////////////////////////////////////////////////////////////////////////////////

// $extract = $db->extract('id', 'title'); 
// echo 'Le row count est : '.$db->rowCount();
// var_dump($extract);

//////////////////////////////////////////////////////////////////////////////////////////////
										//CREATE()//
/////////////////////////////////////////////////////////////////////////////////////////////

// $create = $db->create([
// 		'langue_id' => 1,
// 		'category_id' => 4,
// 		'title' => 'Ma troisieme article sur internet',
// 		'description' => 'Lorem ipsum 3 article sur internet',
// 	]);
// echo 'Le row count est : '.$db->rowCount().'<br/>';
// echo 'le dernier Id : '.$db->lastInsertId();
// var_dump($create);

//////////////////////////////////////////////////////////////////////////////////////////////
										//UPDATEById()//
/////////////////////////////////////////////////////////////////////////////////////////////

// $update = $db->updateById([
// 		'langue_id' => 1,
// 		'category_id' => 1,
// 		'title' => 'Ma quatriéme article sur fashion',
// 		'description' => 'Lorem ipsum 4 article',
// 	], ['33']);

// $update = $db->updateById([
// 		'id' => '53',
// 		'langue_id' => 1,
// 		'category_id' => 1,
// 		'title' => 'Ma quatriéme article sur internet',
// 		'description' => 'LLorem ipsum 4 article sur internet',
// 	]);

// echo 'Le row count est : '.$db->rowCount().'<br/>';

// var_dump($update);

//////////////////////////////////////////////////////////////////////////////////////////////
										//UPDATE()//
/////////////////////////////////////////////////////////////////////////////////////////////
// $update1 = $db->update([
// 			'fields' => [
// 				'langue_id' => 1,
// 				'category_id' => 1,
// 				'title' => 'Ma quatrieme article sur informatique'
// 			],
// 			'conditions' => ['id' => '?', 'is_actived' => '?']
// ], [33,1]);

// echo 'Le row count est : '.$db->rowCount();

// var_dump($update1);

//////////////////////////////////////////////////////////////////////////////////////////////
										//DELETE()//
/////////////////////////////////////////////////////////////////////////////////////////////
//$delete = $db->deleteById(35);

// $delete = $db->delete([
// 		'id' => 35,
// 		'is_actived' => 1
// 	]);

//var_dump($delete);
//////////////////////////////////////////////////////////////////////////////////////////////
										//FIND()//
/////////////////////////////////////////////////////////////////////////////////////////////

// $posts = $db->find([
// 		'table'      => ['articles' => 'art'],
// 		'fields'     => ['art.id', 'art.title','art.description', 'cat.name '],
// 		'joins'      => [
// 						'tables'    => ['categories'],
// 						'alias'     => ['cat'],
// 						'type'      => ['LEFT'],
// 						'condition' => ['cat.id' => 'art.category_id']
// 		],
// 		'conditions' => ['category_id' => '?'],
// 		'order' => ['art.id' => 'DESC']
// 	], ['2']
// );
// echo 'Le row count est : '.$db->rowCount();
// var_dump($posts);

//////////////////////////////////////////////////////////////////////////////////////////////
										//All()//
/////////////////////////////////////////////////////////////////////////////////////////////

$posts = $db->all(true,'4', [
		'order' => ['id' => 'DESC']
]);

//echo 'Le row count est : '.$db->rowCount().'<br/>';
echo $db->getSQL();

foreach($posts as $post): ?>
	<h5>ID : <?= $post->id; ?></h5>
	<h1><?=$post->title ?></h1>
	<?= $post->description; ?>
	<hr>
<?php endforeach; 

//////////////////////////////////////////////////////////////////////////////////////////////
										//PAGINATION//
/////////////////////////////////////////////////////////////////////////////////////////////

	for($i=1;$i<=$db->_paginate_number;$i++):
		if($i==$db->_paginate_currentPage):?>

	<?=$i ?>

<?php else: ?>
	/ <a href="index.php?page=<?=$i ?>"><?=$i ?></a>

<?php endif;
endfor; ?>

