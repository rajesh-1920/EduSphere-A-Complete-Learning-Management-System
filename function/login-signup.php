<?php

use function PHPSTORM_META\type;

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
            return "Fail";
        }
    }

    public function login_section($data)
    {
        $email = $data['email'];
        $pass = $data['password'];
        $type = ($data['role'] == "instructor" ? 1 : 2);
        if (!empty($email)) {
            $query = "SELECT * FROM info WHERE info.Email='$email'";
            if (mysqli_query($this->conn, $query)) {
                $login_messege = mysqli_query($this->conn, $query);
                $pass1 = "";
                $type1 = "";
                while ($temp = mysqli_fetch_assoc($login_messege)) {
                    $pass1 = $temp["pass"];
                    $type1 = $temp["type"];
                }
                if ($pass1 != $pass) {
                    return "*Invalid password or username Please try again";
                } else if ($type != $type1) {
                    return "*Invalid username or type Please try again";
                } else {
                    return "success";
                }
            }
        } else {
            return "*Invalid password or username Please try again";
        }
    }
}
