<?php

namespace Sledgehammer\Mvc;

use Exception;
use Sledgehammer\Mvc\Document\Xml;
use Sledgehammer\Core\Framework;
use Sledgehammer\Core\Json;

/**
 * Folder for basic CRUD operations on a Repository model.
 */
class CrudFolder extends Folder
{
    public $requireDataOnSave = 1; // int Controleer bij de create() & update() of er $_POST data is verstuurd.
    protected $model;
    protected $repository = 'default';

    /**
     * Wordt gebruik om te id uit de $_REQUEST te halen.  $idValue = $_POST[$this->id].
     *
     * @var string
     */
    protected $primaryKey = 'id';
    protected $maxRecursion = 0;

    /**
     * @param Record $record in static mode
     * @param array options  array('repository' => 'twitter', 'primaryKey' => 'customer_id')
     */
    public function __construct($model, $options = [])
    {
        parent::__construct();
        $this->handle_filenames_without_extension = true;
        $this->model = $model;
        foreach ($options as $option => $value) {
            $this->$option = $value;
        }
    }

    public function generateContent()
    {
        try {
            return parent::generateContent();
        } catch (Exception $e) {
            return static::error($e);
        }
    }

    public function index($format)
    {
        $repo = getRepository($this->repository);
        $collection = $repo->all($this->model);
        $data = $repo->export($this->model, $collection, $this->maxRecursion);

        return $this->format($data, $format);
    }

    public function file($filename)
    {
        $format = file_extension($filename, $id);
        if ($id === 'list') {
            return $this->index($format);
        }

        $repo = getRepository($this->repository);
        $instance = $repo->get($this->model, $id);
        $data = $repo->export($this->model, $instance, $this->maxRecursion);

        return $this->format($data, $format);
    }

    public function folder($folder, $filename = false)
    {
        $instance = $this->load($folder);
        if (isset($instance->$filename)) {
            $repo = getRepository($this->repository);
            $data = $repo->export('Order', $instance->$filename, $this->maxRecursion + 2);

            return $this->format($data, 'json');
        }

        return parent::folder($folder, $filename);
    }

    /**
     * Stuur de gegevens van het record naar de client.
     *
     * @throws Exception on failure
     *
     * @return Json
     */
    protected function read()
    {
        $instance = $this->load($_REQUEST[$this->primaryKey]);
        $data = $this->extract($instance, $this->maxRecursion);

        return static::success($data);
    }

    private function load($id)
    {
        $repo = getRepository($this->repository);

        return $repo->get($this->model, $id);
    }

    /**
     * @throws Exception on failure
     *
     * @return Json
     */
    private function update()
    {
        $instance = $this->load($_REQUEST[$this->primaryKey]);
        set_object_vars($instance, $this->getNewValues());
        $repo = getRepository($this->repository);
        $repo->save($this->model, $instance);

        return static::success();
    }

    /**
     * @throws Exception on failure
     *
     * @return Json
     */
    private function create()
    {
        $repo = getRepository($this->repository);
        $instance = $repo->create($this->model, $this->getNewValues());
        $repo->save($model, $instance);
        //		redirect();
//		return static::success($this->primaryKey => $instance->{$this->primaryKey});
    }

    /**
     * Aan de hand van de id bepalen of er een record toegevoegd of bijgewerkt moet worden.
     *
     * @throws Exception on failure
     *
     * @return Json
     */
    private function save()
    {
        if (empty($_REQUEST[$this->primaryKey])) {
            // Converteer een eventuele "" naar null
            if (value($_POST[$this->primaryKey]) === '') {
                $_POST[$this->primaryKey] = null;
            }

            return $this->create();
        }
        return $this->update();
    }

    /**
     * De record verwijderen.
     *
     * @throws Exception on failure
     *
     * @return Json
     */
    private function delete()
    {
        $repo = getRepository($this->repository);
        $repo->delete($this->model, $_POST[$this->primaryKey]);
        //throw new Exception('Verwijderen van '.$this->subject.' #'.$_POST[$this->primarykey].' is mislukt');
        return static::success();
    }

    /**
     * @todo $_POST data filteren zodat eventuele $_POST elementen geen fouten geven set_object_vars()
     *
     * @return array
     */
    protected function getNewValues()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            throw new Exception('Invalid request-method "'.$_SERVER['REQUEST_METHOD'].'", expecting "POST"');
        }
        if (count($_POST) < $this->requireDataOnSave) {
            throw new Exception('Er zijn onvoldoende gegevens verstuurd. (Minimaal '.$this->requireDataOnSave.' $_POST variabele is vereist)');
        }

        return $_POST;
    }

    protected function format($data, $format)
    {
        if ($format === 'xml') {
            return new Xml(Xml::build([$this->model => $data]));
        }
        return new Json($data);
    }


    /**
     * Reports the error/exception to the ErrorHandler and returns the error as Json object.
     * The javascript client should detect and report the error to the user:
     *   if (result.success !== true) { alert(result.error); }.
     *
     * @param string|Exception $error The error message or Exception
     * @param int              $http  The HTTP status code (defaults to 400 Bad Request)
     *
     * @return Json
     */
    protected static function error($error, $http = 400)
    {
        // if (headers_sent() === false && DebugR::isEnabled()) {
        //     ErrorHandler::instance()->html = false;
        // }
        if ($error instanceof Exception) {
            \Sledgehammer\report_exception($error);
            $error = $error->getMessage();
        } else {
            \Sledgehammer\warning($error);
        }

        return new Json([
            'success' => false,
            'error' => $error,
        ], [
            'http' => [
                'Status' => $http.' '.Framework::$statusCodes[$http],
                'Content-Type' => 'application/json'
                // 'Content-Type' => (ErrorHandler::instance()->html ? 'text/html;  charset=utf-8' : 'application/json'),
            ],
        ]);
    }

    /**
     * Short for "new Json(array('success' => true))".
     *
     * @param mixed $data [optional] The data payload
     *
     * @return Json
     */
    protected static function success($data = null)
    {
        if ($data === null) {
            return new Json(array(
                'success' => true,
            ));
        }

        return new Json(array(
            'success' => true,
            'data' => $data,
        ));
    }
}
