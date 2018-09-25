<?php
namespace app\index\controller;

use app\index\model\Question_bank;
use app\index\model\User_record;
use think\Controller;
use think\Db;
use think\facade\Request;


class Index  extends Controller
{
    public function index()
    {
        return $this->view->fetch('Index/index',['title'=>'标题']);
    }
    /********************************************
     * @purpose  使用CSV文件导入题目
     * @date 2018/9/18 11:42
     * @param
     * @return
     *******************************************/
    public function doImportQuestion()
    {
        if (Request::isAjax()) {
            $file = Request::file('formcsv');
            // 移动到框架应用根目录/public/uploads/ 目录下
            if($file){
                $info = $file->move('uploads/');
                if($info){
                    $myFile = fopen("uploads/".$info->getSaveName(), "r") or die('unable to opten file');

                    while (!feof($myFile)){
                        $topic_string =fgets($myFile);//一个题目内容字符串
                        $topicArr = explode(',', $topic_string);
                        if (count($topicArr) < 6) {//如果CSV文件格式不严格,则删除不能记为一题的部分
                            $topicArr = null;
                            exit();
                        }
//                        dump($topicArr);
//                        die;
//                        $question = new Question_bank;
                         Question_bank::create([
                            'question_type'=>$topicArr['0'],
                            'question_stem'=>$topicArr['1'],
                            'question_option'=>$topicArr['2'],
                            'question_option_num'=>$topicArr['3'],
                            'question_answer'=>$topicArr['4'],
                            'question_describe'=>$topicArr['5']
                        ]);
//                        $question->save();
                    }

                }else{
                    // 上传失败获取错误信息
                    echo $file->getError();
                }
            }
        }


    }
    /********************************************
     * @purpose  随机的在题库中抽题组卷
     * @date 2018/9/18 11:43
     * @param $randomNumA int A题库选择题目数量
     * @return  $res 二维数组
     *******************************************/
    public function doRandomChooseQuestion($randomNumA=0,$randomNumB=0,$randomNumC=0,$randomNumD=0)
    {
        $randomNumA = 10;
        $res[] = Db::query("SELECT id FROM zhi_question_bank ORDER BY rand() LIMIT :number",['number'=>$randomNumA]);
//        $res[] = Db::query("SELECT id FROM zhi_question_bankB ORDER BY rand() LIMIT :number",['number'=>$randomNumB]);
//        $res[] = Db::query("SELECT id FROM zhi_question_bankC ORDER BY rand() LIMIT :number",['number'=>$randomNumC]);
//        $res[] = Db::query("SELECT id FROM zhi_question_bankD ORDER BY rand() LIMIT :number",['number'=>$randomNumD]);

        return $res;
    }
    /********************************************
     * @purpose  把随机出好的题目发给前端
     * @date 2018/9/18 11:44
     * @param
     * @return
     *******************************************/
    public function doSetQuestion()
    {
        $strQuestion = '';
        $res = $this->doRandomChooseQuestion(10,0,0,0);
         foreach ($res as $key => $value) {
              foreach ($value as $kkey => $vvalue) {
                  $questions[] = $vvalue['id'];

                  $question = Question_bank::where('id',$vvalue['id'])->find();
//                  $question = Question_bank::get($vvalue['id']);
//                  dump($question);
//                  dump('------------');
//                  echo $question;
//                  echo '<br>';
              }
              dump($questions);
              $strQuestion .= implode(',',$questions);
              dump($strQuestion);

         }
        dump($strQuestion);

        User_record::create([
            'uid'=>1,
            'exam_name'=>'',
            'start_time'=>0,
            'end_time'=>0,
            'questions'=> $strQuestion,
            'answers'=>'',
            'score'=>0,
            'is_submit'=>0
        ]);



    }
    /********************************************
     * @purpose  给用户判分
     * @date 2018/9/18 14:32
     * @param 
     * @return 
     *******************************************/
    public function doScoreToUser()
    {

    }


}
