<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 03.03.16
 * Time: 13:29
 */

namespace app\components;


use yii\base\Component;
use Yii;

class PathTransformer extends Component
{
    public function transform($model, $columns = [])
    {
        unset($model['digits_id']);
        foreach ($columns as $c) {
            if (is_null($model[$c]) || empty($model[$c])) {
                $model[$c] = '';
                continue;
            }
            if (isset($model[$c]) && substr($model[$c], 0, 4) != 'http') {


                if (substr($model[$c], 0, 1) != '/') {
                    $model[$c] = '/' . $model[$c];
                }
                $protocol = 'http://';
                if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
                    $protocol = 'https://';
                }

                $model[$c] = DOMAIN_FULL . $model[$c];
            }
        }
        return $model;
    }

    public function absToLegasy($str) {
        return str_replace($_SERVER['DOCUMENT_ROOT'], '', $str);
    }

    public function nullFilter($modelAttributes)
    {
        foreach ($modelAttributes as &$attr) {
            if ($attr === null) {
                $attr = '';
                continue;
            }
            if ($attr === false || $attr == 'NO') {
                $attr = 0;
                continue;
            }
            if ($attr === true || $attr == 'YES') {
                $attr = 1;
                continue;
            }
        }

        return $modelAttributes;
    }
}