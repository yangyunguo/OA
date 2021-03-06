<?php 

require_once('PublicMethod.php');
/**
 * 测试流程：
 * 1、清空工时，并设置工时
 *     update moa_user set contribution = 0, totalPenalty = 0;
 *     update moa_worker set worktime = 40, penalty = 10;
 * 2、删除生成的excel目录下的文件(因为未超过15，不允许再次清算)
 *     /var/www/html/OA/mmsoa/assets/excel
 * 3、完成
 *
 * 查看数据库工时的语句
 *
 *    select contribution, totalPenalty, worktime, penalty from moa_worker natural join moa_user where state = 0 \G
*/
class TableExport extends CI_Controller { 

    function __construct() { 
        parent::__construct();
        $this->load->model('Moa_user_model');
        $this->load->model('Moa_worker_model');
        $this->load->helper(array('form', 'url'));
        $this->load->library('session');
        $this->load->helper('cookie');

        $this->load->library('PHPExcel'); 
        $this->load->library('PHPExcel/IOFactory'); 
        
        $this->load->helper('file');
        $this->load->helper('download');
    } 

    function getAllFile() {
        $all_files = get_filenames('./assets/excel/');
        return $all_files;
    }

    function isExist($filename) {
        $all_files = TableExport::getAllFile();
        if(in_array($filename, $all_files)) 
            return true;
        return 0;
    }

    /**
     * 下载一键清算之后的excel文件
     * @return 成功，则直接下载文件
     */
    public function getLastExcel() {
        $all_files = TableExport::getAllFile();
        rsort($all_files); //对日期进行排序，$all_files[0]为最新的日期
        if(count($all_files) != 0) {
            $filename = $all_files[0]."";
            $name = './assets/excel/'.$filename;
            force_download($name, NULL);
        }
    }

    public function getExcel($filename) {
        $name = './assets/excel/'.$filename;
        if(isExist($filename)) {
            force_download($name, NULL);
        } else {
            echo json_encode(array("status" => FALSE, "msg" => "获取文件失败，文件不存在"));
            return;
        }
    }

    function createExcel($user_list) {
        date_default_timezone_set('PRC');
        $now_time = date('Y-m-d');
        $excelname = $now_time.".xls";

        if(TableExport::isExist($excelname)) { /*文件已经存在，不允许再次覆盖*/
            echo json_encode(array("status" => FALSE, "msg" => "创建excel失败，文件已经存在"));
            return ;
        }


        $objPHPExcel = new PHPExcel(); 

        $objPHPExcel->getProperties()->setTitle("MOA_Salary");   //标题

        $col    = array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L");
        $head   = array("序号", "校区", "工作单位", "工作门", "学号", "姓名", "原本工时", "扣除工时", "本月实发工时", "实发金额", "账号", "联系电话");

        for($i = 0; $i < count($col); $i++) { /*设置表头*/
            $objPHPExcel->getActiveSheet()->setCellValue($col[$i]."1",  $head[$i]); 
        }
 
        for($i = 2; $i <= count($user_list)+1; $i++) {
            $one_user = $user_list[$i-2];
            $salary = 0;
            if($one_user['real_worktime'] > 0)
                $salary = PublicMethod::cal_salary($one_user['real_worktime']);
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $i,  $i - 1);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $i,  "东校区");
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $i,  "网络中心");
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $i,  "多媒体室");
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $i,  $one_user['studentid']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $i,  $one_user['name']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $i,  $one_user['worktime']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . $i,  $one_user['real_penalty']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . $i,  $one_user['real_worktime']);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . $i,  $salary);
            $objPHPExcel->getActiveSheet()->setCellValue('K' . $i,  $one_user['creditcard']." ");
            $objPHPExcel->getActiveSheet()->setCellValue('L' . $i,  $one_user['phone']);
        }
        //             ("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L");
        $width  = array(  5,   8,  10,  10,  10,  15,  10,  10,  15,  10,  25,  15);

        for($i = 0; $i < count($col); $i++) {   /*设置列宽度*/
            $objPHPExcel->getActiveSheet()->getColumnDimension($col[$i])->setWidth($width[$i]);;
        }

        for($i = 0; $i < count($col); $i++) {
            for($j = 1; $j <= count($user_list) + 1; $j++) {
                $objPHPExcel->getActiveSheet()->getStyle($col[$i].$j."")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            }
        }
        $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
        $objWriter->save("./assets/excel/".$excelname); 
    }

    function writeBack($user_list) {
        $status = $this->Moa_worker_model->update_all_worktime($user_list);
        if($status == TRUE) 
            return 1;
        return 0;
    }

    function dealAll($user_list) {
        foreach($user_list as &$one_user) {
            $w  = $one_user['worktime'];
            $p  = $one_user['penalty'];
            $r  = $w - $p;      //实际发放的工时
            $rp = 0;            //实际扣除的工时

            if($w >= $p)        /*扣除后有剩余*/
                $rp = $p;
            else
                $rp = $w;

            if($r >= 40)        /*大于40个工时，只发放40*/
                $r = 40;

            if($r <= 0)         /*小于0则只做清算，不发放*/
                $r = 0;

            $one_user['real_worktime']          = $r;
            $one_user['real_penalty']           = $rp;
            $one_user['update_worktime']        = $w - $r - $rp;
            $one_user['update_penalty']         = $p - $rp;
            $one_user['update_contribution']    = $one_user['contribution'] + $r;
            $one_user['update_totalPenalty']    = $one_user['totalPenalty'] + $rp;

        }
        if (TableExport::writeBack($user_list)) {
            TableExport::createExcel($user_list);
            echo json_encode(array("status" => TRUE, "msg" => "清算成功"));
            return;
        } else {
            echo json_encode(array("status" => FALSE, "msg" => "清算工时失败, 写入数据库失败"));
            return;
        }
    }

    function lessThanHalfMonth() {

        $all_files = TableExport::getAllFile();
        if(count($all_files) == 0)
            return false;

        rsort($all_files); //对日期进行排序，$all_files[0]为最新的日期

        date_default_timezone_set('PRC');
        $time   = date('Y-m-d');

        $startdate  = $time."";

        //截取文件的日期, 如2016-10-30.xls, 则取2016-10-30
        $enddate    = $all_files[0]."";
        $enddate    = substr($enddate, 0,10);

        $nowdate  = strtotime($startdate);
        $filedate    = strtotime($enddate);

        $days       = round(($nowdate-$filedate)/86400);

        if($days <= 15) 
            return true;
        return false;
    }

    
    /**
     * 全员工时一键清算
     * @return 成功，则创建excel文件, excel名字为创建的日期
     * 
     */
    public function calculate() {
        if (isset($_SESSION['user_id'])) {
            // 检查权限: -1-离职人员 0-普通助理 1-组长 2-负责人助理 3-助理负责人  6-超级管理员
            if ($_SESSION['level'] != -1 && $_SESSION['level'] != 0 && $_SESSION['level'] != 1 &&
                $_SESSION['level'] != 2 && $_SESSION['level'] != 3 && $_SESSION['level'] != 6
            ) {
                // 提示权限不够
                PublicMethod::permissionDenied();
            }

            if(TableExport::lessThanHalfMonth()) {
                echo json_encode(array("status" => FALSE, "msg" => "清算失败，距离上次清算时间不足15天"));
                return;
            }

            $res        = $this->Moa_worker_model->get_by_state(0);
            $user_list  = array();

            foreach ($res->result() as $row) {
                $user_obj   = array();

                $user_obj['uid']            = $row->uid;
                if($user_obj['uid'] == 198)
                    continue;
                $user_obj['wid']            = $row->wid;

                /*工时信息*/
                $user_obj['contribution']   = $row->contribution;
                $user_obj['totalPenalty']   = $row->totalPenalty;
                $user_obj['worktime']       = $row->worktime;
                $user_obj['penalty']        = $row->penalty;

                /*身份信息*/
                $user_obj['studentid']      = $row->studentid;
                $user_obj['name']           = $row->name;
                $user_obj['phone']          = $row->phone;
                $user_obj['school']         = $row->school;
                $user_obj['address']        = $row->address;
                $user_obj['creditcard']     = $row->creditcard;

                array_push($user_list, $user_obj);
            }
            TableExport::dealAll($user_list);
        } else {
            // 未登录的用户请先登录
            PublicMethod::requireLogin();
        }
    }


} 
?> 