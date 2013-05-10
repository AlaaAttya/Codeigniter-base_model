<?php

class MY_Model extends CI_Model {
    public $user_id;
    public $user_name;
    public $email;
    public $bio;
    public $location;
    public $birth_date;

    private $db_fields = array('user_id', 'user_name', 'bio', 'location', 'email', 'birth_date');
    private $table_name;
    private $tabel_key = "user_id";


    function __construct() {

        parent::__construct();
        
        //set the table from the name of model class name
        $this->table_name = preg_replace('/(my_model|_model)?$/', '', strtolower(get_class($this)));
        if($this->table_name!='')
        {
            $row = $this->db->query("SHOW KEYS FROM {$this->table_name} WHERE Key_name = 'PRIMARY'")->result();
            
            if(!is_null($row))
            {
                echo $row[0]->Column_name;
            }
        }  
    }

    
    /*--------------------------------------------------------------------------------*/
    function insert() {
        //temp record to be inserted
        $temp = array();

        //assigning data attributes to be inserted

        foreach ($this->db_fields as $field) {
            //skipping 'user_id' field to be inserted
            if ($field !== $this -> db_fields[0]) {
                if (property_exists($this, $field) && isset($field))
                    $temp[$field] = $this -> $field;
            }

        }

        $this -> db -> insert($this -> table_name, $temp);

        if ($this -> db -> affected_rows() > 0)
            return $this -> db -> insert_id();

        return FALSE;
    }

    /*--------------------------------------------------------------------------------------*/
    function select_by_id($uid) {
        $result = $this -> db -> get_where($this -> table_name, array('user_id' => $uid)) -> result();

        if (isset($result)) {
            $result_object = new this;

            foreach ($this->db_fields as $field) {
                if (property_exists($this, $field))
                    $result_object -> $field = $result[0] -> $field;
            }

            return $this->my_get_object_vars($result_object);
        } else
        return FALSE;

    }

    /*--------------------------------------------------------------------------------------*/
    function select_all() {
        $result = $this -> db -> get('user_details') -> result();

        $temp_result_object = new user_model();
        $result_array = array();

        if (isset($result)) {
            foreach ($result as $record) {
                foreach ($this->db_fields as $field) {
                    if (property_exists($this, $field))
                        $temp_result_object -> $field = $record -> $field;
                }

                $result_array[] = $this->my_get_object_vars($temp_result_object);
            }

            return $result_array;
        } else
        return FALSE;
    }
    
    /**
     * return the public properties only, solving php5.3 bug issue
     */
    function my_get_object_vars($obj) {
        $ref = new ReflectionObject($obj);
        $pros = $ref -> getProperties(ReflectionProperty::IS_PUBLIC);
        $result = array();
        foreach ($pros as $pro) {
            false && $pro = new ReflectionProperty();
            $result[$pro -> getName()] = $pro -> getValue($obj);
        }

        return $result;
    }

    /*---------------------------------------------------------------------------------------*/
    function update() {
        $temp = array();

        //assigning data attributes to be inserted

        foreach ($this->db_fields as $field) {
            if (property_exists($this, $field))
                $temp[$field] = $this -> $field;
        }

        $this -> db -> where($this -> tabel_key, $temp[$this -> tabel_key]);
        $this -> db -> update($this -> table_name, $temp);

        if ($this -> db -> affected_rows() > 0)
            return TRUE;

        return FALSE;
    }

    /*---------------------------------------------------------------------------------------*/
    function delete($id) {
        $this -> db -> delete($this -> table_name, array($this -> tabel_key => $id));
        if ($this -> db -> affected_rows() > 0)
            return TRUE;

        return FALSE;
    }

    

}
?>