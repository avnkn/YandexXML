<?php
use yii\helpers\Html;
?>

<h1>Данные отредактированны</h1>
<div class="row">
    <div class="col-md-4">
        <?php
        if (isset($keysArrNewTest)) {
            echo "\n<br>keysArrNewTest\n<br>";
            var_dump($keysArrNewTest);
        }
        if (isset($keysArrNewTestStatus)) {
            echo "\n<br>keysArrNewTestStatus\n<br>";
            var_dump($keysArrNewTestStatus);
        }
        if (isset($keysArr)) {
            echo "\n<br>keysArr\n<br>";
            var_dump($keysArr);
        }
        ?>
        <ul>
            <li><label>Url сайта</label>: <?= Html::encode($model->url) ?></li>
            <li><label>Регион</label>: <?= Html::encode($model->lr) ?></li>
            <li><label>Запросы</label>: <?= Html::encode($model->key) ?></li>
        </ul>
        <p><a href="/sitepositions/index">Вернуться к списку сайтов</a></p>
    </div>
</div>
