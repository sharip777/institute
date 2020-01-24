<?php


class Institutes
{
    public function __construct()
    {
        global $wpdb;
        $this->db = $wpdb;
        $this->table_name = $this->db->prefix . VNINS_DBTN;
        return $this;
    }

    public function get_all_institutes()
    {
        $institutes = $this->db->get_results("SELECT * FROM $this->table_name");
        return $institutes;
    }

    public function get_sizo()
    {
        $institutes = $this->db->get_results("SELECT * FROM $this->table_name WHERE `is_sizo` = 1 ");
        return $institutes;
    }

    public function get_not_sizo()
    {
        $institutes = $this->db->get_results("SELECT * FROM $this->table_name WHERE `is_sizo` = 0 ");
        return $institutes;
    }
}
