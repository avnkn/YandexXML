<?php
use yii\helpers\Html;
?>

<h1>Добавлена информация</h1>
<div class="row">
    <div class="col-md-4">
        <ul>
            <li><label>Url сайта</label>: <?= Html::encode($model->url) ?></li>
            <li><label>Регион</label>: <?= Html::encode($model->lr) ?></li>
            <li><label>Запросы</label>: <?= Html::encode($model->key) ?></li>
        </ul>
    </div>
</div>
