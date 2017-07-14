<?php
/**
 * @author Aleksandr Mokhonko
 * Date: 16.11.15
 *
 * @var array $data
 * @var array|bool $methodAttributes
 * @var array|bool $methodFiles
 * @var string $controllerName
 * @var string $methodName
 * @var string $nameAttributes
 * @var string $nameFiles
 */
use yii\helpers\Url;
?>

<div class="container">
	<h1><?php echo Yii::$app->name;?> Test Api</h1>
	<hr>
    <style>
        h2 {
            margin-top: 0;
        }
        .float-right {
            float: right;
        }
        .delete-button {
            cursor: pointer;
        }
		.line-margin {
			margin-bottom: 10px;
		}
    </style>
	<div>
		<h3><i>Dashboard</i></h3>
		<?php $url = '';?>
		<?php foreach($data as $controller):?>
			<div class="row line-margin">
				<div class="col-md-2">
					<span class="label label-danger"><?php echo ucfirst($controller['id']);?></span>
				</div>
				<div class="col-md-9">
					<?php if(!empty($controller['methods'])):?>
						<?php foreach($controller['methods'] as $method):?>
							<a style="margin-right: 5px;" href="<?php echo Url::toRoute([
								'', 'c' => $controller['id'], 'm' => $method['methodName']
								]
							);?>">
								<?php
									if($controller['id'] == $controllerName && $method['methodName'] == $methodName) {
										$class = 'label-warning';
										$url = $method['url'];
									}
									else {
										$class = 'label-primary';
									}
								?>

								<span class="label <?php echo $class;?>" title="<?php echo $method['url'];?>">
									<?php echo ucfirst($method['methodName']);?>
								</span>
							</a>
						<?php endforeach;?>
					<?php endif;?>
				</div>
			</div>
		<?php endforeach;?>
	</div>
	<hr>
	<div>
		<?php if(!empty($controllerName)):?>
			<?php if($methodAttributes !== null || $methodFiles !== null):?>
			<form action="<?php echo $url;?>" method="post" <?php echo !empty($methodFiles) ? 'enctype="multipart/form-data"' : '';?>>
				<h3>
					<i>Form</i>
					<?php if(!empty($methodFiles)):?>
						<input type="submit" value="Request POST" class="btn btn-info">
					<?php else : ?>
						<input type="button" id="send-request" value="Request AJAX" class="btn btn-info">
					<?php endif; ?>

				</h3>
				<fieldset><?php echo $url;?></fieldset>
				<label><input type="checkbox" name="testApi" checked="checked" value="1"> !JSON</label>
                <div id="responseData" class="well">Output data</div>
				<fieldset class="form-group row">
					<div class="col-md-5">
						<textarea class="form-control" name="<?php echo $nameAttributes;?>" rows="5" id="jsonTextArea"></textarea>
					</div>
				</fieldset>

				<div id="inputData">
				<?php foreach($methodAttributes as $attribute => $attributeData):?>
                    <?php if (is_array($attributeData[0])) : ?>
                        <div class="array-input well" data-array-label="<?php echo $attribute;?>">
                            <div class="array-input-title row">
                                <div class="col-md-10"><h2>Array for <?php echo $attribute;?></h2></div>
                                <div class="col-md-2"><button type="button" class="float-right btn btn-success btn-add-array-item">Add Row</button></div>
                            </div>
                            <div class="multi-fields-container">
                            <?php foreach ($attributeData[0] as $aData) :?>
                                <div class="form-group">
                                    <label class="sr-only" for="exampleInputAmount">Amount (in dollars)</label>
                                    <div class="input-group">
                                        <div class="input-group-addon delete-button">x</div>
                                        <input type="text" value="<?php echo $aData;?>" name="<?php echo $attribute;?>" class="form-control" placeholder="<?php echo $attribute;?>">
                                        <div class="input-group-addon">
                                            <?php echo empty($attributeData[1]) ? 'Optional' : '<span style="color: red;">Required</span>';?>
                                            <?php echo empty($attributeData[2]) ? '' : '//' . $attributeData[2];?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach;?>
                            </div>
                        </div>
                    <?php endif;?>
                    <?php if (!is_array($attributeData[0])) : ?>
                        <div class="single-input"  data-single-label="<?php echo $attribute;?>">
                        <fieldset class="form-group row">
                            <label for="<?php echo $attribute;?>" class="col-md-2 form-control-label"><?php echo $attribute;?></label>
                            <div class="col-md-5">
                                <input
                                        type="text"
                                        class="form-control form-control-md inputField"
                                        value="<?php echo $attributeData[0];?>"
                                        id="<?php echo $attribute;?>"
                                        name="<?php echo $attribute;?>"
                                >
                            </div>
                            <small class="text-muted">
                                <?php echo empty($attributeData[1]) ? 'Optional' : '<span style="color: red;">Required</span>';?>
                                <?php echo empty($attributeData[2]) ? '' : '//' . $attributeData[2];?>
                            </small>
                        </fieldset>
                        </div>
                    <?php endif;?>
				<?php endforeach;?>
				</div>
				<?php if(!empty($methodFiles)):?>
					<div>
						<?php foreach($methodFiles as $key => $file): ?>
							<label for="<?php echo $key;?>" class="col-md-2 form-control-label"><?php echo $key;?></label>
							<input type="file" name="<?php echo $key;?>">
						<?php endforeach;?>
					</div>
				<?php endif;?>
			</form>
			<?php else:?>
					<h2>Test api not found</h2>
			<?php endif;?>
		<?php endif;?>
	</div>
</div>

<script type="text/javascript" src="/js/api-help.js"></script>

