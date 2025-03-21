<?php

use function PHPSTORM_META\type;

class login_signup
{
    private $conn;
    public function __construct()
    {
        $dbhost = "localhost";
        $dbuser = "root";
        $dbname = "edusphere";
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
            return "Fail";
        }
    }

    public function login_section($data)
    {
        $userid = $data['userid'];
        $pass = $data['password'];
        if (!empty($userid)) {
            if ($data['role'] == "admin")
                $query = "SELECT * FROM admin WHERE admin.userid='$userid'";
            if ($data['role'] == "instructor")
                $query = "SELECT * FROM instructor WHERE instructor.userid='$userid'";
            if ($data['role'] == "student")
                $query = "SELECT * FROM student WHERE student.userid='$userid'";
            if (mysqli_query($this->conn, $query)) {
                $login_messege = mysqli_query($this->conn, $query);
                $pass1 = "";
                while ($temp = mysqli_fetch_assoc($login_messege)) {
                    $pass1 = $temp["pass"];
                }
                if ($pass1 != "") {
                    if ($pass1 != $pass) {
                        return "*Invalid password or user id Please try again";
                    } else {
                        return "success";
                    }
                } else {
                    return "*Invalid user id or role Please try again";
                }
            }
        } else {
            return "*Invalid or empty user id Please try again";
        }
    }
}
