<?php
require_once __DIR__ . '/../Model/NextLvlBase.php';

$db = new Database();
$conn = $db->getConnection();
$errors = [];
$message = $_GET['message'] ?? '';
$editingEvent = null;
$postedEvent = null;

function clean_text($value)
{
    return trim((string) $value);
}

function redirect_with_message($message)
{
    header('Location: create-event.php?message=' . urlencode($message));
    exit();
}

function ensure_event_table_schema($conn)
{
    $conn->exec(
        'CREATE TABLE IF NOT EXISTS evento (
            Id int(255) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            Name varchar(50) NOT NULL,
            Sport varchar(60) NOT NULL,
            `Date` date NULL,
            Location varchar(100) NULL,
            Price decimal(10,2) NULL,
            Description text
        )'
    );

    $requiredColumns = [
        'Name' => 'ALTER TABLE evento ADD COLUMN Name varchar(50) NOT NULL',
        'Sport' => 'ALTER TABLE evento ADD COLUMN Sport varchar(60) NOT NULL',
        'Date' => 'ALTER TABLE evento ADD COLUMN `Date` date NULL',
        'Location' => 'ALTER TABLE evento ADD COLUMN Location varchar(100) NULL',
        'Price' => 'ALTER TABLE evento ADD COLUMN Price decimal(10,2) NULL',
        'Description' => 'ALTER TABLE evento ADD COLUMN Description text',
    ];

    $stmt = $conn->prepare(
        'SELECT COUNT(*)
         FROM INFORMATION_SCHEMA.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = "evento"
           AND COLUMN_NAME = :column_name'
    );

    foreach ($requiredColumns as $columnName => $alterSql) {
        $stmt->execute([':column_name' => $columnName]);

        if ((int) $stmt->fetchColumn() === 0) {
            $conn->exec($alterSql);
        }
    }
}
ensure_event_table_schema($conn);

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

        if ($id) {
            $stmt = $conn->prepare('DELETE FROM evento WHERE Id = :id');
            $stmt->execute([':id' => $id]);
            redirect_with_message('Evento eliminado correctamente.');
        }

        $errors[] = 'No se ha podido identificar el evento a eliminar.';
    }

    if ($action === 'create' || $action === 'update') {
        $event = [
            'Name' => clean_text($_POST['Name'] ?? ''),
            'Sport' => clean_text($_POST['Sport'] ?? ''),
            'Date' => clean_text($_POST['Date'] ?? ''),
            'Location' => clean_text($_POST['Location'] ?? ''),
            'Price' => clean_text($_POST['Price'] ?? ''),
            'Description' => clean_text($_POST['Description'] ?? ''),
        ];
        $postedEvent = $event;

        $fieldLabels = [
            'Name' => 'nombre',
            'Sport' => 'deporte',
            'Date' => 'fecha',
            'Location' => 'ubicacion',
            'Price' => 'precio',
            'Description' => 'descripcion',
        ];

        foreach ($event as $field => $value) {
            if ($value === '') {
                $errors[] = 'El campo ' . $fieldLabels[$field] . ' es obligatorio.';
            }
        }

        $date = null;
        $hasDateErrors = false;

        if ($event['Date'] !== '') {
            $date = DateTime::createFromFormat('Y-m-d', $event['Date']);
            $dateErrors = DateTime::getLastErrors();
            $hasDateErrors = $dateErrors && ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0);
        }

        if ($event['Date'] !== '' && (!$date || $hasDateErrors || $date->format('Y-m-d') !== $event['Date'])) {
            $errors[] = 'La fecha no tiene un formato valido.';
        }

        if ($event['Price'] !== '' && !is_numeric($event['Price'])) {
            $errors[] = 'El precio debe ser un numero valido.';
        }

        if (is_numeric($event['Price']) && (float) $event['Price'] < 0) {
            $errors[] = 'El precio no puede ser negativo.';
        }

        if (!$errors) {
            if ($action === 'create') {
                $stmt = $conn->prepare(
                    'INSERT INTO evento (Name, Sport, `Date`, Location, Price, Description)
                     VALUES (:Name, :Sport, :Date, :Location, :Price, :Description)'
                );
                $stmt->execute($event);
                redirect_with_message('Evento creado correctamente.');
            }

            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

            if (!$id) {
                $errors[] = 'No se ha podido identificar el evento a actualizar.';
            } else {
                $event['Id'] = $id;
                $stmt = $conn->prepare(
                    'UPDATE evento
                     SET Name = :Name,
                         Sport = :Sport,
                         `Date` = :Date,
                         Location = :Location,
                         Price = :Price,
                         Description = :Description
                     WHERE Id = :Id'
                );
                $stmt->execute($event);
                redirect_with_message('Evento actualizado correctamente.');
            }
        }
    }
}