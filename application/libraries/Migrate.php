<?php
/**
 * Created by PhpStorm.
 * User: 39260
 * Date: 2020/3/21
 * Time: 20:09
 */
defined('BASEPATH') or exit('No direct script access allowed');

class Migrate extends My_class
{
    protected $forge_db;
    protected $my_util;
    protected $my_forge;
    private $migration_table = 'migrations';

    private $prod_db;
    private $prod_user;
    private $prod_pwd;

    private $forge_db_user;
    private $forge_db_pwd;

    public function __construct()
    {
        $this->config->load('database');

        // 待建的数据库 账号 密码 codeigniter/codeigniter/helloworld
        $this->prod_db = config_item('db_config_prod')['database'];
        $this->prod_user = config_item('db_config_prod')['username'];
        $this->prod_pwd = config_item('db_config_prod')['password'];

        // mysql管理员账号/密码 root/xxxxxx
        $this->forge_db_user = config_item('db_config_forge')['username'];
        $this->forge_db_pwd = config_item('db_config_forge')['password'];

        // 加载数据库类 database('default')，用于创建codeigniter数据库以及codeigniter用户
        $this->forge_db = $this->load->database(config_item('db_forge'), true);
        $this->my_util = $this->load->dbutil($this->forge_db, true);

        // 创建codeigniter数据库
        if (!$this->my_util->database_exists($this->prod_db)) {
            $this->my_forge = $this->load->dbforge($this->forge_db, true);
            $result = $this->my_forge->create_database($this->prod_db);
            if (!$result) {
                log_message('error', 'Database not exist and create failed !');
                return;
            }
        }

        // 创建用户codeigniter
        $query = $this->forge_db->query("SELECT 1 FROM user WHERE User='" . $this->prod_user . "'");
        if (!$query->row()) {
            $this->forge_db->query(
                "create user '" . $this->prod_user . "'@'" . "localhost' identified by '" . $this->prod_pwd . "'"
            );
            log_message('info', 'CREATE USER codeigniter !');
        }

        // 授权用户codeigniter
        $this->forge_db->query("grant all on {$this->prod_db}.* to '{$this->prod_user}'@'localhost'");
        log_message('info', 'grant prod user to prod db');

        $this->load->database();
        $this->load->dbutil();
        $this->load->dbforge();

        // If the migrations table is missing, make it
        if (!$this->db->table_exists($this->migration_table)) {
            $this->dbforge->add_field(array(
                'version' => array('type' => 'BIGINT', 'constraint' => 20),
            ));
            $this->dbforge->create_table($this->migration_table);
            $this->db->insert($this->migration_table, array('version' => 0));
        }
    }

    public function upgrade()
    {
        $this->load->library('migration');
        if (!$this->migration->current() === true) {
            log_message('error', $this->migration->error_string());
            return false;
        } else {
            log_message('info','migration versioned!');
            return true;
        }
    }

}