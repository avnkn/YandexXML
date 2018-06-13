<?php

namespace app\models;

use yii\base\Model;

class Create extends Model
{
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
