<?php
final class Database
{/*
 * Database class to connect with the database
*/
    private $db_host = 'localhost';
    private $db_name = 'backlog'; // Please change this if you have a different database
    private $db_username = 'root';
    private $db_password = 'root';

    public function dbConnection()
    {

        try
        {
            $conn = new PDO('mysql:host=' . $this->db_host . ';dbname=' . $this->db_name, $this->db_username, $this->db_password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conn;
        }
        catch(PDOException $e)
        {
            JsonResponse(['error' => "Connection error " . $e->getMessage() ], 500);
            exit;
        }

    }
}

final class Backlog
{/*
 * Implementing a Backlog Model object and functions to perform CRUD operations
*/
    public $conn;
    public $table_name = 'back_log';
    public $id;
    public $tool_name;
    public $type;
    public $description;
    public $priority;
    public $status;
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
        $stmt = $this
            ->conn
            ->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function find(int $id)
    {
        $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);

        $query = 'SELECT * FROM ' . $this->table_name . ' WHERE id=:id';

        $stmt = $this
            ->conn
            ->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function create()
    {
        $query = 'INSERT INTO ' . $this->table_name . ' (tool_name, type, description, priority, date_filed, status, tester, image_name, date_closed) VALUES 
        (:tool_name, :type, :description, :priority, :date_filed, :status, :tester, :image_name, :date_closed)';

        try
        {

            $stmt = $this
                ->conn
                ->prepare($query);

            $tool_name = filter_var($this->tool_name, FILTER_SANITIZE_STRING);
            $type = filter_var($this->type, FILTER_SANITIZE_STRING);
            $description = filter_var($this->description, FILTER_SANITIZE_STRING);
            $priority = filter_var($this->priority, FILTER_SANITIZE_STRING);
            $date_filed = date('Y-m-d');
            $status = filter_var($this->status, FILTER_SANITIZE_STRING);
            $tester = filter_var($this->tester, FILTER_SANITIZE_STRING);
            $image_name = filter_var($this->image_name, FILTER_SANITIZE_STRING);

            $date_para = ':date_closed';
            if ($status === "Closed")
            {
                $date_closed = date('Y-m-d');
                $stmt->bindParam($date_para, $date_closed);
            }
            else
            {
                $date_closed = null;
                $stmt->bindParam($date_para, $date_closed, PDO::PARAM_NULL);
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

            return $this
                ->conn
                ->lastInsertId();
        }
        catch(Exception $exception)
        {
            JsonResponse(['error' => $exception], 400);
        }
    }

    public function update()
    {

        $query = 'UPDATE ' . $this->table_name . '  set tool_name = :tool_name,
        type = :type, description = :description, priority = :priority,
        status = :status, date_closed = :date_closed, tester = :tester,
        image_name = :image_name WHERE id = :id';

        try
        {

            $stmt = $this
                ->conn
                ->prepare($query);

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

            $date_para = ':date_closed';
            if ($status === "Closed")
            {
                $date_closed = date('Y-m-d');
                $stmt->bindParam($date_para, $date_closed);
            }
            else
            {
                $date_closed = null;
                $stmt->bindParam($date_para, $date_closed, PDO::PARAM_NULL);
            }
            $stmt->execute();

            return $stmt->rowCount();

        }
        catch(\Exception $exception)
        {
            JsonResponse(['error' => $exception], 400);
        }
    }

    public function destroy(int $id)
    {
        $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);
        $query = 'DELETE FROM ' . $this->table_name . ' where id=:id';

        $stmt = $this
            ->conn
            ->prepare($query);
        $stmt->bindParam(':id', $id);

        $stmt->execute();

        return $stmt->rowCount();
    }
}

final class RequestHandler
{/*
 * Creating a custom Request handler to perform GET, POST, PUT, DELETE
*/
    public function __construct(Backlog $backlog)
    {
        $this->backlogObject = $backlog;
    }

    //data read is are here
    public function GET_method()
    {
        if (isset($_GET['id']))
        {
            //IF HAS ID PARAMETER
            $id = $_GET['id']??0;

            $backlog = $this
                ->backlogObject
                ->find($id);

            if (!$backlog)
            {
                JsonResponse(['error' => 'Resource Not Found'], 404);
            }

            JsonResponse(['data' => $backlog]);

        }
        else
        {

            $backlog = $this
                ->backlogObject
                ->all();

            JsonResponse(['data' => $backlog]);
        }
    }

    //data insert part is here
    public function POST_method($data)
    {
        //Upload file
        $uploadedFile = null;

        if (!empty($_FILES["file"]["name"]))
        {

            $uploadedFile = $this->fileUpload();

        }
        $tool_name = $data['tool_name'];
        $type = $data['type'];
        $description = $data['description'];
        $priority = $data['priority'];
        $status = $data['status'];
        $tester = $data['tester'];
        $image_name = $uploadedFile;

        $backlog = $this->backlogObject;
        $backlog->tool_name = $tool_name;
        $backlog->type = $type;
        $backlog->description = $description;
        $backlog->priority = $priority;
        $backlog->status = $status;
        $backlog->tester = $tester;
        $backlog->image_name = $image_name;

        $result = $backlog->create();

        if ((int)$result <= 0)
        {
            JsonResponse(['error' => 'The request could not be completed'], 400);
        }

        // return to user $_POST data or re-query
        JsonResponse(['data' => $backlog->find($result) ], 201);
    }

    //data edit part is here
    public function PUT_method($data)
    {

        $id = $data['id']??0;

        $backlog = $this
            ->backlogObject
            ->find($id);

        if (!$backlog)
        {
            JsonResponse(['error' => 'Resource Not Found'], 404);
        }

        $uploadedFile = null;

        if (!empty($_FILES["file"]["name"]))
        {
            $uploadedFile = $this->fileUpload();
        }

        if ($uploadedFile == null && $backlog->image_name != null)
        {
            $uploadedFile = $backlog->image_name;
        }

        $tool_name = $data['tool_name'];
        $type = $data['type'];
        $description = $data['description'];
        $priority = $data['priority'];
        $status = $data['status'];
        $fix_confirm = $data['fix_confirm'];
        $tester = $data['tester'];
        $image_name = $uploadedFile;

        $backlog = $this->backlogObject;

        $backlog->id = $id;
        $backlog->tool_name = $tool_name;
        $backlog->type = $type;
        $backlog->description = $description;
        $backlog->priority = $priority;
        $backlog->status = $status;
        $backlog->fix_confirm = $fix_confirm;
        $backlog->tester = $tester;
        $backlog->image_name = $image_name;
        $result = $backlog->update();

        if (!$result)
        {
            JsonResponse(['error' => 'The request could not be completed. Are you sure you updated the data?'], 400);
        }

        // return to user $_POST data or re-query
        JsonResponse(['data' => $backlog->find($id) ]);

    }

    //delete method is here
    public function DELETE_method($data)
    {
        $id = $data['del_id']??0;

        $backlogObject = $this->backlogObject;

        $backlog = $backlogObject->find($id);
        if (!$backlog)
        {
            JsonResponse(['error' => 'Resource Not Found'], 404);

        }

        $result = $backlogObject->destroy($id);

        if (!$result)
        {
            JsonResponse(['error' => 'The request could not be completed'], 400);
        }

        JsonResponse(['message' => 'Resource has been deleted'], 204);
    }

    private function fileUpload()
    {

        // File path config
        $uploadDir = './';

        $fileName = basename($_FILES["file"]["name"]);
        $targetFilePath = $uploadDir . $fileName;
        $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

        // Allow certain file formats
        $allowTypes = array(
            'jpg',
            'png',
            'jpeg'
        );
        if (in_array($fileType, $allowTypes))
        {
            // Upload file to the server
            if (move_uploaded_file($_FILES["file"]["tmp_name"], $targetFilePath))
            {
                return $fileName;
            }
            else
            {
                JsonResponse(['error' => 'Sorry, there was an error uploading your file'], 405);
            }
        }
        else
        {
            JsonResponse(['error' => 'Sorry, only JPG, JPEG, & PNG files are allowed to upload.'], 405);
        }

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

$request = $_SERVER['REQUEST_METHOD'];

$backlog = new Backlog();
$custom_request = new RequestHandler($backlog);

switch ($request)
{
    /*
     * Find Error based on request and data
    */
    case 'GET':
        $custom_request->GET_method();
        break;
    case 'POST':
        if (isset($_POST['id']))
        {
            $custom_request->PUT_method($_POST);
        }
        elseif (isset($_POST['del_id']))
        {
            $custom_request->DELETE_method($_POST);
        }
        else
        {
            $custom_request->POST_method($_POST);
        }
        break;

    default:
        JsonResponse(['error' => 'Resource Not Found'], 404);
        break;
}
