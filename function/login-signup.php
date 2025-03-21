<?php
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
    // for sign up -------------------------------------
    public function insert_signup_data($data)
    {
        $name = trim($data['name']);
        $email = trim($data['email']);
        $pass = trim($data['password']);
        $std_id = "" . $data['std-id'];
        // studetnt account create -------------------------------
        if ($data['role'] == "student") {
            $char = "";
            for ($i = strlen($name) - 1; $i >= 0; $i--) {
                if ($name[$i] >= 'A' && $name[$i] <= 'Z') {
                    $char = $name[$i];
                    break;
                }
            }
            $userid = substr($name, 0, 1) . $char . substr($std_id, -4);
            $query = "INSERT INTO student (id,name,userid,email,pass) 
                        VALUES($std_id,'$name','$userid','$email','$pass');";
            if (mysqli_query($this->conn, $query)) {
                return $userid;
            } else {
                return "Fail";
            }
        }
        // instructor account create ------------------------------------
        if ($data['role'] == "instructor") {
            $id = 0;
            $query = "SELECT * FROM instructor ORDER BY id DESC LIMIT 1";
            if (mysqli_query($this->conn, $query)) {
                $temp = mysqli_query($this->conn, $query);
                $value = mysqli_fetch_assoc($temp);
                $id = $value['id'];
            }
            $id++;
            $id = "" . $id;
            while (strlen($id) < 4) {
                $id = "0" . $id;
            }
            $char = "";
            for ($i = strlen($name) - 1; $i >= 0; $i--) {
                if ($name[$i] >= 'A' && $name[$i] <= 'Z') {
                    $char = $name[$i];
                    break;
                }
            }
            $userid = substr($name, 0, 1) . $char . $id;
            $query = "INSERT INTO instructor (name,userid,email,pass) 
                        VALUES('$name','$userid','$email','$pass');";
            if (mysqli_query($this->conn, $query)) {
                return $userid;
            } else {
                return "Fail";
            }
        }
    }


    // for login --------------------------------------------
    public function login_section($data)
    {
        $userid = trim($data['userid']);
        $pass = trim($data['password']);
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
