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
            // 1. Verificar si el email existe mediante el Procedimiento Almacenado
            // Usamos prepare para evitar inyecciones en el parámetro del procedimiento
            $stmt = $conn->prepare("CALL sp_comprovar_email(:email, @result)");
            $stmt->execute([':email' => $this->ProEmail]);
            
            // Recuperamos el valor de la variable @result definida en MySQL
            $res = $conn->query("SELECT @result AS exist")->fetch(PDO::FETCH_ASSOC);
            $exist = intval($res["exist"]);

            if ($exist === 1) {
                echo "<span>El correo electrónico ya está registrado. Inténtelo con otro.</span>";
                return;
            }

            // 2. Validar que las contraseñas coincidan
            if ($this->ProPwd !== $ProPwdCon) {
                echo "<span>Las contraseñas no coinciden. Inténtelo de nuevo.</span>";
                return;
            }

            // 3. Si todo es correcto, insertar en la tabla promotor
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
            // Capturamos cualquier error de la base de datos
            echo "<span>Error en el registro del promotor: " . $e->getMessage() . "</span>";
        } finally {
            // En PDO, poner el statement a null es equivalente a cerrarlo
            $stmt = null;
        
        $conn->query("CALL sp_comprovar_emailp('$this->ProEmail', @result)");
        $result = $conn->query("SELECT @result AS exist");
        $row = $result->fetch_assoc();
        $exist = intval($row["exist"]);
 
        if ($exist === 1) {
            echo "<span>El correo electrónico ya está registrado. Inténtelo con otro.</span>";
            return;
        }
 
        if ($this->ProPwd !== $ProPwdCon) {
            echo "<span>Las contraseñas no coinciden. Inténtelo de nuevo.</span>";
            return;
        }
 
        if ($this->ProPwd === $ProPwdCon && $exist === 0) {
            $insert = $conn->query("INSERT INTO promotor (Name, Pwd, Email, Direction, CreditCard)
                VALUES ('$this->ProName', '$this->ProPwd', '$this->ProEmail', '$this->ProDirection', '$this->ProCreditCard')");
            header('Location: ../Vista/index.php');
            exit();
        }
    }
}
?>