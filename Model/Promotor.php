<?php
class Promotor {
    private $ProName;
    private $ProPwd;
    private $ProPwdCon;
    private $ProEmail;
    private $ProDirection;
    private $ProCreditCard;

    public function __construct($ProName, $ProPwd, $ProPwdCon, $ProEmail, $ProDirection, $ProCreditCard)
    {
        $this->ProName = $ProName;
        $this->ProPwd = $ProPwd;
        $this->ProPwdCon = $ProPwdCon;
        $this->ProEmail = $ProEmail;
        $this->ProDirection = $ProDirection;
        $this->ProCreditCard = $ProCreditCard;
    }

    public function registerp($ProPwdCon, $conn)
    {
        try {
            
            $stmt = $conn->prepare("CALL sp_comprovar_emailp(:email, @result)");
            $stmt->execute([':email' => $this->ProEmail]);
            
            
            $res = $conn->query("SELECT @result AS exist")->fetch(PDO::FETCH_ASSOC);
            $exist = intval($res["exist"]);

            if ($exist === 1) {
                echo "<span>El correo electrónico ya está registrado. Inténtelo con otro.</span>";
                return;
            }

            
            if ($this->ProPwd !== $ProPwdCon) {
                echo "<span>Las contraseñas no coinciden. Inténtelo de nuevo.</span>";
                return;
            }

            
            if ($this->ProPwd === $ProPwdCon && $exist === 0) {
                $sql = "INSERT INTO promotor (Name, Pwd, Email, Direction, CreditCard)
                        VALUES (:name, :pwd, :email, :direction, :creditcard)";
                
                $insertStmt = $conn->prepare($sql);
                $insertStmt->execute([
                    ':name'       => $this->ProName,
                    ':pwd'        => $this->ProPwd,
                    ':email'      => $this->ProEmail,
                    ':direction'  => $this->ProDirection,
                    ':creditcard' => $this->ProCreditCard
                ]);

                header('Location: ../Vista/index.php');
                exit();
            }

        } catch (PDOException $e) {
            
            echo "<span>Error en el registro del promotor: " . $e->getMessage() . "</span>";
        } finally {
            
            $stmt = null;
        }
    }
}
?>