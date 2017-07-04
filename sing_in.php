<?php
session_start();
//twig -----------
/*//---------- подгружаем и активируем авто-загрузчик Twig-а-------------
require_once 'Twig/Autoloader.php';
Twig_Autoloader::register();

try {
  // указывае где хранятся шаблоны
  $loader = new Twig_Loader_Filesystem('templates');
  // инициализируем Twig
  $twig = new Twig_Environment($loader);
  // подгружаем шаблон
  $template = $twig->loadTemplate('index.tmpl');
  // передаём в шаблон переменные и значения
  // выводим сформированное содержание
  echo $template->render(array(
    'name' => 'Clark Kent',
    'username' => 'ckent',
    'password' => 'krypt0n1te',
  ));
} catch (Exception $e) {
  die ('ERROR: ' . $e->getMessage());
}
//----------------------------------Twig----------------------------------*/
//----------- twig
//востановление данных из сесси

if (isset($_SESSION['login'])){
    $loginSuccessfull = true;
    $dbname   = $_SESSION['dbname'];
    $login = $_SESSION['login'];
    $password   = $_SESSION['password'];
}
else {
    $dbname   = "";
    $login = "";
    $password   = "";
}

//авторизация
if (isset($_POST['login']) && isset($_POST['password']) && isset($_POST['dbname'])){
    $dbname   = $_POST['dbname'];
    $login = $_POST['login'];
    $password   = $_POST['password'];
    try {
        $dbh = new PDO("mysql:host=localhost;dbname=$dbname", $login, $password);
		$loginSuccessfull = true;
		$_SESSION['logged'] = $loginSuccessfull;
    } catch (PDOException $e) {
        //$error = $e->getMessage();
		print "Error!: " . $e->getMessage() . "<br/>";
    }
}
//сохранение данных в сессию
if ($loginSuccessfull){
	$_SESSION['dbname'] = $dbname ;
    $_SESSION['login'] = $login ;
    $_SESSION['password'] = $password ;
	$sql = isset($_POST['query']) ? $_POST['query'] : "";
	if ($sql){
        try {
            $dbh = new PDO("mysql:host=localhost;dbname=$dbname", $login, $password);
            $result = $dbh->query($sql);
            $rows   = $result->fetchall(PDO::FETCH_BOTH);
			//var_dump($rows );
			//echo '<br><br>';
            if ($sql){
			    foreach($rows as $row) {
					 echo '<br>';
					 foreach($row as $key => $value){
						echo "[".$key."]=> |".$value."| \t";
					 }
					 echo '<br>';
				}
			}
			//-------------
			/*echo '<table border="1">';
			echo '<thead>';
			echo '<tr>';
			echo '<th>fio</th>';
			echo '<th>grup</th>';
			echo '<th>namenum</th>';
			echo '</tr>';
			echo '</thead>';
			echo '<tbody>';
			while($data = mysql_fetch_array($qr_result)){ 
			echo '<tr>';
			echo '<td>' . $data['fio'] . '</td>';
			echo '<td>' . $data['grup'] . '</td>';
			echo '<td>' . $data['namenum'] . '</td>';
			echo '</tr>';
			}
			echo '</tbody>';
			echo '</table>';*/
            //print_r($rows);
            //$dbh = null; temp
        } catch (PDOException $e) {
            print "Error!: " . $e->getMessage() . "<br/>";
            die();
        }
    }
}
//ДОБОВЛЕНИЕ ПОСТА
if ($_POST['tag_post'] and $_POST['post_title']and ($_POST['post_text']) and (empty($_POST['add_post']))){
	
	
        try {
            $dbh = new PDO("mysql:host=localhost;dbname=$dbname", $login, $password);
			$t = $dbh->query("SELECT post_title FROM post")->fetchAll(PDO::FETCH_ASSOC);
			$tru = 1;
			foreach ($t as $tr){
				if ($tr['post_title'] == $_POST['post_title'])
					$tru = 0;
			}
			if($tru){
			$dbh->beginTransaction();
			$post_add = $dbh->prepare("INSERT INTO post (post_title, post_text, post_create_datetime) VALUES (:post_title, :post_text, NOW())");
			$post_add = $dbh->prepare("INSERT INTO post (post_title, post_text, post_create_datetime) VALUES (:post_title, :post_text, NOW())");
			$post_add->bindParam(':post_title', $_POST['post_title']);
			$post_add->bindParam(':post_text', $_POST['post_text']);			
			$post_add->execute();
			$t_to_P["post_id"] = $dbh->lastInsertId();
			
			
			foreach (explode(",", $_POST['tag_post']) as $tag_temp){
				$tag_add = $dbh->prepare("INSERT INTO tag (tag_title) VALUES (:tag_title)");
				$tag_add->bindParam(':tag_title', $tag_temp);
				$tag_add->execute();	
				$t_to_P["tag_temp"] [] = $dbh->lastInsertId();
			}
			$dbh->commit();
			//teg_to_post
			foreach ($t_to_P['tag_temp'] as $tmp){
				echo '<br>';
				$tag_to_post = $dbh->prepare("INSERT INTO `post_to_tag` (`post_id`, `tag_id`) VALUES (:post_id, :tag_id)");
				$tag_to_post->bindParam(':post_id', $t_to_P["post_id"]);
				$tag_to_post->bindParam(':tag_id', $tmp);
				$tag_to_post->execute();
			}

			echo "Успешно добавленно";
			}else
			echo "Запись уже существует!";
			
		} catch (PDOException $e) {
			$dbh->rollBack();
            print "Error!: " . $e->getMessage() . "<br/>";
            die();
        }
}
//выход
if (isset($_POST['logout']) and $_POST['logout'] == 'true'):
        $_SESSION['logged'] = false; //если пользователь нажал Выйти, то в мы сохраняем это в сессию
		$loginSuccessfull = false;
		$_POST['logout'] = false;
		session_destroy();
endif;
?>
