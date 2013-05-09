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
        //echo $this->table_name;
        //SHOW KEYS FROM data WHERE Key_name = 'PRIMARY'
       
        $pr = $this->db->query("SHOW KEYS FROM {$this->table_name} WHERE Key_name = 'PRIMARY'")->result_array();
        var_dump($pr[0]['Column_name']);
       
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

    /*----------------------------------------------------------------------------------------*/
    /**********-------------------user model special functions-----------------------**********/
    function prep_password($password) {
        return sha1($password . $this -> config -> item('encryption_key'));
    }

    /*
     * @param      email
     * @param      password
     * @desc       get the user_id of the specified email
     *             if the email exists in the db check if the password
     *             matches the user_id from the 'native_user' table
     *
     * */
    function check_native_user_data($email, $password) {
        $this -> db -> select('user_id , user_name , email , bio , location , profile_pic , rate , birth_date');

        if ($user = $this -> db -> get_where('user_details', array('email' => $email)) -> result()) {
            if ($uid = $this -> db -> get_where('native_user', array('user_id' => $user[0] -> user_id, 'password' => $password)) -> result())
                return $user[0];
            else
                return FALSE;
        } else
            return FALSE;

    }

    /*
     * Insert user into 'native_user' table
     * used bye sign_up user controller method
     * */
    function insert_native($uid, $password) {
        $this -> db -> insert('native_user', array('user_id' => $uid, 'password' => $password));
        if ($this -> db -> affected_rows() > 0)
            return $this -> db -> insert_id();
        return FALSE;
    }

    /**
     * Return 'user_id' of the current user object
     * using user 'email'
     *
     */
    function select_by_email() {
        $this -> db -> select('user_id');
        $uid = $this -> db -> get_where('user_details', array('email' => $this -> email)) -> result();
        if ($this -> db -> affected_rows() > 0)
            return $uid[0] -> user_id;
        else
            return FALSE;
    }

    function reset_password($password) {
        $this -> db -> where($this -> tabel_key, $this -> user_id);
        $this -> db -> update('native_user', array('password' => $password));
        if ($this -> db -> affected_rows() > 0)
            return TRUE;
        return FALSE;
    }

    /*
     * params: $message (message body)
     * desc.: sends custom email to the pecified user
     */
    function send_mail($message, $subject, $email) {
        $this -> load -> library('email');
        //$this->config->load('Email');

        $temp = new CI_Model();
        $temp -> load -> library('email');
        //var_dump($temp->email);
        $temp -> email -> set_newline("\r\n");
        $temp -> email -> from('vidooman@gmail.com', 'Alaa Attya');
        $temp -> email -> to($email);
        $temp -> email -> subject($subject);
        $temp -> email -> message($message);

        if ($temp -> email -> send()) {
            //echo "sent";
        } else {
            show_error($temp -> email -> print_debugger());
        }
    }

}
?>