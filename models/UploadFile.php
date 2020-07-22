<?php
/**
 * Created by PhpStorm.
 * User: xuant
 * Date: 10/2/2018
 * Time: 9:08 PM
 */

namespace app\models;
use Yii;
use yii\base\ErrorException;
use yii\base\Model;
use yii\db\Exception;
use yii\helpers\Inflector;
use app\models\Post;
use yii\web\UploadedFile;
use app\models\ServicePrice;


class UploadFile extends Model
{

    public $upload_file;
    public $upload_file_editor;
    public $upload_file_editor_completed;
    public $pageCount = 0;

    public function rules()
    {
        //return parent::rules(); // TODO: Change the autogenerated stub
        $rules = [
            //[['upload_file'],'required'],
            [['upload_file','upload_file_editor'], 'safe'],
            [['upload_file'], 'file','extensions' => ['png','jpeg','jpg']],
            [['upload_file_editor'],'file','extensions' => ['doc','docx']],
            [['upload_file_editor_completed'],'file','extensions' => ['doc','docx']]
         ];
         return array_merge(parent::rules(),$rules);
       /*return[
             [['type_of_service','type_of_paper','subject_area','topic','paper_details','academic_level','currency','writer_level','customer_service','method','upload_file',
                 'urgency'],'required', 'on' => self::SCENARIO_STEP1],
             ['id_writer','validateId'],
             [['upload_file'], 'file','extensions' => 'docx',],
             [['number_of_page','id_writer'],'integer'],
         ];*/
    }
    public function attributeLabels()
    {
        return [

        ];
    }

    public function UploadFile()
    {
        if ($this->upload_file) {
            $randomString = Yii::$app->getSecurity()->generateRandomString(20);
            $file = $randomString . '.' . $this->upload_file->extension;
            if (Yii::$app->user->identity->getImage()!= null)
            {
                if (file_exists(Yii::$app->basePath . '/web/' .'upload_img_user/'. Yii::$app->user->identity->getImage()))
                {
                    unlink(Yii::$app->basePath . '/web/' . 'upload_img_user/' . Yii::$app->user->identity->getImage());
                }
            }
            if ($this->upload_file->saveAs('upload_img_user/' . $file)) {
                if($user = User::findIdentity(Yii::$app->user->getId())) {
                    $user->Image = $file;
                    if ($user->save()) {
                        return true;
                    }
                }
                else
                {
                    $this->addError('myFile', 'Unable to save the uploaded file');
                }
            } else {
                $this->addError('myFile', 'Unable to save the uploaded file');
            }
        }
        return false;
    }
    public function UploadFile_Editor($id_editor)
    {
        if ($this->upload_file_editor) {
            $randomString = Yii::$app->getSecurity()->generateRandomString(20);
            $file = $randomString . '.' . $this->upload_file_editor->extension;
            /*if (Yii::$app->user->identity->getImage()!= null)
            {
                if (file_exists(Yii::$app->basePath . '/web/' .'upload_file_info_editor/'. Yii::$app->user->identity->getImage()))
                {
                    unlink(Yii::$app->basePath . '/web/' . 'upload_file_info_editor/' . Yii::$app->user->identity->getImage());
                }
            }*/
            if ($this->upload_file_editor->saveAs('upload_file_info_editor/' . $file)) {
                if ( $editor = Editor::findOne(['id'=>$id_editor])) {
                    $editor->File_Info_Editor = $file;
                    if ($editor->save()) {
                        return true;
                    }
                }
                else
                {
                    $this->addError('myFile', 'Unable to save the uploaded file');
                }
            } else {
                $this->addError('myFile', 'Unable to save the uploaded file');
            }
        }
        return false;
    }
    public function UploadPost_Editor_Completed($id_post)
    {
        if ($this->upload_file_editor_completed) {
            $randomString = Yii::$app->getSecurity()->generateRandomString(20);
            $file = $randomString . '.' . $this->upload_file_editor_completed->extension;

            if ($this->upload_file_editor_completed->saveAs('upload_post_editor_completed/' . $file)) {
                $post = Post::find()->with(['editors'])->where(['id'=>$id_post])->limit(1)->one();
                if ($post != null) {
                    $post->File_Editor_Completed = $file;
                    $post->Date_Finish =  date('Y-m-d H:i:s');
                    $post->Status="Completed";
                    $post->Status_Sort= 3;
                    $post->editors->Order_Process =$post->editors->Order_Process - 1;
                    $post->editors->Completed_order += 1;
                    if ($post->save() && $post->editors->save()) {
                        return true;
                    }
                }
                else
                {
                    $this->addError('myFile', 'Unable to save the uploaded file');
                }
            } else {
                $this->addError('myFile', 'Unable to save the uploaded file');
            }
        }
        return false;
    }

    public function UploadFileOrder($upload_file)
    {
        $randomString = Yii::$app->getSecurity()->generateRandomString(20);
        $file = $randomString . '.' . $upload_file->extension;
        if(!$upload_file->saveAs('uploads_post/' .$file)){
            $this->addError('myFile','Unable to save the uploaded file');
        }
        //$this->file = $file;
        return $file;
    }


    public function PageCount_DOCX($upload_file) {
        //$pageCount = 0;

        $zip = new \ZipArchive();

        if($zip->open($upload_file->tempName) === true) {
            if(($index = $zip->locateName('docProps/app.xml')) !== false)  {
                $data = $zip->getFromIndex($index);
                $zip->close();
                $xml = new \SimpleXMLElement($data);
                $this->pageCount = $xml->Pages;
                //$this->number_of_page = $pageCount;
            }
            //$zip->close();
        }
        return $this->pageCount;
    }
}