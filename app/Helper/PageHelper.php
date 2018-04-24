<?php
namespace App\Helper;

/**
 * Created by PhpStorm.
 * User: wangsl
 * Date: 2018/4/13
 * Time: 14:27
 */
class PageHelper
{
//    public $lastId;//上一页最大id
//    public $offset;//每页偏移量，默认为10
//    public $start;//开始参数（算出来的page*offset+1）
//    public $page_key;//分页索引，默认为id    TODO 强烈建议id为主键或已添加索引
//    public $table_name;//查询表名
//    public $order;//升序、降序，默认为降序
    //使用子查询  和limit $start ,1  速度比较快
    //$sql = "select *from user where id>{$id} limit 10";$id为上一页最大的值
    //DB::insert('insert into users (id, name, email, password) values (?, ?, ?, ? )',
    //DB::update('update users set name='']);
    //DB::select('select * from users where id = ?', [1]);
    //DB::delete('delete from users');
//select xx from  table_a where ID >=(select max(ID) from (select xxxx INNER JOIN xx where xxxx limit m,1)) limit 10.
    static public function getPage($params){
        if(empty($params)){
            return false;
        }
        if(empty($params['table_name'])){
            return false;
        }
        $lastId = $params['lastId'] ?? 0;
        $offset = $params['offset'] ?? 10;
        $start = $params['start'] ?? 1;
        $page_key = $params['page_key'] ?? 'id';
        $table_name = $params['table_name'];
        $order = $params['order'] ?? 'desc';
        $table_name =env('DB_PREFIX').$table_name;
        $sql = "select {$page_key} from {$table_name} ";
        if($lastId > 0){
            $where = "where {$page_key} >{$lastId}";
        }elseif($start>0){
            $where = "where {$page_key} >=(select {$page_key} from {$table_name} order by {$page_key} {$order}  limit {$start},1)";
        }else{
            $where = "1=1";
        }
        $sql .= $where."order by {$page_key} {$order} limit {$offset}";
        $res = DB::select($sql);
        return $res;
    }
}








