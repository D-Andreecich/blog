<?php

require_once 'sing_in.php';
require_once 'index.php';
?>

<html>
    <head>
        <title>blog</title>
		
	</head>
    <body>
        <?php if (!$_SESSION['logged']): ?> <!-- Если пользователь не залогинен -->
		<form method='post' action="">
                Login: <input type='text' name='login' >
                Password: <input type='password' name='password'>
				<!-- temp
				Name host: <input type='text' name='host'>
				-->
				Database Name : <input type='text' name='dbname'><br/>
                <input type='submit' value='Login' />
		</form>
		<?php else: // Пользователь залогинен успешно ?>
			<?php if ($loginSuccessfull || $_SESSION['logged']): // причем только что ?>
				<div style='color: green;'> Welcome <span style='color: blue;'><?php echo $login ?></span></div>
            <?php endif; ?>
				
				<form>
				<button type="submit" formmethod ="post" name="logout" value="true">Выйти</button>
				</form>
				</br>
				<!-- НАПИСАНИЕ ЗАПРОСА -->
				<form method='post' action="">
				Enter the query: <input type='textarea' name='query' ><br> <!-- value='<?php echo ($_POST[query])?>' -->
				<!--SELECT <input type='textarea' name='select'>
				FROM <input type='textarea' name='from'>
				WHERE <input type='textarea' name='query'><br>-->
				<input type='submit' value='Query' action=""/>
				</form >
				<!-- ДОБОВЛЕНИЕ ПОСТА  -->
					
				<?php if (isset($_POST[add_post])):	?>
				<form method='post' action="">
					Заголовок поста <input type='text' name='post_title'  maxlength="99">
					<br>
					Текст поста <input type='text' name='post_text'>
					<br>
					Теги <input type='text' name='tag_post' placeholder="tag1,tag2,...">

					<br>
					<button type="submit" >Добавить пост</button>
				</form>
				<?php else: ?>
				<form>
					<button type="submit" formmethod="post" name="add_post" value="true">Добавить пост</button>
				</form>
				<?php endif; ?>
        <?php endif; ?>

		<h1>Лента постов</h1>	
		<?php
		try {
			$dbh = new PDO("mysql:host=localhost;dbname=blog", 'D_Andreevich', '');
			$result = $dbh->query('SELECT * FROM `post` ORDER BY `post_update_datetime` desc, `post_create_datetime` DESC');
			$rows   = $result->fetchAll(); 
			//$dbh = null; temp
		} catch (PDOException $e) {
			print "Error!: " . $e->getMessage() . "<br/>";
			die();
		}
		foreach ($rows as $res) {
			
			
			$comment = $dbh->query("SELECT COUNT(comment_id) AS kol FROM comment WHERE post_id = $res[0]")->fetchAll(PDO::FETCH_COLUMN);
			$tag_array = $dbh->query("SELECT tag_title, post_text FROM tag, post_to_tag, post WHERE post_to_tag.post_id=post.post_id 
			AND post_to_tag.tag_id=tag.tag_id AND post_to_tag.post_id = $res[0]")->fetchAll(PDO::FETCH_COLUMN);
			$tag = '#'.implode(", #", $tag_array);
			?>
			<a href="/edit.php?id=<?php echo $res[0]?>"><h2><?php echo $res[post_title]?></h2></a>
			<?php
			echo '<br>
			<span class= "post_tag">Теги: ', $tag ,'</span>
			<hr>
			<p class = "post_text">', $res['post_text'] , '</p>
			<span class = "count_comment"> Количество комментариев: ', $comment[0] ,'</span>
			<span class = "post_create_datetime"> Время добавления: ', $res['post_create_datetime'] ,'</span>';
			if($res['post_update_datetime']){
			echo '<span class = "post_create_datetime"> Изменен : ', $res['post_update_datetime'] ,'</span>';
			}
		}
		?>
	</body>
</html>