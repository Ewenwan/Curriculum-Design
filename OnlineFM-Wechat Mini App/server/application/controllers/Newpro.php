<?php
/**
 * Created by PhpStorm.
 * User: 64980
 * Date: 2018/5/6
 * Time: 18:48
 */

defined('BASEPATH') OR exit('No direct script access allowed');

use QCloud_WeApp_SDK\Mysql\Mysql as DB;

class Newpro extends CI_Controller {
    public function index() {
        DB::getInstance();
        $raw = file_get_contents('php://input');
        $data = json_decode($raw,true);
        $member = $data['member'];
//        status状态 0:已创建项目(正常)   -1:权限不足   -2:同名项目已创建   -3:插入异常
        if(DB::select('user',['userName'],['userName' => $data['creatName'], 'root' => 1])== []) {
            return $this->json([
                'status' => -1,
                'message' => '用户权限不足',
            ]);
        }
//        检查项目名和创建者是否都相同
        else if(DB::select('project',['*'],['creatName' =>$data['creatName'], 'projectName' => $data['proName']])){
            return $this->json([
                'status' => -2,
                'message' => '同名项目已创建',
            ]);
        }
        else {
            DB::insert('project',['creatName' => $data['creatName'], 'projectName' =>$data['proName'] , 'status' => 0]);
            $newId=DB::row('project',['id'],['creatName' => $data['creatName'], 'projectName' =>$data['proName']]);
//            return $this->json([
//                'id' => $newId,
//            ]);
            foreach ($member as $key => $value) {
                if(DB::insert('pmember', ['pid' => $newId->id , 'member' => $value])) {
                    continue;
                }
                else {
                    return $this->json([
                        'status' => -3,
                        'message' => '插入异常',
                    ]);
                }
            }
            return $this->json([
                'status' => 0,
                'message' => '已创建新项目',
            ]);




        }
    }
}
