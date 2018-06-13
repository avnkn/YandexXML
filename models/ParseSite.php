<?php

namespace app\models;

use yii\base\Model;

class ParseSite extends Model
{
    public $url;
    public $id;
    public function rules()
    {
        return [
            [['url'], 'required']
        ];
    }
}
