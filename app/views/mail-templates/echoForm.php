<?php
/**
 * @author Aleksandr Mokhonko
 * Date: 30.08.16
 *
 * @var string $subject
 * @var integer $userId
 * @var integer $reportedUserId
 * @var integer $syncId
 * @var integer $chatId
 * @var integer $imageId
 * @var string $imageUrl
 * @var string $scenario
 */

use \yii\bootstrap\Html;

?>

<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?= $subject ?></title>
</head>
<body>
<strong><?= $subject ?></strong>
<div>
    <?php foreach ($data as $qa) : ?>
        <div style="font-size: 16px; font-weight: bold;">
            <?php echo $qa['q']; ?>
        </div>
        <div style="font-size: 14px;">
            <?php echo $qa['a']; ?>
        </div>
        <br />
        <br />
    <?php endforeach; ?>
</div>
</body>
</html>