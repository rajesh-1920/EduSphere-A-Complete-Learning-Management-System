<?php
class login_signup
{
    private $conn;
    public function __construct()
    {
        $dbhost = "localhost";
        $dbuser = "root";
        $dbname = "temp";
        $dbpass = "";
        $this->conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
        if (!isset($this->conn)) {
            die("Database connection error");
        }
    }
    public function insert_signup_data($data)
    {
        $name = $data['name'];
        $email = $data['email'];
        $pass = $data['password'];
        $type = ($data['role'] == "instructor" ? 1 : 2);
        if (!empty($name) && !empty($email) && !empty($pass) && !empty($type)) {
            $query = "INSERT INTO info (Name,Email,pass,type) VALUES('$name','$email','$pass',$type);";
            if (mysqli_query($this->conn, $query)) {
                return "success";
            }
        } else {
            return "Invalid";
        }
    }
}
