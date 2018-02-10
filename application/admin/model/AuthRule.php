<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/1
 * Time: 13:03
 */

/**
 *
 * @author Administrator
 *         规则表的模型
 *        
 */
namespace app\admin\Model;

use think\Model;

class AuthRule extends Model
{

    /*
     * 从大到小排序查询出来的结果集,方便后续操作
     */
    public function getrule()
    {
        $data = $this->order('id desc')->select();
        return $data;
    }

    /*
     * 无限分类,very important
     */
    public function gettree($data, $pid = 0, $level = 1)
    {
        static $arr = array();
        foreach ($data as $key => $val) {
            if ($val['pid'] == $pid) {
                $val['false_level'] = $level;
                $arr[] = $val;
                $this->gettree($data, $val['id'], $level = 1);
            }
        }
        return $arr;
    }

    /*
     * 以下的方法我也是一脸懵逼
     */
    public function getchilrenid($authRuleId)
    {
        $AuthRuleRes = $this->select();
        return $this->_getchilrenid($AuthRuleRes, $authRuleId);
    }

    public function _getchilrenid($AuthRuleRes, $authRuleId)
    {
        static $arr = array();
        foreach ($AuthRuleRes as $k => $v) {
            if ($v['pid'] == $authRuleId) {
                $arr[] = $v['id'];
                $this->_getchilrenid($AuthRuleRes, $v['id']);
            }
        }
        
        return $arr;
    }

    public function getparentid($authRuleId)
    {
        $AuthRuleRes = $this->select();
        return $this->_getparentid($AuthRuleRes, $authRuleId, True);
    }

    public function _getparentid($AuthRuleRes, $authRuleId, $clear = False)
    {
        static $arr = array();
        if ($clear) {
            $arr = array();
        }
        foreach ($AuthRuleRes as $k => $v) {
            if ($v['id'] == $authRuleId) {
                $arr[] = $v['id'];
                $this->_getparentid($AuthRuleRes, $v['pid'], False);
            }
        }
        asort($arr);
        $arrStr = implode('-', $arr);
        return $arrStr;
    }
}

