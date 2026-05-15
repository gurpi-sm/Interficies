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
        $stmt = null;

        try {
            $stmt = $conn->prepare("CALL sp_comprovar_emailp(:email, @result)");
            $stmt->execute([':email' => $this->ProEmail]);

            $res = $conn->query("SELECT @result AS exist")->fetch(PDO::FETCH_ASSOC);
            $exist = intval($res["exist"]);

            if ($exist === 1) {
                $_SESSION['register_error'][] = "El correo electrónico ya está registrado. Inténtelo con otro.";
                header('Location: ../Vista/promoter-registration.php');
                exit();
            }

            if ($this->ProPwd !== $ProPwdCon) {
                $_SESSION['register_error'][] = "Las contraseñas no coinciden. Inténtelo de nuevo.";
                header('Location: ../Vista/promoter-registration.php');
                exit();
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
            $_SESSION['register_error'][] = "Error en el registro del promotor: " . $e->getMessage();
            header('Location: ../Vista/promoter-registration.php');
            exit();
        } finally {
            $stmt = null;
        }
    }
}
?>
