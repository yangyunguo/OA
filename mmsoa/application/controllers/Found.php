<?php
/**
 * Created by PhpStorm.
 * User: zhong
 * Date: 2017/9/14
 * Time: 1:56
 */
header("Content-type: text/html; charset=utf-8");

require_once('PublicMethod.php');


class Found extends CI_Controller
{


    public function __construct()
    {
        parent::__construct();
        $this->load->model('Moa_user_model');
        $this->load->model('Moa_worker_model');
        $this->load->model('Moa_found_model');
        $this->load->helper(array('form', 'url'));
        $this->load->library('session');
        $this->load->helper('cookie');
    }

    public function index()
    {
        if (isset($_SESSION['user_id'])) {

            $data = array();
            $data['d_fid'] = array();
            $data['d_state'] = array();
            $data['d_fwid'] = array();
            $data['d_signuptime'] = array();
            $data['d_fdatetime'] = array();
            $data['d_fdescription'] = array();
            $data['d_fplace'] = array();
            $data['d_finder'] = array();
            $data['d_fcontact'] = array();
            $data['d_owid'] = array();
            $data['d_owner'] = array();
            $data['d_odatetime'] = array();
            $data['d_ocontact'] = array();
            $data['d_onumber'] = array();

            // 取所有助理的wid与name
            $common_worker = $this->Moa_user_model->get();

            for ($i = 0; $i < count($common_worker); $i++) {
                $uid_list[$i] = $common_worker[$i]->uid;
                $name_list[$i] = $common_worker[$i]->name;
                $wid_list[$i] = $this->Moa_worker_model->get_wid_by_uid($uid_list[$i]);
            }

            $data['name_list'] = $name_list;
            $data['wid_list'] = $wid_list;

            // 获取所有拾获物品登记信息
            $obj = $this->Moa_found_model->get_all();
            if ($obj == false){
                NULL;
            }else{
                for ($i = 0; $i < count($obj); $i++){
                    $data['d_fid'][$i] = $obj[$i]->fid;
                    $data['d_state'][$i] = $obj[$i]->state;
                    $data['d_fwid'][$i] = $obj[$i]->fwid;
                    $data['d_signuptime'][$i] = $obj[$i]->signuptime;
                    $data['d_fdatetime'][$i] = $obj[$i]->fdatetime;
                    $data['d_fdescription'][$i] = $obj[$i]->fdescription;
                    $data['d_fplace'][$i] = $obj[$i]->fplace;
                    $data['d_finder'][$i] = $obj[$i]->finder;
                    $data['d_fcontact'][$i] = $obj[$i]->fcontact;
                    $data['d_owid'][$i] = $obj[$i]->owid;
                    $data['d_owner'][$i] = $obj[$i]->owner;
                    $data['d_odatetime'][$i] = $obj[$i]->odatetime;
                    $data['d_ocontact'][$i] = $obj[$i]->ocontact;
                    $data['d_onumber'][$i] = $obj[$i]->onumber;
                }
            }

            /*for ($i = 0; $i < count($dutyout_list); $i++) {
                //取出勤id
                $d_doid = $dutyout_list[$i]->doid;
                //值班时段id
                $d_dutyid = $dutyout_list[$i]->dutyid;
                $d_duty = $this->Moa_duty_model->get_by_id($d_dutyid);
                //var_dump($d_duty);
                //星期
                $d_weekday = $d_duty->weekday;
                $d_weekdaytranslate = PublicMethod::translate_weekday($d_weekday);
                $d_period = $d_duty->period;
                $d_periodtime = PublicMethod::get_duty_duration($d_period);

                $d_wid = $dutyout_list[$i]->wid;
                $d_week = $dutyout_list[$i]->weekcount;
                $d_outtime = $dutyout_list[$i]->outtimestamp;

                $d_roomid = $dutyout_list[$i]->roomid;
                $d_room = $this->Moa_room_model->get($d_roomid)->room;

                $d_problemid = $dutyout_list[$i]->problemid;
                $d_problem = $this->Moa_problem_model->get($d_problemid);
                $d_solvewid = $d_problem->solve_wid;
                $d_description = $d_problem->description;
                $d_solution = $d_problem->solution;
                $d_solvetime = $d_problem->solved_time;

                $d_uid = $this->Moa_worker_model->get_uid_by_wid($d_wid);
                $d_user = $this->Moa_user_model->get($d_uid);
                $d_name = $d_user->name;
                //$d_level = $this->Moa_user_model->get($_SESSION['user_id'])->level;
                $d_solvename = "";

                if ($d_solvewid != null && $d_solvewid != false) {
                    $d_solveuid = $this->Moa_worker_model->get_uid_by_wid($d_solvewid);
                    $d_solveuser = $this->Moa_user_model->get($d_solveuid);
                    $d_solvename = $d_solveuser->name;
                    //var_dump($d_solveuid);
                    //var_dump($d_solveuser);
                } else {
                    $d_solvewid = false;
                    $d_solvename = false;
                    $d_solvetime = false;
                    //$d_description = false;
                    $d_solution = false;
                }


                //$data = array();
                $data['d_doid'][$i] = $d_doid;
                $data['d_dutyid'][$i] = $d_dutyid;
                $data['d_weekday'][$i] = $d_weekday;
                $data['d_weekdaytranslate'][$i] = $d_weekdaytranslate;
                $data['d_periodtime'][$i] = $d_periodtime;
                $data['d_wid'][$i] = $d_wid;
                $data['d_uid'][$i] = $d_uid;
                //$data['d_level'][$i] = $d_level;
                $data['d_week'][$i] = $d_week;
                $data['d_outtime'][$i] = $d_outtime;
                $data['d_roomid'][$i] = $d_roomid;
                $data['d_room'][$i] = $d_room;
                $data['d_problemid'][$i] = $d_problemid;
                $data['d_solvewid'][$i] = $d_solvewid;
                $data['d_description'][$i] = $d_description;
                $data['d_solution'][$i] = $d_solution;
                $data['d_name'][$i] = $d_name;
                $data['d_solvename'][$i] = $d_solvename;
                $data['d_solvetime'][$i] = $d_solvetime;
            }*/

            $this->load->view('view_found', $data);

        } else {
            // 未登录的用户请先登录
            PublicMethod::requireLogin();
        }
    }

    public function add()
    {
        if (isset($_SESSION['user_id']) && isset($_POST['wid'])) {
            $wid = $_POST['wid'];
            $dutyid = $_POST['dutyid'];
            $pid = $_POST['problemid'];
            $result = $this->Moa_dutyout_model->add($wid, $pid, $dutyid);
            //echo "<script>console.log($result);alert();</script>";
            if ($result > 0) {
                echo json_encode(array("status" => true, "msg" => "添加成功！", "doid" => $result));
            } else {
                echo json_encode(array("status" => false, "msg" => "添加失败！"));
            }
        } else {
            echo json_encode(array("status" => false, "msg" => "添加失败！"));
        }
    }

    public function update()
    {
        if (isset($_SESSION['user_id']) && isset($_POST['doid']) && isset($_POST['wid']) && isset($_POST['solve_time'])) {
            $doid = $_POST['doid'];
            $pid = $this->Moa_dutyout_model->get_by_doid($doid)->pid;
            $wid = $_POST['wid'];
            $solve_time = $_POST['solve_time'];
            $solution = htmlspecialchars($_POST['solution']);
            $result = $this->Moa_problem_model->update($pid, $solve_time, $wid, $solution);
            if (result > 0) {
                echo json_encode(array("status" => true, "msg" => "已解决！"));
            } else {
                echo json_encode(array("status" => true, "msg" => "更新失败！"));
            }
        } else {
            echo json_encode(array("status" => true, "msg" => "更新失败！"));
        }
    }


    public function delete_dutyout()
    {
        if (isset($_POST['doid'])) {
            $doid = $_POST['doid'];
            $deleting_record = $this->Moa_dutyout_model->get_by_doid($doid);
            $deleting_record_uid = $this->Moa_worker_model->get_uid_by_wid($deleting_record->wid);
            $current_uid = $_SESSION['user_id'];
            $current_level = $this->Moa_user_model->get($current_uid)->level;
            if ($deleting_record_uid == $current_uid || $current_level >= 2) {
                $state = $this->Moa_dutyout_model->delete_by_doid($doid);
                if ($state == true) {
                    echo json_encode(array("status" => true, "msg" => "已删除！"));
                } else {
                    echo json_encode(array("status" => false, "msg" => "删除失败！"));
                }
            } else {
                echo json_encode(array("status" => false, "msg" => "删除失败！"));
            }
        }
    }

    public function getInformation()
    {
        // 取所有课室的roomid与room（课室编号）
        $state = 0;
        $room_obj_list = $this->Moa_room_model->get_by_state($state);

        for ($i = 0; $i < count($room_obj_list); $i++) {
            $roomid_list[$i] = $room_obj_list[$i]->roomid;
            $room_list[$i] = $room_obj_list[$i]->room;
        }

        $data['roomid_list'] = $roomid_list;
        $data['room_list'] = $room_list;

        $common_worker = $this->Moa_user_model->get(0);

        for ($i = 0; $i < count($common_worker); $i++) {
            $uid_list[$i] = $common_worker[$i]->uid;
            $name_list[$i] = $common_worker[$i]->name;
            $wid_list[$i] = $this->Moa_worker_model->get_wid_by_uid($uid_list[$i]);
        }

        $data['name_list'] = $name_list;
        $data['wid_list'] = $wid_list;

        $obj = $this->Moa_problem_model->get_unsolve(); /*获取所有未解决的problem的信息*/
        if ($obj == FALSE) {
            $data['problem_list'] = array();
            $data['problemid_list'] = array();
        } else {
            for ($i = 0; $i < count($obj); $i++) {
                $data['problem_list'][$i] = $obj[$i]->description;
                $data['problemid_list'][$i] = $obj[$i]->pid;
            }
        }

        //获取所有值班时段信息
        $duty = $this->Moa_duty_model->get_all();
        for ($i = 0; $i < count($duty); $i++) {
            $dutyid_list[$i] = $duty[$i]->dutyid;
            $weekday_list[$i] = PublicMethod::translate_weekday($duty[$i]->weekday);
            $period_list[$i] = PublicMethod::get_duty_duration($duty[$i]->period);
        }
        $data['dutyid_list'] = $dutyid_list;
        $data['weekday_list'] = $weekday_list;
        $data['period_list'] = $period_list;

        echo json_encode(array("status" => TRUE, "msg" => "获取课室等信息成功", "data" => $data));
        return;
    }

    //插入新problem
    public function insertProblem()
    {
        if (isset($_SESSION['user_id'])) {
            date_default_timezone_set('PRC');

            $founder_wid = date($_POST['founder_wid']);
            $found_time = $_POST['found_time'];
            $roomid = $_POST['roomid'];
            $description = htmlspecialchars($_POST['description']);

            $insert_id = $this->Moa_dutyout_model->insert($founder_wid, $found_time, $roomid, $description);
            if ($insert_id > 0)
                echo json_encode(array("status" => TRUE, "msg" => "新建故障信息成功", "insert_id" => $insert_id));
            else
                echo json_encode(array("status" => FALSE, "msg" => "新建故障信息失败"));

        } else {
            // 未登录的用户请先登录
            echo json_encode(array("status" => FALSE, "msg" => "未登陆"));
        }

    }

    //更新Problem状态
    public function updateProblem()
    {
        if (isset($_SESSION['user_id'])) {

            $pid = $_POST['pid'];
            $solved_time = $_POST['solved_time'];
            $solve_wid = $_POST['solve_wid'];
            $solution = htmlspecialchars($_POST['solution']);

            $affected_rows = $this->Moa_problem_model->update($pid, $solved_time, $solve_wid, $solution);
            if ($affected_rows > 0)
                echo json_encode(array("status" => TRUE, "msg" => "更新成功"));
            else
                echo json_encode(array("status" => FALSE, "msg" => "更新失败"));
        } else {
            // 未登录的用户请先登录
            echo json_encode(array("status" => FALSE, "msg" => "未登陆"));
        }

    }

    public function getDutyIdByPeriod()
    {
        if (isset($_SESSION['user_id'])) {
            $weekday = $_POST['weekday'];
            $period = $_POST['period'];
            $result = $this->Moa_duty_model->get_by_day_and_period($weekday, $period);
            echo json_encode(array('status' => true, 'dutyid' => $result->dutyid));
        } else {
            echo json_encode(array('status' => false));
        }
    }

}