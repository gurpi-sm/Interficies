<?php
class Aficionado {
    private $FanName;
    private $FanEmail;
    private $FanPwd;
    private $FanPwdCon;
    private $FanSport;

    public function __construct($FanName, $FanEmail, $FanPwd, $FanPwdCon, $FanSport)
    {
        $this->FanName = $FanName;
        $this->FanEmail = $FanEmail;
        $this->FanPwd = $FanPwd;
        $this->FanPwdCon = $FanPwdCon;
        $this->FanSport = $FanSport;
    }

    public function register($FanPwdCon, $conn)
    {
        $stmt = $conn->prepare("CALL sp_comprovar_email(:email, @result)");
        $stmt->execute([':email' => $this->FanEmail]);
        $stmt->closeCursor();

        $result = $conn->query("SELECT @result AS exist")->fetch(PDO::FETCH_ASSOC);
        $exist = intval($result["exist"]);

        if ($exist === 1) {
            echo "<span>El correo electronico ya esta registrado. Intentelo con otro.</span>";
            return;
        }

        if ($this->FanPwd !== $FanPwdCon) {
            echo "<span>Las contrasenas no coinciden. Intentelo de nuevo.</span>";
            return;
        }

        $insert = $conn->prepare(
            "INSERT INTO aficionado (Name, Email, Pwd, PwdCon, Sport)
             VALUES (:name, :email, :pwd, :pwdcon, :sport)"
        );
        $insert->execute([
            ':name' => $this->FanName,
            ':email' => $this->FanEmail,
            ':pwd' => $this->FanPwd,
            ':pwdcon' => $this->FanPwdCon,
            ':sport' => $this->FanSport,
        ]);

        header('Location: ../Vista/index.php');
        exit();
    }
}
?>
