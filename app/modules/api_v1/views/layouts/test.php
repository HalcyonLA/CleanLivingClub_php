<?php
/**
 * @author Aleksandr Mokhonko
 * Date: 04.12.15
 *
 * @var \yii\web\View $this
 * @var string $content
 */
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;
AppAsset::register($this);
?>
<?php $this->beginPage();?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Test Api</title>
	<?php $this->head(); ?>
	<?php /*
	<link href="<?php echo Yii::$app->request->baseUrl; ?>/public/lib/bootstrap-4.0.0-alpha/dist/css/bootstrap.min.css" rel="stylesheet" type="text/css">
	<script src="<?php echo Yii::$app->request->baseUrl; ?>/public/js/jquery-2.1.4.min.js" type="text/javascript"></script>
	<script src="<?php echo Yii::$app->request->baseUrl; ?>/public/lib/bootstrap-4.0.0-alpha/dist/js/bootstrap.min.js" type="text/javascript"></script>
 	*/
	?>
</head>

<body>
<?php $this->beginBody();?>
<?php echo $content; ?>
<?php $this->endBody();?>
</body>
</html>
<?php $this->endPage();?>
