
<?php
// controller/UserController.php
require_once '../Modelo/NextLvlBase.php';

// Scanner sc =  new Scanner();
// sc.nextLine();
$uc = new UserController();
$uc->login();

class UserController
{
    private $connection;

    public function __construct() {}

    public function register() {}


    public function login()
    {
        require_once '../Modelo/NextLvlBase.php';
        $db = new Database();
        $conn = $db->getConnection();
        $sql = "INSERT INTO aficionado (Name, Pwd, Email, Sport)
values ('Pau Hernandez', '1234', 'pau@gmail.com', 'Futbol')";

        $conn->close();
    }

    public function logout() {}
}
?>