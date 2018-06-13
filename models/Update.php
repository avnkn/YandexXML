<?php

namespace app\models;

use yii\base\Model;

class Update extends Model
{
    public $id;
    public $url;
    public $lr;
    public $key;

    public function rules()
    {
        return [
            [['url', 'lr', 'key'], 'required']
        ];
    }
}
