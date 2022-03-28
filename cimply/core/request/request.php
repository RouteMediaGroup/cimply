<?php
namespace Cimply\Core\Request {

    /**
     * Description of CIMPLY.WORK
     *
     * @author Michael Eckebrecht
     */

    use \Cimply\Core\{Core, Validator\Validator};
    use \Cimply\Interfaces\ICast;
    class Request implements ICast {

        private  
        $request = [],
        $getRequestData = null, $postRequestData = null, $putRequestData = null, $deleteRequestData = null, $optionsRequestData = null,
        $clientRequestMethod = null,
        $serverRequestMethod = null,
        $files = null,
        $request_uri = null;
        public $method = [], $validate = null, $validationList = [];
        
        public function __construct(Validator $validations = null) {
            if(!isset($validations)) {
                $this->validate = new Validator();
            } else {
                $this->validate = $validations;
            }
            !(isset($_SERVER['REQUEST_URI'])) ? : $this->service(); 
        }

        private function service() {
            switch($_SERVER['REQUEST_METHOD'] ?? null) {
                case 'GET':
                    $this->setGetRequest();
                    break;
                case 'POST':
                    $this->setPostRequest();
                    break;
                case 'PUT':
                    $this->setPutRequest();
                    break;
                case 'DELETE':
                    $this->setDeleteRequest();
                    break;
                case 'OPTIONS':
                    $this->setOptionsRequest();
                    break;
            }

            $this->request_uri = \filter_var(urldecode($_SERVER['REQUEST_URI']), FILTER_SANITIZE_URL);
            $this->setRequest()->getFileData()->addSource();            
        }

        private function setRequest() {
            $postRequestData = file_get_contents("php://input", false, stream_context_get_default(), 0);
            $this->request = isset($postRequestData) ? $postRequestData : [];
            return $this;
        }

        public final static function Cast($mainObject, $selfObject = self::class): self {
            return Core::Cast($mainObject, $selfObject);
        }

        private function setGetRequest() {
            $this->getRequestData = \filter_input(INPUT_GET, 'method');
        }
        private function setPostRequest() {
            $this->postRequestData = \filter_input(INPUT_POST, 'method');
        }
        private function setPutRequest() {
            parse_str(file_get_contents('php://input'), $this->putRequestData);            
        }
        private function setDeleteRequest() {
            parse_str(file_get_contents('php://input'), $this->deleteRequestData);
        }
        private function setOptionsRequest() {
            parse_str(file_get_contents('php://input'), $this->optionsRequestData);
        }
        
        function getGetData() {
            return $this->getRequestData;
        }

        function getPostData() {
            return $this->postRequestData;
        }
        
        function getPutData() {
            return $this->putRequestData;
        }
        
        function getDeleteData() {
            return $this->deleteRequestData;
        }
        
        function getOptionsData() {
            return $this->optionsRequestData;
        }
    
        function filteredServerRequest() {
            return $this->serverRequestMethod;
        }

        function filteredRequestUri() {
            return $this->filteredRequestUri;
        }

        public function getRequest() {
            return $this->request;
        }
        
        public function getFiles(): ?array {
            return $this->files;
        }

        public function getFileData(): self {
            if(isset($_FILES['file']['tmp_name'])) {
                ob_start();
                echo file_get_contents(end($_FILES['file']['tmp_name']));
                $FileData = ob_get_contents();
                ob_end_clean();
                $this->files = array_merge($_POST, $this->incomingFiles($_FILES), array("Binary" => $FileData));
            }
            return $this;
        }

        public function incomingFiles($files) {
            $files2 = [];
            foreach ($files as $input => $infoArr) {
                $filesByInput = [];
                foreach ($infoArr as $key => $valueArr) {
                    if (is_array($valueArr)) {
                        foreach($valueArr as $i=>$value) {
                            $filesByInput[$i][$key] = $value;
                        }
                    }
                    else {
                        $filesByInput[] = $infoArr;
                        break;
                    }
                }
                $files2 = array_merge($files2, $filesByInput);
            }
            $files3 = [];
            foreach($files2 as $file) {
                if (!$file['error']) $files3[] = $file;
            }
            return ['files' => $files3];
        }

        public function getMixedData():Object {
            $response = [];
            $response = $this->incomingFiles($_FILES) ?? [];
            $response['request'] = $_REQUEST ?? [];
            return (object)$response;
        }

        public function addSource($item = null) {
            isset($item) ? $item : $item = \JsonDeEncoder::Decode($this->request, true);
            $this->validate->addSource($item);
            return $this;
        }

        public function addValidationRules($item = null) {
            $name = key($item) ? : null;
            $this->validate->AddRules(\Lists::ListOfObjects($item[$name]));
            return $this;
        }

        public function getValidations() {
            $result = \Lists::ArrayList($this->request, 'dataObject');
            if(isset($result['dataObject'])) {
                foreach($result['dataObject'] as $key => $value) {
                    $validation = $this->validate;
                    $validation->addSource($value)->AddRules($this->validate->GetRules());
                    $this->validationList[$key] = $validation;
                }
                return $this->validationList;
            }
            return [];
        }

        public function execute():object {
            return (object)$this->addSource(array_merge($_REQUEST, $this->incomingFiles($_FILES)))->validate->run();
        }
    }
}