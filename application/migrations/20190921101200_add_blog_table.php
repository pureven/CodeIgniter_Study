<?php
/**
 * Created by PhpStorm.
 * User: 39260
 * Date: 2019/9/21
 * Time: 9:38
 */
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_add_blog_table extends CI_Migration
{
    public function up()
    {
        if (!$this->db->table_exists('blog')) {
            $test = [
                'blog_id' => [
                    'type' => 'INT',
                    'constraint' => 5,
                    'unsigned' => TRUE,
                    'auto_increment' => TRUE
                ],
                'blog_title' => [
                    'type' => 'VARCHAR',
                    'constraint' => '100',
                ],
                'blog_description' => [
                    'type' => 'TEXT',
                    'null' => TRUE,
                ],
            ];
            $this->dbforge->add_field($test);
            $this->dbforge->add_key('blog_id', true);
            $this->dbforge->create_table('blog');
        }
    }

    public function down()
    {
        if ($this->db->table_exists('blog')) {
            $this->dbforge->drop_table('blog');
        }
    }
}