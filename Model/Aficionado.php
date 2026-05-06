
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
        try {
            
            $stmt = $conn->prepare("CALL sp_comprovar_email(:email, @result)");
            $stmt->execute([':email' => $this->FanEmail]);
            
            
            $res = $conn->query("SELECT @result AS exist")->fetch(PDO::FETCH_ASSOC);
            $exist = intval($res["exist"]);

            if ($exist === 1) {
                echo "<span>El correo electrónico ya está registrado. Inténtelo con otro.</span>";
                return;
            }

            
            if ($this->FanPwd !== $FanPwdCon) {
                echo "<span>Las contraseñas no coinciden. Inténtelo de nuevo.</span>";
                return;
            }

            // 3. Insertar nuevo registro
            if ($this->FanPwd === $FanPwdCon && $exist === 0) {
                $sql = "INSERT INTO aficionado (Name, Email, Pwd, PwdCon, Sport) 
                        VALUES (:name, :email, :pwd, :pwdcon, :sport)";
                
                $insertStmt = $conn->prepare($sql);
                $insertStmt->execute([
                    ':name'   => $this->FanName,
                    ':email'  => $this->FanEmail,
                    ':pwd'    => $this->FanPwd,
                    ':pwdcon' => $this->FanPwdCon,
                    ':sport'  => $this->FanSport
                ]);

                header('Location: ../Vista/index.php');
                exit();
            }

        } catch (PDOException $e) {
            // Manejo de errores de base de datos
            echo "<span>Error en el registro: " . $e->getMessage() . "</span>";
        } finally {
            
            $stmt = null;
        }
    }
}
?>