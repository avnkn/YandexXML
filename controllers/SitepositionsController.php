<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\data\Pagination;
use yii\helpers\Html;
use app\models\Site;
use app\models\Keys;
use app\models\Positions;
use app\models\ParseSite;
use app\models\Create;
use app\models\Update;

class SitepositionsController extends Controller
{
    public function actionIndex()
    {
        $request = Yii::$app->request;
        $get = $request->get();
        $post = $request->post();
        $abs = $request->getAbsoluteUrl();

        $query = Site::find();
        $model = new ParseSite();

        $pagination = new Pagination([
            'defaultPageSize' => 10,
            'totalCount' => $query->count(),
        ]);

        $sites = $query->orderBy('host')
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            // данные в $model удачно проверены

            // делаем что-то полезное с $model ...
            return $this->render('index', [
                'sites' => $sites,
                'pagination' => $pagination,
                'model' => $model
            ]);
        } else {
            // либо страница отображается первый раз, либо есть ошибка в данных
            return $this->render('index', [
                'sites' => $sites,
                'pagination' => $pagination,
                'model' => $model,
                'get' => $get,
                'post' => $post,
                'abs' => $abs
            ]);
        }
    }

    public function actionCreate()
    {
        $model = new Create();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            // данные в $model удачно проверены

            // делаем что-то полезное с $model ...
            Yii::$app->db->createCommand()->insert('site', [
                'host' => Html::encode($model->url),
                'lr' => Html::encode($model->lr),
            ])->execute();
            $Site = Yii::$app->db->createCommand('SELECT id FROM {{site}} WHERE host = :url')
            ->bindValue(':url', Html::encode($model->url))->queryOne();
            $idSite = $Site['id'];
            $keys = explode(PHP_EOL, Html::encode($model->key));
            foreach ($keys as $key) {
                Yii::$app->db->createCommand()->insert('keys', [
                    'site_id' => (int) $idSite,
                    'key' => trim(Html::encode($key))
                ])->execute();
            }
            return $this->render('create-confirm', ['model' => $model]);
        } else {
            // либо страница отображается первый раз, либо есть ошибка в данных
            return $this->render('create', ['model' => $model]);
        }
    }

    public function actionDelete()
    {
        $request = Yii::$app->request;
        $post = $request->post();
        $sitesDel = $keysDel = $positionsDel = 0;
        if ($request->method == 'DELETE' && array_key_exists('ParseSite', $post)) {
            $id = (int) $post['ParseSite']['id'];
            $idKeys = Yii::$app->db
                ->createCommand("SELECT [[id]] FROM {{keys}} WHERE site_id = $id")
                ->queryAll();
            foreach ($idKeys as $idKey) {
                $positionsDel += Yii::$app->db->createCommand()
                    ->delete('positions', "keys_id = {$idKey['id']}")->execute();
            }
            $keysDel = Yii::$app->db->createCommand()->delete('keys', "site_id = $id")->execute();
            $sitesDel = Yii::$app->db->createCommand()->delete('site', "id = $id")->execute();
        }
        return $this->render('delete', [
            'sitesDel' => $sitesDel,
            'keysDel' => $keysDel,
        ]);
    }

    public function actionUpdate()
    {
        $model = new Update;
        $request = Yii::$app->request;
        $post = $request->post();
        $model->load(Yii::$app->request->post());
        if ($request->method == 'UPDATE' && array_key_exists('ParseSite', $post)) {
            $id = (int) $post['ParseSite']['id'];
            $model->id = $id;
            $siteInfo = Yii::$app->db
                ->createCommand("SELECT [[host]], [[lr]], [[active]]  FROM {{site}} WHERE id = $id")
                ->queryOne();
            $model->url = $siteInfo['host'];
            $model->lr = $siteInfo['lr'];
            $keyInfo = Yii::$app->db
                ->createCommand("SELECT [[key]] FROM {{keys}} WHERE site_id = $id")
                ->queryAll();
            $keyArr = array_reduce($keyInfo, function ($acc, $value) {
                $acc[] = $value['key'] . PHP_EOL;
                return $acc;
            }, []);
            $keyStr = implode('', $keyArr);
            $model->key = $keyStr;
            return $this->render('update', [
                'model' => $model,
            ]);
        } elseif ($request->method == 'UPDATE' && array_key_exists('Update', $post)) {
            $id = (int) $post['Update']['id'];
            $host = Html::encode($post['Update']['url']);
            $lr = (int) Html::encode($post['Update']['lr']);
            $key = trim(Html::encode($post['Update']['key']));
            Yii::$app->db->createCommand("UPDATE {{site}} SET [[host]] = '$host' WHERE [[id]] = $id")
                ->execute();
            Yii::$app->db->createCommand("UPDATE {{site}} SET [[lr]] = $lr WHERE [[id]] = $id")
                ->execute();

            $keysArr = explode(PHP_EOL, $key);
            foreach ($keysArr as $k => $keyNew) {
                $keyNewTrim[$k] =  trim($keyNew);
                if (!Yii::$app->db->createCommand("SELECT * FROM {{keys}} WHERE [[key]] = '{$keyNewTrim[$k]}'")
                ->queryOne()) {
                    Yii::$app->db->createCommand()->insert('keys', [
                        'site_id' => $id,
                        'key' => trim(Html::encode($keyNew))
                    ])->execute();
                }
            }
            $keysArrAll = Yii::$app->db->createCommand("SELECT * FROM {{keys}} WHERE [[site_id]] = '$id'")
                ->queryAll();
            foreach ($keysArrAll as $keyAll) {
                if (!in_array($keyAll['key'], $keyNewTrim)) {
                    Yii::$app->db->createCommand()->delete('positions', "keys_id = {$keyAll['id']}")->execute();
                    Yii::$app->db->createCommand()->delete('keys', "id = {$keyAll['id']}")->execute();
                }
            }
            return $this->render('update-confirm', [
                'model' => $model,
            ]);
        }
    }

    public function actionPositions()
    {
        $astTree = $this->getAstTree();
        $result = $this->renderAstTree($astTree);
        return $this->render('positions', [
            'result' => $result
        ]);
    }

    public function actionGetPositions()
    {
        $get = Yii::$app->request->get();
        if (array_key_exists('id', $get)) {
            $id = (int) $get['id'];
        }
        $urlArr = Yii::$app->db
            ->createCommand("SELECT [[host]], [[lr]] FROM {{site}} WHERE id = $id")
            ->queryOne();
        $url = (explode("//", $urlArr['host']))[1];
        $lr = $urlArr['lr'];
        $time = time();

        $keyInfo = Yii::$app->db
            ->createCommand("SELECT [[id]], [[site_id]], [[key]] FROM {{keys}} WHERE site_id = $id")
            ->queryAll();
        foreach ($keyInfo as $key => $value) {
            $res = $this->yandexXML($value['key'], $url, $lr);
            if ($res['status']) {
                $pos = count($res['result']);
            } else {
                $pos = 0;
            }
            Yii::$app->db->createCommand()->insert('positions', [
                'date' => $time,
                'keys_id' => $value['id'],
                'position' => $pos
            ])->execute();
        }

        $astTree = $this->getAstTree();
        $result = $this->renderAstTree($astTree);
        return $this->render('positions', [
            'result' => $result,
            'res' => $res
        ]);
    }

    private function getAstTree()
    {
        $result=[];
        $rowsSite = (new \yii\db\Query())
            ->select(['id', 'host', 'lr'])
            ->from('site')
            //->where(['id' => "2"])
            ->limit(10)
            ->all();

        foreach ($rowsSite as $keySite => $valueSite) {
            $rowsKey = (new \yii\db\Query())  //SELECT id,`key` FROM `keys` WHERE site_id = $id";
                ->select(['id', 'key'])
                ->from('keys')
                ->where(['site_id' => $valueSite['id']])
                ->all();
            $valueSite['type'] = "site";
            $resultPosition = [];
            foreach ($rowsKey as $keyKey => $valueKey) {
                $rowsPosition = (new \yii\db\Query())
                    ->select(['id', 'date', 'keys_id', 'position'])
                    ->from('positions')
                    ->where(['keys_id' => $valueKey['id']])
                    ->all();
                $valueKey['type'] = 'key';
                $resultValue =[];
                foreach ($rowsPosition as $keyPosition => $valuePosition) {
                    $valuePosition['type'] = 'position';
                    $valuePosition['children'] = null;
                    $resultValue[$keyPosition] = $valuePosition;
                }

                $valueKey['children'] = $resultValue;
                $resultPosition[$keyKey] = $valueKey;
            }

            $valueSite['children'] = $resultPosition;
            $result[$keySite]=$valueSite;
        }
        return $result;
    }

    private function renderAstTree($astTree)
    {
        $iterSite = function ($value) {
            $url = explode("//", $value['host']);

            $str[] = "<h1>{$url[1]}</h1>";
            $str[] = "<table class='table table-striped'>";
            $str[] = "<tr>";
            $str[] = "<th>№</th>";
            $str[] = "<th>Фраза</th>";
            if (isset($value['children'][0]['children'])) {
                foreach ($value['children'][0]['children'] as $key => $date) {
                    $str[] = "<th>" . date("d.m.y", $date['date']) . "</th>";
                }
            }
            $str[] = "</tr>";
            $str[] = $this->renderAstTree($value['children']);
            $str[] = "</table>";
            $strResult = implode('', $str);
            return $strResult;
        };
        $iterKey = function ($value) {
            $str[] = "<tr>";
            $str[] = "<td>{$value['id']}</td>";
            $str[] = "<td>{$value['key']}</td>";
            if (!empty($value['children'])) {
                $str[] = $this->renderAstTree($value['children']);
            }
            $str[] = "</tr>";
            $strResult = implode('', $str);
            return $strResult;
        };
        $iterPosition = function ($value) {
            //$str = "<td>{$value['position']}(" . date("d.m.y", $value['date']) . ")</td>";
            $str = "<td>{$value['position']}</td>";
            return $str;
        };
        $iters = [
            'site'      => $iterSite,
            'key'       => $iterKey,
            'position'  => $iterPosition
        ];
        $funcArrayReduce = function ($item, $value) use ($iters) {
            $item[] = $iters[$value['type']]($value);
            return $item;
        };

        $arrResult = array_reduce($astTree, $funcArrayReduce);
        $strResult = implode('', $arrResult);
        return $strResult;
    }

    private function yandexXML($key_query, $url_site, $lr_site)
    {
        $configXML = require __DIR__ . "/../config/yandex_XML.php";
        $url_site = str_replace("http://", "", $url_site);
        $url_site = str_replace("https://", "", $url_site);
        $user = "&user=" . $configXML['user'];
        $key  = "&key=" . $configXML['key'];
        $query = "&query=" . urlencode("$key_query");
        $lr = "&lr=" . "$lr_site";
        $l10n  = "&l10n=" . "ru";
        //$page = "&page=" . "0";
        $page = "&page=";

        $str = $configXML['url'] . $user . $key . $query . $lr . $l10n . $page;
        $query_result_search = [];
        for ($i=0; $i < 10; $i++) {
            $str = $str . $i;
            $homepage = file_get_contents($str);
            sleep(1.05);
            // Создание объекта, экземпляра класса DomDocument
            $dom = new \DomDocument();
            //Загрузка XML из строки
            $dom->loadXML($homepage);

            $sdfssdf= $dom->saveXML();
            file_put_contents("log.xml", $sdfssdf, FILE_APPEND);
            // Получение коневого элемента
            $docs = $dom->getElementsByTagName("doc");
            if ($docs->length==0) {
                echo "Ошибка ответа от YandexXML;";
                exit;
            }
            foreach ($docs as $doc) {
                $doc_url = "";
                $doc_domain = "";
                $child_docs = $doc->childNodes;
                foreach ($child_docs as $child_doc) {
                    if ($child_doc->nodeName == "url") {
                        $doc_url = $child_doc->nodeValue;
                    }
                    if ($child_doc->nodeName == "domain") {
                        $doc_domain = $child_doc->nodeValue;
                    }
                }
                $query_result_search[] = ['domain' => $doc_domain, 'url' => $doc_url];
                if ($url_site == $doc_domain) {
                    return ['status' => true, 'result' => $query_result_search];
                }
            }
        }
        unset($dom);
        return ['status' => false, 'result' => $query_result_search];  //[host] => url
    }
}
