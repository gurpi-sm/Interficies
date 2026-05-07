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
        $stmt = $conn->prepare("CALL sp_comprovar_emailp(:email, @result)");
        $stmt->execute([':email' => $this->ProEmail]);
        $stmt->closeCursor();

        $result = $conn->query("SELECT @result AS exist")->fetch(PDO::FETCH_ASSOC);
        $exist = intval($result["exist"]);

        if ($exist === 1) {
            echo "<span>El correo electronico ya esta registrado. Intentelo con otro.</span>";
            return;
        }

        if ($this->ProPwd !== $ProPwdCon) {
            echo "<span>Las contrasenas no coinciden. Intentelo de nuevo.</span>";
            return;
        }

        $insert = $conn->prepare(
            "INSERT INTO promotor (Name, Pwd, Email, Direction, CreditCard)
             VALUES (:name, :pwd, :email, :direction, :credit_card)"
        );
        $insert->execute([
            ':name' => $this->ProName,
            ':pwd' => $this->ProPwd,
            ':email' => $this->ProEmail,
            ':direction' => $this->ProDirection,
            ':credit_card' => $this->ProCreditCard,
        ]);

        header('Location: ../Vista/index.php');
        exit();
    }
}
?>
