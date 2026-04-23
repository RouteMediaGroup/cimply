<?php
/*
 * Cimply.Work Business Framework
 * Version 4.0.2
 * Copyright (c) 2012-2026 RouteMedia®. All rights reserved.
 * Proprietary software. Use permitted only under valid commercial license.
 * Unauthorized copying, modification, distribution, or use is prohibited.
 * Contact: direkt@route-media.info
 */

namespace Cimply\Core\Request {

    use Cimply\Core\Core;
    use Cimply\Core\Validator\Validator;
    use Cimply\Interfaces\ICast;

    class Request implements ICast
    {
        use \Cast;

        private array|string $request = [];
        private mixed $getRequestData = null;
        private mixed $postRequestData = null;
        private mixed $putRequestData = null;
        private mixed $deleteRequestData = null;
        private mixed $optionsRequestData = null;
        private mixed $clientRequestMethod = null;
        private mixed $serverRequestMethod = null;
        private ?array $files = null;
        private ?string $request_uri = null;
        private mixed $filteredRequestUri = null;

        public array $method = [];
        public ?Validator $validate = null;
        public array $validationList = [];

        public function __construct(?Validator $validations = null)
        {
            $this->validate = $validations ?? new Validator();

            if (isset($_SERVER['REQUEST_URI'])) {
                $this->service();
            }
        }

        public static function Cast($mainObject, $selfObject = self::class): self
        {
            return Core::Cast($mainObject, $selfObject);
        }

        private function service(): void
        {
            $this->clientRequestMethod = $_SERVER['REQUEST_METHOD'] ?? null;
            $this->serverRequestMethod = $_SERVER['REQUEST_METHOD'] ?? null;

            switch ($_SERVER['REQUEST_METHOD'] ?? null) {
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

            $requestUri = (string)($_SERVER['REQUEST_URI'] ?? '');
            $this->request_uri = (string)\filter_var(\urldecode($requestUri), FILTER_SANITIZE_URL);
            $this->filteredRequestUri = $this->request_uri;

            $this->setRequest()
                ->getFileData()
                ->addSource();
        }

        private function setRequest(): self
        {
            $postRequestData = \file_get_contents('php://input');
            $this->request = ($postRequestData !== false && $postRequestData !== '') ? $postRequestData : [];
            return $this;
        }

        private function setGetRequest(): void
        {
            $this->getRequestData = \filter_input(INPUT_GET, 'method');
        }

        private function setPostRequest(): void
        {
            $this->postRequestData = \filter_input(INPUT_POST, 'method');
        }

        private function setPutRequest(): void
        {
            $raw = \file_get_contents('php://input');
            $data = [];
            if ($raw !== false && $raw !== '') {
                \parse_str($raw, $data);
            }
            $this->putRequestData = $data;
        }

        private function setDeleteRequest(): void
        {
            $raw = \file_get_contents('php://input');
            $data = [];
            if ($raw !== false && $raw !== '') {
                \parse_str($raw, $data);
            }
            $this->deleteRequestData = $data;
        }

        private function setOptionsRequest(): void
        {
            $raw = \file_get_contents('php://input');
            $data = [];
            if ($raw !== false && $raw !== '') {
                \parse_str($raw, $data);
            }
            $this->optionsRequestData = $data;
        }

        public function getGetData(): mixed
        {
            return $this->getRequestData;
        }

        public function getPostData(): mixed
        {
            return $this->postRequestData;
        }

        public function getPutData(): mixed
        {
            return $this->putRequestData;
        }

        public function getDeleteData(): mixed
        {
            return $this->deleteRequestData;
        }

        public function getOptionsData(): mixed
        {
            return $this->optionsRequestData;
        }

        public function filteredServerRequest(): mixed
        {
            return $this->serverRequestMethod;
        }

        public function filteredRequestUri(): mixed
        {
            return $this->filteredRequestUri;
        }

        public function getRequest(): array|string
        {
            return $this->request;
        }

        public function getFiles(): ?array
        {
            return $this->files;
        }

        public function getFileData(): self
        {
            if (isset($_FILES['file']['tmp_name'])) {
                $tmpNames = $_FILES['file']['tmp_name'];
                $fileData = null;

                if (\is_array($tmpNames)) {
                    $lastTmp = \end($tmpNames);
                    if (\is_string($lastTmp) && $lastTmp !== '' && \is_file($lastTmp)) {
                        $content = \file_get_contents($lastTmp);
                        $fileData = ($content !== false) ? $content : null;
                    }
                } elseif (\is_string($tmpNames) && $tmpNames !== '' && \is_file($tmpNames)) {
                    $content = \file_get_contents($tmpNames);
                    $fileData = ($content !== false) ? $content : null;
                }

                $post = \is_array($_POST ?? null) ? $_POST : [];
                $incomingFiles = $this->incomingFiles($_FILES);

                $this->files = \array_merge($post, $incomingFiles, ['Binary' => $fileData]);
            }

            return $this;
        }

        public function incomingFiles($files): array
        {
            if (!\is_array($files) || $files === []) {
                return ['files' => []];
            }

            $files2 = [];

            foreach ($files as $input => $infoArr) {
                if (!\is_array($infoArr)) {
                    continue;
                }

                $filesByInput = [];

                foreach ($infoArr as $key => $valueArr) {
                    if (\is_array($valueArr)) {
                        foreach ($valueArr as $i => $value) {
                            $filesByInput[$i][$key] = $value;
                        }
                    } else {
                        $filesByInput[] = $infoArr;
                        break;
                    }
                }

                if ($filesByInput !== []) {
                    $files2 = \array_merge($files2, $filesByInput);
                }
            }

            $files3 = [];
            foreach ($files2 as $file) {
                if (!\is_array($file)) {
                    continue;
                }

                $error = $file['error'] ?? UPLOAD_ERR_OK;
                if ((int)$error === UPLOAD_ERR_OK) {
                    $files3[] = $file;
                }
            }

            return ['files' => $files3];
        }

        public function getMixedData(): object
        {
            $response = $this->incomingFiles($_FILES);
            $response['request'] = \is_array($_REQUEST ?? null) ? $_REQUEST : [];
            return (object)$response;
        }

        public function addSource($item = null): self
        {
            if ($item === null) {
                if (\is_string($this->request) && $this->request !== '') {
                    $item = \JsonDeEncoder::Decode($this->request, true);
                } else {
                    $item = [];
                }
            }

            $this->validate->addSource($item);
            return $this;
        }

        public function addValidationRules($item = null): self
        {
            if (!\is_array($item) || $item === []) {
                return $this;
            }

            $name = \array_key_first($item);
            if ($name === null || !isset($item[$name])) {
                return $this;
            }

            $this->validate->AddRules(\Lists::ListOfObjects($item[$name]));
            return $this;
        }

        public function getValidations(): array
        {
            $requestString = \is_string($this->request) ? $this->request : '';
            $result = \Lists::ArrayList($requestString, 'dataObject');

            if (isset($result['dataObject']) && \is_array($result['dataObject'])) {
                foreach ($result['dataObject'] as $key => $value) {
                    $validation = clone $this->validate;
                    $validation->addSource($value)->AddRules($this->validate->GetRules());
                    $this->validationList[$key] = $validation;
                }

                return $this->validationList;
            }

            return [];
        }

        public function execute(): object
        {
            $requestData = \is_array($_REQUEST ?? null) ? $_REQUEST : [];
            $fileData = $this->incomingFiles($_FILES);

            return (object)$this->addSource(\array_merge($requestData, $fileData))
                ->validate
                ->run();
        }
    }
}