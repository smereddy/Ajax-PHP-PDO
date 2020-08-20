<?php

final class Database
{

    private $db_host = 'localhost';
    private $db_name = 'backlog';
    private $db_username = 'root';
    private $db_password = 'root';


    public function dbConnection()
    {

        try {
            $conn = new PDO('mysql:host=' . $this->db_host . ';dbname=' . $this->db_name, $this->db_username, $this->db_password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conn;
        } catch (PDOException $e) {
            echo "Connection error " . $e->getMessage();
            exit;
        }

    }
}

final class Backlog
{
    public $conn;
    public $table_name = 'back_log';
    public $id;
    public $requestor_id;
    public $tool_name;
    public $type;
    public $description;
    public $priority;
    public $date_filed;
    public $status;
    public $fix_confirm;
    public $date_closed;
    public $tester;
    public $image_name;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->dbConnection();
    }

    public function all()
    {
        $query = 'SELECT * FROM ' . $this->table_name . '';
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function find(int $id)
    {
        $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);

        $query = 'SELECT * FROM ' . $this->table_name . ' WHERE id=:id';

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function create()
    {
        $query = 'INSERT INTO ' . $this->table_name . ' (tool_name, type, description, priority, date_filed, status, tester, image_name, date_closed) VALUES 
        (:tool_name, :type, :description, :priority, :date_filed, :status, :tester, :image_name, :date_closed)';

        try {

            $stmt = $this->conn->prepare($query);

            // create adHoc query to check if student number is unique


            $tool_name = filter_var($this->tool_name, FILTER_SANITIZE_STRING);
            $type = filter_var($this->type, FILTER_SANITIZE_STRING);
            $description = filter_var($this->description, FILTER_SANITIZE_STRING);
            $priority = filter_var($this->priority, FILTER_SANITIZE_STRING);
            $date_filed = date('Y-m-d');
            $status = filter_var($this->status, FILTER_SANITIZE_STRING);
            $tester = filter_var($this->tester, FILTER_SANITIZE_STRING);
            $image_name = filter_var($this->image_name, FILTER_SANITIZE_STRING);
            if($status=="Closed"){
                $date_closed = date('Y-m-d');
                $stmt->bindParam(':date_closed', $date_closed);
            }else{
                $date_closed = null;
                $stmt->bindParam(':date_closed', $date_closed, PDO::PARAM_NULL);
            }


            $stmt->bindParam(':tool_name', $tool_name);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':type', $type);
            $stmt->bindParam(':priority', $priority);
            $stmt->bindParam(':date_filed', $date_filed);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':tester', $tester);
            $stmt->bindParam(':image_name', $image_name);

            $stmt->execute();

            return $this->conn->lastInsertId();
        } catch (\Exception $exception) {
            JsonResponse(['error' => $exception], 400);
        }
    }

    public function update()
    {

        $query = 'UPDATE ' . $this->table_name . '  set tool_name = :tool_name,
        type = :type, description = :description, priority = :priority,
        status = :status, date_closed = :date_closed, tester = :tester,
        image_name = :image_name WHERE id = :id';

        try {

            $stmt = $this->conn->prepare($query);


            $id = filter_var($this->id, FILTER_SANITIZE_NUMBER_INT);
            $tool_name = filter_var($this->tool_name, FILTER_SANITIZE_STRING);
            $type = filter_var($this->type, FILTER_SANITIZE_STRING);
            $description = filter_var($this->description, FILTER_SANITIZE_STRING);
            $priority = filter_var($this->priority, FILTER_SANITIZE_STRING);
            $status = filter_var($this->status, FILTER_SANITIZE_STRING);
            $tester = filter_var($this->tester, FILTER_SANITIZE_STRING);
            $image_name = filter_var($this->image_name, FILTER_SANITIZE_STRING);

            $stmt->bindParam(':tool_name', $tool_name);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':type', $type);
            $stmt->bindParam(':priority', $priority);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':tester', $tester);
            $stmt->bindParam(':image_name', $image_name);


            if($status=="Closed"){
                $date_closed = date('Y-m-d');
                $stmt->bindParam(':date_closed', $date_closed);
            }else{
                $date_closed = null;
                $stmt->bindParam(':date_closed', $date_closed, PDO::PARAM_NULL);
            }
            $stmt->execute();

            return $stmt->rowCount();

        } catch (\Exception $exception) {
            JsonResponse(['error' => $exception], 400);
        }
    }

    public function destroy(int $id)
    {
        $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);
        $query = 'DELETE FROM ' . $this->table_name . ' where id=:id';

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);

        $stmt->execute();

        return $stmt->rowCount();
    }
}

/*
 * Helper functions
 */
function JsonResponse($data, $status = 200)
{
    /*
     * This function is used to send a JsonResponse back to the client
     */
    http_response_code($status);
    echo json_encode($data);
    exit();
}

class BACKLOG_API{

}

$request = $_SERVER['REQUEST_METHOD'];


switch ($request) {
    case 'GET':
        getmethod();
        break;
    case 'POST':
        if(isset($_POST['id'])){
            putmethod($_POST);
        }
        elseif (isset($_POST['del_id'])){
            deletemethod($_POST);
        }
        else{
            postmethod($_POST);
        }
        break;

    default:
        echo '{"name": "data not found"}';
        break;
}
//data read part are here
function getmethod()
{
    if (isset($_GET['id'])) {
        //IF HAS ID PARAMETER
        $id = $_GET['id'] ?? 0;

        $backlogObject = new Backlog();

        $backlog = $backlogObject->find($id);
        if (!$backlog) {
            JsonResponse(['error' => 'Resource Not Found'], 404);
        }

        JsonResponse(['data' => $backlog]);

    } else {
        $backlogObject = new Backlog();

        $backlog = $backlogObject->all();

        JsonResponse(['data' => $backlog]);
    }
}

//data insert part are here
function postmethod($data)
{

    $tool_name = $data['tool_name'];
    $type = $data['type'];
    $description = $data['description'];
    $priority = $data['priority'];
    $status = $data['status'];
    $tester = $data['tester'];
    $image_name = $data['image_name'];

    $backlog = new Backlog();
    $backlog->tool_name = $tool_name;
    $backlog->type = $type;
    $backlog->description = $description;
    $backlog->priority = $priority;
    $backlog->status = $status;
    $backlog->tester = $tester;
    $backlog->image_name = $image_name;


    $result = $backlog->create();


    if ((int)$result <= 0) {
        JsonResponse(['error' => 'The request could not be completed'], 400);
    }

// return to user $_POST data or re-query
    JsonResponse(['data' => $backlog->find($result)], 201);
}

//data edit part are here
function putmethod($data)
{


    $id = $data['id'] ?? 0;

    $backlogObject = new Backlog();

    $backlog = $backlogObject->find($id);

    if (!$backlog) {
        JsonResponse(['error' => 'Resource Not Found'], 404);
    }

    $requestor_id = $data['requestor_id'];
    $tool_name = $data['tool_name'];
    $type = $data['type'];
    $description = $data['description'];
    $priority = $data['priority'];
    $date_filed = $data['date_filed'];
    $status = $data['status'];
    $fix_confirm = $data['fix_confirm'];
    $date_closed = $data['date_closed'];
    $tester = $data['tester'];
    $image_name = $data['image_name'];

    $backlog = new Backlog();

    $backlog->id = $id;
    $backlog->requestor_id = $requestor_id;
    $backlog->tool_name = $tool_name;
    $backlog->type = $type;
    $backlog->description = $description;
    $backlog->priority = $priority;
    $backlog->date_filed = $date_filed;
    $backlog->status = $status;
    $backlog->fix_confirm = $fix_confirm;
    $backlog->date_closed = $date_closed;
    $backlog->tester = $tester;
    $backlog->image_name = $image_name;
    $result = $backlog->update();


    if (!$result) {
        JsonResponse(['error' => 'The request could not be completed. Are you sure you updated the data?'], 400);
    }

    // return to user $_POST data or re-query
    JsonResponse(['data' => $backlog->find($id)]);

}

//delete method are here
function deletemethod($data)
{
    $id = $data['del_id'] ?? 0;

    $backlogObject = new Backlog();

    $backlog = $backlogObject->find($id);
    if (!$backlog) {
        JsonResponse(['error' => 'Resource Not Found'], 404);
    }

    $result = $backlogObject->destroy($id);

    if (!$result) {
        JsonResponse(['error' => 'The request could not be completed'], 400);
    }

    JsonResponse(['message' => 'Resource has been deleted'], 204);
}

?>