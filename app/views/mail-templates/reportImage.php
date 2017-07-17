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
    <?php if ($scenario == \app\models\ReportsImages::SCENARIO_ADD_TS) : ?>
    <table>
        <tr><td>Reporting Media Type</td><td>TimesyncImage</td></tr>
        <tr><td>Reporting User ID</td><td><?= $userId ?></td></tr>
        <tr><td>Reported User ID</td><td><?= $reportedUserId ?></td></tr>
        <tr><td>Sync ID</td><td><?= $syncId ?></td></tr>
        <tr><td>Chat ID</td><td><?= $chatId ?></td></tr>
        <tr><td>Image ID</td><td><?= $imageId ?></td></tr>
        <tr><td>Image Url</td><td><?= $imageUrl ?></td></tr>
        <tr><td rowspan="2"><?= empty($imageUrl) ? '' : Html::img($imageUrl) ?></td></tr>
    </table>
    <?php endif; ?>

	<?php if ($scenario == \app\models\ReportsImages::SCENARIO_ADD_TT) : ?>
        <table>
            <tr><td>Reporting Media Type</td><td>TimetravelImage</td></tr>
            <tr><td>Reporting User ID</td><td><?= $userId ?></td></tr>
            <tr><td>Reported User ID</td><td><?= $reportedUserId ?></td></tr>
            <tr><td>Image ID</td><td><?= $imageId ?></td></tr>
            <tr><td>Image Url</td><td><?= $imageUrl ?></td></tr>
            <tr><td rowspan="2"><?= empty($imageUrl) ? '' : Html::img($imageUrl) ?></td></tr>
        </table>
	<?php endif; ?>
</div>
</body>
</html>