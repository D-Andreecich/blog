<?php
require_once 'sing_in.php';

//----------------ВЫВОД------------------
try{
	$dbh = new PDO("mysql:host=localhost;dbname=$dbname;charset=UTF8", $login, $password);
	$rowsult = $dbh->query("SELECT * FROM post WHERE post_id = $_GET[id]"); 
	$rows   = $rowsult->fetch(); 

	$comment = $dbh->query("SELECT * FROM comment WHERE post_id = $rows[0]")->fetchAll(PDO::FETCH_ASSOC);

	$col_comment = $dbh->query("SELECT COUNT(comment_id) AS kol FROM comment WHERE post_id = $rows[0]")->fetchAll(PDO::FETCH_COLUMN);
	
	$tag_array = $dbh->query("SELECT tag.tag_id, tag.tag_title FROM tag, post_to_tag, post WHERE post_to_tag.post_id=post.post_id 
	AND post_to_tag.tag_id=tag.tag_id AND post_to_tag.post_id = $rows[0]")->fetchAll(PDO::FETCH_KEY_PAIR);
	$tag = implode(",", $tag_array);
}
catch (PDOException $e){
	echo "Sorry, most likely you are not logged in!".'<br>';
			?>
			<form method='post' action="">
					Login: <input type='text' name='login' >
					Password: <input type='password' name='password'>
					<!-- temp
					Name host: <input type='text' name='host'>
					-->
					Database Name : <input type='text' name='dbname'><br/>
					<input type='submit' value='Login' />
					<br>
					
			</form> 
			<form method="LINK" action="/index.php">
				<input type="submit" value="Return back!">
			</form>
			<?php
}

if($_POST['tag_post'] and $_POST['postTitle']and $_POST['post_text']){
	
	//Обновление записи
	try {
		$dbh = new PDO("mysql:host=localhost;dbname=$dbname;charset=UTF8", $login, $password);
			
		$dbh->beginTransaction();
		
		$post_add = $dbh->prepare("UPDATE `post` SET `post_title` = :post_title, `post_text` = :post_text, `post_update_datetime` = NOW() WHERE `post`.`post_id` = :post_id");
		$post_add->bindParam(':post_title', $_POST['postTitle']);
		$post_add->bindParam(':post_text', $_POST['post_text']);                    
		$post_add->bindParam(':post_id', $_GET[id]);                  
		$post_add->execute();
		
		//редактирование тегов 	
		if($tag != $_POST['tag_post']){
			if(trim($_POST['tag_post']) == ""){
				echo "YES";
				$sql_del = "DELETE tag FROM tag, post_to_tag, post WHERE post_to_tag.post_id=post.post_id AND post_to_tag.tag_id=tag.tag_id AND post_to_tag.post_id = :post_id";
				$tag_del = $dbh->prepare($sql_del);
				$tag_del->bindParam(':post_id', $_GET[id], PDO::PARAM_INT);   
				$tag_del->execute();
				echo "YES end del";
			}else{
				$tag_post = explode(",", trim($_POST['tag_post'])); //array
				$i=0;
				foreach($tag_array as $key=> $value){
					$tag_up = $dbh->prepare("UPDATE `tag` SET `tag_title` = :tag_title WHERE `tag`.`tag_id` = :tag_id");
					$tag_up->bindParam(':tag_title', $tag_post[$i++]);
					$tag_up->bindParam(':tag_id', $key);
					$tag_up->execute();
				}
				if($tag_post[$i]){
					while($tag_post[$i]){
						$tag_add = $dbh->prepare("INSERT INTO `tag` (`tag_title`) VALUES (:tag_title)");
						$tag_add->bindParam(':tag_title', $tag_post[$i++]);
						$tag_add->execute();    
						$t_to_P["tag_temp"][] = $dbh->lastInsertId();
						}
					//teg_to_post
					foreach ($t_to_P['tag_temp'] as $tmp){
						$tag_to_post = $dbh->prepare("INSERT INTO `post_to_tag` (`post_id`, `tag_id`) VALUES (:post_id, :tag_id)");
						$tag_to_post->bindParam(':post_id', $_GET[id]);
						$tag_to_post->bindParam(':tag_id', $tmp);
						$tag_to_post->execute();
					}
				}
			}
		}

		$dbh->commit();
		echo '<br>'."Успешно обновлено!";
		echo '<meta http-equiv="refresh" content="0; url=index.php">';
	} catch (PDOException $e) {
		print "Error!: " . $e->getMessage() . "<br/>";
		$dbh->rollBack();
		die();
	}
}
?>
<html>
<head>
	<title>edit</title>
</head>
<?php if($dbh): ?>
<body>
<br>
<h2 class = "post__title"> <?echo $rows['post_title']?> </h2>
<span class= "post_tag">Теги: <?echo$tag ?></span>
<hr>
<p class = "post_text"><?echo$rows['post_text']?></p>
<span class = "count_comment"> Количество комментариев: <?echo $col_comment[0]?></span>
<span class = "post_create_datetime"> Добавлен : <?echo$rows['post_create_datetime']?></span>

<?php if($rows['post_update_datetime']): ?>
<span class = "post_create_datetime"> Изменен : <?echo$rows['post_update_datetime']?></span>  
<?php endif;?>

<br>
<?php foreach($comment as $com){    ?>
<hr>
<p class="comment"> <?php echo $com['comment_text'] ?> </p>

<span class = "comment_username"> <?php echo $com['comment_username'] ?> </span>
<span class = "comment_datetime"> <?php echo $com['comment_datetime'] ?> </span>
<?php } ?>
<br>
<br>
<h2> Редактирование поста </h2>

<form method='post' action="">	<!-- /index.php -->
Заголовок поста <input type='text' name='postTitle'  maxlength="99" value="<?echo $rows['post_title']?>">
<br>
Текст поста <br>    
<textarea rows="7" cols="60" name='post_text' ><?echo $rows['post_text']?></textarea>
<br>
Теги <input type='text' name='tag_post' value="<?echo $tag?>">
<br>
<button type="submit" >Сохранить изменения</button>

</form>
<!--
	<pre><?php 
			  print_r($_SESSION); 
			  print_r($_POST); 
			  print_r($_GET); 
			  //отладочный вывод для понимания
		?>
	</pre>
</body>
-->
<?php endif;?> 
</html>