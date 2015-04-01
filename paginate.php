<?php
require 'vendor/autoload.php';

use \oop\Core\Table\Table;

$db = new Table();
$db->table = 'articles';

$posts = $db->all(true,'4'); // appel au function all; and active pagination with param true

$db->getSQL(); ?>

Nombre ligne : <?=$db->_paginate_CountNbrAll; ?><br/>
Nombre Pagination : <?=$db->_paginate_number; ?><br/>

<?php foreach($posts as $post): ?>
	<?= $post->id; ?>
	<h1><?=$post->title ?></h1>
	<?= $post->description; ?>
	<hr>
<?php endforeach; ?>

<?php for($i=1;$i<=$db->_paginate_number;$i++):
		if($i==$db->_paginate_currentPage):?>

	<?=$i ?>

<?php else: ?>
	/ <a href="index.php?page=<?=$i ?>"><?=$i ?></a>

<?php endif;
endfor; ?>


