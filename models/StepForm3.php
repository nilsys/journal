<?php
/**
 * Created by PhpStorm.
 * User: xuant
 * Date: 10/6/2018
 * Time: 11:14 PM
 */

namespace app\models;


use yii\base\Model;

class StepForm3 extends Model
{
    public $currency;
    public $method;
    public function rules()
    {
        //return parent::rules(); // TODO: Change the autogenerated stub
        return[
            [['method'],'required'],
            //[['additional_materials'], 'file','skipOnEmpty' => false,'extensions' => 'docx',],

        ];
    }
}