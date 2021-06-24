<?php

namespace Liveet\Controllers;

use Aws\S3\S3Client;
use Liveet\Domain\MailHandler;
use Rashtell\Domain\CodeLibrary;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Rashtell\Domain\KeyManager;
use Rashtell\Domain\MCrypt;
use Rashtell\Domain\JSON;
use Liveet\Models\BaseModel;
use DateTime;
use Illuminate\Database\Eloquent\Model;
use Liveet\Domain\Constants;
use Liveet\Models\EventModel;
use Psr\Http\Message\UploadedFileInterface;

class BaseController
{
    protected function getValidJsonOrError($request)
    {
        $json = new JSON();

        $data = $request->getParsedBody();
        $data = isset($data) ? $data : $request->getBody();


        $validJson = $json->jsonFormat($data);

        if ($validJson == NULL) {
            $error = array("errorMessage" => "The parameter is not a valid objects", "errorStatus" => 1, "statusCode" => 400);

            return ["error" => $error, "data" => null];
        }

        if (!isset($validJson->data)) {
            $error = array("errorMessage" => "The request object does not conform to standard", "errorStatus" => 1, "statusCode" => 400);

            return ["error" => $error, "data" => null];
        }

        return ["data" => $validJson->data, "error" => ""];
    }

    protected function getPageNumOrError($request)
    {
        $data = $request->getAttributes();
        $page = 1;

        if (!(isset($data["page"]))) {
            // $error = array("errorMessage" => "Page is required", "errorStatus" => 1, "statusCode" => 400);

            // return ["error" => $error, "page" => null];
            $page = 1;
        } else {
            $page = $data["page"];
        }


        if (!(is_numeric($page) || (int) $page < 0)) {
            // $error = array("errorMessage" => "The page number is invalid", "errorStatus" => 1, "statusCode" => 400);

            // return ["error" => $error, "page" => null];
            $page = 1;
        }

        return ["page" => $page, "error" => null];
    }

    protected function getPageLimit($request)
    {
        $data = $request->getAttributes();

        $limit = isset($data["limit"]) && is_numeric($data["limit"]) ? $data["limit"] : 1000000000;

        ["page" => $page, "error" => $error] = $this->getPageNumOrError($request);
        // $start = ($page - 1) * $limit;

        return ["limit" => $limit, "error" => $error];
    }

    protected function getDateOrError($request)
    {
        $data = $request->getAttributes();

        if (!(isset($data["fromDate"]) && isset($data["toDate"]))) {
            $error = array("errorMessage" => "Date range is required", "errorStatus" => 1, "statusCode" => 400);

            return ["error" => $error, "page" => []];
        }

        $fromDate = $data["fromDate"];
        $toDate = $data["toDate"];

        if (!(is_numeric($fromDate) || is_numeric($toDate))) {
            $error = array("errorMessage" => "The date is invalid", "errorStatus" => 1, "statusCode" => 400);

            return ["error" => $error, "page" => []];
        }

        return ["fromDate" => $fromDate, "toDate" => $toDate, "error" => ""];
    }

    protected function getRouteParams($request, $details = null)
    {
        $data = $request->getAttributes();

        if (!$details) {
            return $data;
        }

        $existData = ["error" => null];

        foreach ($details as $detail) {
            if (!isset($data[$detail])) {

                $error = array("errorMessage" => "Invalid request: " . $detail . " not set", "errorStatus" => 1, "statusCode" => 400);

                return array_merge($existData, ["error" => $error]);
            }

            $existData = array_merge($existData, [$detail => $data[$detail]]);
        }

        return $existData;

        // return $request->getAttributes();
    }

    protected function getRouteTokenOrError($request)
    {
        if (!isset($request->getAttributes()["token"])) {
            $error = array("errorMessage" => "Invalid url", "errorStatus" => 1, "statusCode" => 400);
            return ["error" => $error, "token" => ""];
        }

        $token = $request->getAttributes()["token"];

        return ["data" => $token, "error" => null];
    }

    protected function valuesExistsOrError($data, array $details = [])
    {
        $existData = ["error" => null];

        foreach ($details as $detail) {
            if (!isset($data->$detail)) {
                $json = new JSON();

                $error = array("errorMessage" => "All fields are required: " . $detail . " not set", "errorStatus" => 1, "statusCode" => 400);

                $existData = array_merge($existData, ["error" => $error]);
                return $existData;
            }

            $existData = array_merge($existData, [$detail => $data->$detail]);
        }

        foreach ($data as $key => $value) {
            $existData[$key] = $value;
        }

        return $existData;
    }

    public static function getTokenInputsFromRequest($request)
    {
        $token = static::getToken($request);

        if (!$token) {
            return [];
        }

        $authDetails = (new BaseModel)->getTokenInputs($token);

        return $authDetails;
    }

    public static function getToken($request)
    {
        $headers = $request->getHeaders();

        $authorization = isset($headers["Token"]) ? $headers["Token"] : (isset($headers["token"]) ? $headers["token"] : null);

        if (!$authorization) {
            return null;
        }

        $token = isset($authorization[0]) ? $authorization[0] : null;

        $tokenArr = $token ? explode(" ", $token) : [];

        return isset($tokenArr[1]) ? $tokenArr[1] : null;
    }

    /**
     * Parses base64 images to url
     * 
     * $accountOptions["mediaOptions"=>[
     *  ["mediaKey"=>"", "mediaPrefix"=>"", multiple=>false]
     * ]
     *
     * @param array $data
     * @param array $accountOptions
     * @return array
     */
    public function parseImage($data, $accountOptions = [])
    {
        if (isset($accountOptions["mediaOptions"])) {
            foreach ($accountOptions["mediaOptions"] as $mediaOption) {

                $mediaKey = $mediaOption["mediaKey"];

                if (!isset($data[$mediaKey])) {

                    $mediaExtError = ["errorMessage" => $mediaKey . " not set", "errorStatus" => 1, "statusCode" => 400];

                    // $data["error"] = $mediaExtError;
                    // break;

                    continue;
                }

                $return = [];

                $event_id = $data["event_id"] ?? "";

                if (
                    (isset($mediaOption["multiple"]) && $mediaOption["multiple"] && gettype($data[$mediaKey]) == "array")
                    || gettype($data[$mediaKey]) == "array"
                ) {
                    foreach ($data[$mediaKey] as $mediaKeyy) {
                        $return[] = $this->handleLiveetParseImage($mediaOption, $mediaKeyy, $event_id);
                    }

                    $data[$mediaKey] = $return;
                } else {
                    $return = $this->handleLiveetParseImage($mediaOption, $data[$mediaKey], $event_id);

                    $data[$mediaKey] = $return["path"] ?? $return["url"] ?? "";
                    $data[$mediaKey . "Type"] = $return["type"] ?? "";
                }
            }
        }

        return $data;
    }

    public function handleLiveetParseImage($mediaOption, $media, $event_id)
    {
        if (strrpos($media, "data:image/") !== 0 && strrpos($media, "data:video/") !== 0) {
            $data["url"] = $media;
            return $data;
        }

        $mediaPrefix = isset($mediaOption["mediaPrefix"]) ? $mediaOption["mediaPrefix"] . " - " : "";

        // $mediaName = $mediaPrefix . bin2hex(random_bytes(8));
        // $mediaName .=  (new DateTime())->getTimeStamp();
        $mediaName =  rand(00000000, 99999999);

        $event_id = (int)$event_id;
        $event = (new EventModel)->find($event_id); //get event code from event id
        $event_code = "";
        if ($event) {
            $event_code = $event["event_code"];
        } else {
            $data["error"] = "not found";
        }

        $event_code_dir = $event_code ? "$event_code/" : "";
        $event_code_key = $event_code ? "$event_code-" : "";

        $mediaKey = $mediaOption["mediaKey"];
        $mediaExtType = $this->getFileTypeOfBase64($media);
        $mediaExtType = strtolower($mediaExtType);
        $key = "$mediaPrefix$event_code_key$mediaName.$mediaExtType";
        $mediaPath = "https://liveet-media.s3-us-west-2.amazonaws.com/$event_code_dir" . $key;

        $aws_key = $_ENV["AWS_KEY"];
        $aws_secret = $_ENV["AWS_SECRET"];

        $mediaType = "";
        if (!in_array($mediaExtType, Constants::IMAGE_TYPES_ACCEPTED) && !in_array($mediaExtType, Constants::VIDEO_TYPES_ACCEPTED)) {
            $mediaExtError = "Unsupport media type. ";
            $mediaExtError .= $this->getSupportedMediaTypes();

            $data["error"] = [$mediaExtError];
            return $data;
        }
        if (in_array($mediaExtType, Constants::IMAGE_TYPES_ACCEPTED)) {
            $mediaType = "image";
        }
        if (in_array($mediaExtType, Constants::VIDEO_TYPES_ACCEPTED)) {
            $mediaType = "video";
        }

        $bucket = "liveet-media/$event_code";
        if (!$event_code) {
            $bucket = "liveet-media";
        }
        $contentType = $this->getContentType($mediaExtType, $mediaType);

        try {
            $s3 = new S3Client([
                'region'  => 'us-west-2',
                'version' => 'latest',
                'credentials' => [
                    'key'    => $aws_key,
                    'secret' => $aws_secret,
                ]
            ]);

            $s3_result = $s3->putObject([
                'Bucket' => $bucket,
                'Key'    => $key,
                'Body'   => $media,
                'ACL'    => 'public-read',
                'ContentType'    => $contentType
            ]);
        } catch (\Exception $e) {
            $data["error"] = $e->getMessage();
            return $data;
        }

        $data["type"] = $mediaType;
        $data["path"] = $mediaPath;

        return $data;
    }

    public function handleParseImage($mediaOption, $media)
    {
        $mediaKey = $mediaOption["mediaKey"];
        $mediaPath = Constants::IMAGE_PATH;

        if (strrpos($media, "data:image/") !== 0 || strrpos($media, "data:video/") !== 0) {
            // continue;
            $data["url"] = $media;
            return $data;
        }

        $mediaPrefix = isset($mediaOption["mediaPrefix"]) ? $mediaOption["mediaPrefix"] . " - " : "";

        $mediaName = bin2hex(random_bytes(8));
        $mediaName .= $mediaPrefix . (new DateTime())->getTimeStamp();

        $mediaExtType = $this->getFileTypeOfBase64($media);
        $mediaExtType = strtolower($mediaExtType);
        if (!in_array($mediaExtType, Constants::IMAGE_TYPES_ACCEPTED) && !in_array($mediaExtType, Constants::VIDEO_TYPES_ACCEPTED)) {
            $mediaExtError = "Unsupport media type. ";
            $mediaExtError .= $this->getSupportedMediaTypes();

            $data["error"] = [$mediaExtError];
            return $data;
        }

        $newImagePath = "$mediaPath$mediaName.$mediaExtType";

        file_put_contents($newImagePath, file_get_contents($media));

        $mediaTypeKey = $mediaKey . "_type";

        $data["type"] = $mediaExtType;
        $data["path"] = $newImagePath;

        return $data;
    }

    public function getFileTypeOfBase64($data_media)
    {
        if (!empty($data_media) && strrpos($data_media, ";base64,") > 0) :
            $string_pieces = explode(";base64,", $data_media);

            $media_type_pieces = ["", ""];
            if (strrpos($string_pieces[0], "image/") > 0) :
                $media_type_pieces = explode("image/", $string_pieces[0]);
            endif;
            if (strrpos($string_pieces[0], "video/") > 0) :
                $media_type_pieces = explode("video/", $string_pieces[0]);
            endif;

            return  $media_type_pieces[1];
        endif;

        if (!empty($data_media) && strrpos($data_media, ";charset=UTF-8,") > 0) :
            $string_pieces = explode(";charset=UTF-8,", $data_media);

            $media_type_pieces = ["", ""];
            if (strrpos($string_pieces[0], "image/") > 0) :
                $media_type_pieces = explode("image/", $string_pieces[0]);
            endif;
            if (strrpos($string_pieces[0], "video/") > 0) :
                $media_type_pieces = explode("video/", $string_pieces[0]);
            endif;

            $media_type_pieces = $media_type_pieces[1];
            $media_type_pieces = explode("+", $media_type_pieces);

            return  $media_type_pieces[0];
        endif;
    }

    public function convertBase64ToImage($base64_code, $path, $media_name = null)
    {

        if (!empty($base64_code) && !empty($path)) :

            $string_pieces = explode(";base64,", $base64_code);

            $media_type_pieces = ["", ""];
            if (strrpos($string_pieces[0], "image/") > 0) :
                $media_type_pieces = explode("image/", $string_pieces[0]);
            endif;
            if (strrpos($string_pieces[0], "video/") > 0) :
                $media_type_pieces = explode("video/", $string_pieces[0]);
            endif;

            $media_type = $media_type_pieces[1];

            /*@ Create full path with image name and extension */
            $store_at = $path . md5(uniqid()) . "." . $media_type;

            /*@ If image name available then use that  */
            if (!empty($media_name)) :
                $store_at = $path . $media_name . "." . $media_type;
            endif;

            $decoded_string = base64_decode($string_pieces[1]);

            file_put_contents($store_at, $decoded_string);

        endif;
    }

    public function getSupportedMediaTypes()
    {
        $mediatypes = "Supported image types are ";
        foreach (Constants::IMAGE_TYPES_ACCEPTED as $acceptedImageType) {
            $appender = array_search($acceptedImageType, Constants::IMAGE_TYPES_ACCEPTED) === sizeof(Constants::IMAGE_TYPES_ACCEPTED) - 1 ? "." : ", ";
            $mediatypes .= $acceptedImageType . $appender;
        }

        $mediatypes .= " And supported video types are ";
        foreach (Constants::VIDEO_TYPES_ACCEPTED as $acceptedVideoType) {
            $appender = array_search($acceptedVideoType, Constants::VIDEO_TYPES_ACCEPTED) === sizeof(Constants::VIDEO_TYPES_ACCEPTED) - 1 ? "." : ", ";
            $mediatypes .= $acceptedVideoType . $appender;
        }

        return $mediatypes;
    }

    public function getContentType($filetype, $mediaType)
    {
        $return = "$mediaType/$filetype";

        if ($filetype == 'mp4') {
            $return = "$mediaType/$filetype";
        }
        if ($filetype == 'avi') {
            $return = "$mediaType/x-msvideo";
        }
        if ($filetype == 'flv') {
            $return = "$mediaType/x-flv";
        }
        if ($filetype == 'mov') {
            $return = "$mediaType/quicktime";
        }

        return $return;
    }



    /**
     * Moves the uploaded file to the upload directory and assigns it a unique name
     * to avoid overwriting an existing uploaded file.
     *
     * @param string $directory The directory to which the file is moved
     * @param UploadedFileInterface $uploadedFile The file uploaded file to move
     *
     * @return string The filename of moved file
     */
    function handleUploadMedias($request)
    {
        // $directory = $this->get('upload_directory');
        $directory = "assets/medias";
        $uploadedFiles = $request->getUploadedFiles();
        $event_id = $request->getParsedBody()["eventID"] ?? "";

        $outputs = [];

        // handle single input with multiple file uploads
        $i = 0;
        foreach ($uploadedFiles as $uploadedFileName => $uploadedFile) {
            if (gettype($uploadedFile) == "object") {
                $this->uploadFile(
                    $uploadedFile,
                    $directory,
                    function ($output) use (&$outputs, $directory, $event_id) {
                        $outputs[] = $this->uploadMediaCallback($output, $directory, $event_id);
                    }
                );
            }

            if (gettype($uploadedFile) == "array") {
                foreach ($uploadedFile as $index => $uploadedFil) {
                    $this->uploadFile(
                        $uploadedFil,
                        $directory,
                        function ($output) use (&$outputs, $directory, $event_id) {
                            $outputs[] = $this->uploadMediaCallback($output, $directory, $event_id);
                        }
                    );
                }
            }
        }

        return $outputs;
    }

    function uploadFile($uploadedFile, $directory, $callback)
    {
        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
            $fileDetails = $this->moveUploadedFile($directory, $uploadedFile);

            $callback($fileDetails);
        }
    }

    function moveUploadedFile(string $directory, UploadedFileInterface $uploadedFile)
    {
        $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);

        // see http://php.net/manual/en/function.random-bytes.php
        $basename = bin2hex(random_bytes(8));
        $basename .= (new DateTime())->getTimeStamp();
        $filename = sprintf('%s.%0.8s', $basename, $extension);

        $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

        return ["filename" => $filename, "filetype" => $extension];
    }

    function uploadMediaCallback($mediaDetails, $directory, $event_id = 0)
    {
        ["filename" => $filename, "filetype" => $filetype] = $mediaDetails;
        $filePath = $directory . "/" . $filename;

        // $mediaName = bin2hex(random_bytes(8));
        // $mediaName .=  (new DateTime())->getTimeStamp();
        $mediaName =  rand(00000000, 99999999);

        $event_id = (int)$event_id;
        $event = (new EventModel)->find($event_id); //get event code from event id
        $event_code = "";
        if ($event) {
            $event_code = $event["event_code"];
        } else {
            $data["error"] = "not found";
        }

        $event_code_dir = $event_code ? "$event_code/" : "";
        $event_code_key = $event_code ? "$event_code-" : "";

        $filetype = strtolower($filetype);
        $key = "$event_code_key$mediaName.$filetype";
        $mediaPath = "https://liveet-media.s3-us-west-2.amazonaws.com/$event_code_dir" . $key;

        $media = fopen($filePath, 'r');

        $aws_key = $_ENV["AWS_KEY"];
        $aws_secret = $_ENV["AWS_SECRET"];

        $mediaType = "";
        if (!in_array($filetype, Constants::IMAGE_TYPES_ACCEPTED) && !in_array($filetype, Constants::VIDEO_TYPES_ACCEPTED)) {
            $mediaExtError = "Unsupport media type. ";
            $mediaExtError .= $this->getSupportedMediaTypes();

            $data["error"] = [$mediaExtError];
            return $data;
        }
        if (in_array($filetype, Constants::IMAGE_TYPES_ACCEPTED)) {
            $mediaType = "image";
        }
        if (in_array($filetype, Constants::VIDEO_TYPES_ACCEPTED)) {
            $mediaType = "video";
        }

        $bucket = "liveet-media/$event_code";
        if (!$event_code) {
            $bucket = "liveet-media";
        }

        $contentType = $this->getContentType($filetype, $mediaType);

        try {
            $s3 = new S3Client([
                'region'  => 'us-west-2',
                'version' => 'latest',
                'credentials' => [
                    'key'    => $aws_key,
                    'secret' => $aws_secret,
                ]
            ]);

            $s3_result = $s3->putObject([
                'Bucket' => $bucket,
                'Key'    => $key,
                'Body'   => $media,
                'ACL'    => 'public-read',
                'ContentType'    => $contentType
            ]);
        } catch (\Exception $e) {
            $data["error"] = $e->getMessage();
            return $data;
        }

        fclose($media);
        unlink($filePath);

        $data["type"] = $mediaType;
        $data["path"] = $mediaPath;

        return $data;
    }


    public function appendSecurity($allInputs, $accountOptions = [])
    {
        $passwordKey = "password";
        $publicKeyKey = "publicKey";
        $password = null;
        $publicKey = null;

        if (isset($accountOptions["securityOptions"])) {

            $passwordKey = isset($accountOptions["securityOptions"]["passwordKey"]) ?
                $accountOptions["securityOptions"]["passwordKey"] :
                $passwordKey;

            $publicKeyKey = isset($accountOptions["securityOptions"]["publicKeyKey"]) ?
                $accountOptions["securityOptions"]["publicKeyKey"] :
                $publicKeyKey;


            if (isset($accountOptions["securityOptions"]["hasPassword"]) && $accountOptions["securityOptions"]["hasPassword"]) {

                $password = (new KeyManager())->getDigest($allInputs[$passwordKey]);

                if (!$allInputs[$passwordKey]) {
                    $allInputs["error"] =  ["errorMessage" => "Password not set", "errorStatus" => 1, "statusCode" => 400];
                }

                $allInputs[$passwordKey] = $password;
            }

            if (isset($accountOptions["securityOptions"]["hasPublicKey"]) && $accountOptions["securityOptions"]["hasPublicKey"]) {

                $publicKey = (new CodeLibrary())->genID(12, 1);
                $allInputs[$publicKeyKey] = $publicKey;
            }
        }

        return $allInputs;
    }

    public function sendMail($allInputs, $accountOptions = [])
    {
        $success = "";
        $error = "";

        if (isset($accountOptions["emailOptions"])) {
            foreach ($accountOptions["emailOptions"] as $emailOptions) {
                if (isset($allInputs[$emailOptions["emailKey"]])) {
                    $emailKey = $emailOptions["emailKey"];
                    $nameKey = $emailOptions["nameKey"];

                    $email_verification_token = (new MCrypt())->mCryptThis(time() * rand(111111111, 999999999));
                    // $email_verification_token = (new KeyManager)->createClaims(["email" => $allInputs[$emailKey], "name" => $allInputs[$nameKey]], true);

                    $allInputs["email_verification_token"] = $email_verification_token;

                    //Send and email with the email_verification_token
                    $mail = new MailHandler($emailOptions["mailtype"], $emailOptions["usertype"], $allInputs[$emailKey], ["username" => $allInputs[$nameKey], "email_verification_token" => $allInputs["email_verification_token"]]);

                    ["error" => $error, "success" => $success] = $mail->sendMail();

                    if ($error) {
                        continue;
                    }
                }
            }
        }


        return ["success" => $success, "error" => $error, "allInputs" => $allInputs];
    }

    public function modifyInputKeys($allInputs, $accountOptions)
    {
        if (isset($accountOptions["dataOptions"]) && isset($accountOptions["dataOptions"]["overrideKeys"])) {

            foreach ($accountOptions["dataOptions"]["overrideKeys"] as $passedKey => $acceptedKey) {
                $value = null;

                if (isset($allInputs[$passedKey])) {
                    $value = $allInputs[$passedKey];
                    unset($allInputs[$passedKey]);
                }

                $allInputs[$acceptedKey] = $value;
            }
        }

        return $allInputs;
    }

    public function checkOrGetPostBody($request, $response, $inputs)
    {
        $json = new JSON();
        ["data" => $data, "error" => $error] = $this->getValidJsonOrError($request);
        if ($error) {
            return $json->withJsonResponse($response, $error);
        }

        $allInputs = $this->valuesExistsOrError($data, isset($inputs["required"]) ? $inputs["required"] : $inputs);
        if ($allInputs["error"]) {
            return $json->withJsonResponse($response, $allInputs["error"]);
        }

        unset($allInputs["error"]);

        return $allInputs;
    }

    /**
     * @param Request $request
     * @param ResponseInterface $response
     * @param Model $model
     * @param Array $inputs
     * @param Arrat $accountOptions = []
     * 
     */

    public function createSelf(Request $request, ResponseInterface $response, $model, array $inputs = ["required" => [], "expected" => []], array $accountOptions = [], array $override = [], array $checks = []): ResponseInterface
    {
        $json = new JSON();

        ["data" => $data, "error" => $error] = $this->getValidJsonOrError($request);
        if ($error) {
            return $json->withJsonResponse($response, $error);
        }

        $allInputs = $this->valuesExistsOrError($data, isset($inputs["required"]) ? $inputs["required"] : $inputs);
        if ($allInputs["error"]) {
            return $json->withJsonResponse($response, $allInputs["error"]);
        }

        $allInputs = $this->appendSecurity($allInputs, $accountOptions);
        if ($allInputs["error"]) {
            return $json->withJsonResponse($response, $allInputs["error"]);
        }
        $allInputs = $this->parseImage($allInputs, $accountOptions);
        if ($allInputs["error"]) {
            return $json->withJsonResponse($response, $allInputs["error"]);
        }
        $allInputs = $this->modifyInputKeys($allInputs, $accountOptions);
        if ($allInputs["error"]) {
            return $json->withJsonResponse($response, $allInputs["error"]);
        }
        $mailResponse = $this->sendMail($allInputs, $accountOptions);
        if ($mailResponse["error"]) {
        }

        $allInputs = $mailResponse["allInputs"];
        $mailResponseSuccess =  $mailResponse["success"];

        $newAllInputs = [];
        if (isset($inputs["expected"])) {
            foreach ($inputs["expected"] as $key) {
                $newAllInputs[$key] = $allInputs[$key] ?? null;
            }
        } else {
            $newAllInputs = $allInputs;
        }

        foreach ($override as $key => $value) {
            $newAllInputs[$key] = $value;
        }

        $data = $model->createSelf($newAllInputs, $checks);
        if (isset($data["error"]) && $data["error"]) {
            $error = ["errorMessage" => $data["error"], "errorStatus" => 1, "statusCode" => 406];

            return $json->withJsonResponse($response, $error);
        }

        $payload = ["successMessage" => "Created successfully. " . $mailResponseSuccess, "statusCode" => 201, "data" => $data["data"], "errorMessage" => $mailResponse["error"]];

        return $json->withJsonResponse($response, $payload);
    }

    public function createManySelfs(Request $request, ResponseInterface $response, Model $model, array $inputs = ["required" => [], "expected" => []], array $accountOptions = [], array $override = []): ResponseInterface
    {
        $json = new JSON();

        ["data" => $data, "error" => $error] = $this->getValidJsonOrError($request);

        if ($error) {
            return $json->withJsonResponse($response, $error);
        }

        $returnData = [];

        foreach ($data as $key => $eachData) {

            $allInputs = $this->valuesExistsOrError($eachData, isset($inputs["required"]) ? $inputs["required"] : $inputs);
            if ($allInputs["error"]) {
                $returnData[$key] = $allInputs["error"];
                continue;
            }
            $allInputs = $this->appendSecurity($allInputs, $accountOptions);
            if ($allInputs["error"]) {
                $returnData[$key] = $allInputs["error"];
                continue;
            }
            $allInputs = $this->parseImage($allInputs, $accountOptions);
            if ($allInputs["error"]) {
                $returnData[$key] = $allInputs["error"];
                continue;
            }
            $allInputs = $this->modifyInputKeys($allInputs, $accountOptions);
            if ($allInputs["error"]) {
                $returnData[$key] = $allInputs["error"];
                continue;
            }
            foreach ($override as $ovrKey => $value) {
                $allInputs[$ovrKey] = $value;
            }

            $modelData = $model->createManySelfs($allInputs);
            if ($modelData["error"]) {
                $error = ["errorMessage" => $modelData["error"], "errorStatus" => 1, "statusCode" => 406];

                $returnData[$key] = $modelData["error"];

                continue;
            }

            $mailResponse = $this->sendMail($allInputs, $accountOptions);

            $returnData[$key] = $modelData["data"];
        }

        $payload = ["successMessage" => "Success", "statusCode" => 201, "data" => $returnData];

        return $json->withJsonResponse($response, $payload);
    }

    public function login(Request $request, ResponseInterface $response, Model $model, array $inputs, array $queryOptions = ["passwordKey" => "password", "publicKeyKey" => "publicKey"], array $accountOptions = []): ResponseInterface
    {
        $json = new JSON();
        $passwordKey = $queryOptions["passwordKey"];
        $publicKeyKey = $queryOptions["publicKeyKey"];

        ["data" => $data, "error" => $error] = $this->getValidJsonOrError($request);
        if ($error) {
            return $json->withJsonResponse($response, $error);
        }

        $allInputs =  $this->valuesExistsOrError($data, $inputs);
        if ($allInputs["error"]) {
            return $json->withJsonResponse($response, $allInputs["error"]);
        }

        if ($allInputs[$passwordKey] == Constants::DEFAULT_RESET_PASSWORD) {
            //TODO Redirect user to change password page
        }

        $kmg = new KeyManager();
        $password = $kmg->getDigest($allInputs[$passwordKey]);

        $cLib = new CodeLibrary();
        $publicKey = $cLib->genID(12, 1);

        $allInputs[$passwordKey] = $password;
        $allInputs[$publicKeyKey] = $publicKey;


        $allInputs = $this->modifyInputKeys($allInputs, $accountOptions);
        if ($allInputs["error"]) {
            return $json->withJsonResponse($response, $allInputs["error"]);
        }

        $data = $model->login($allInputs);
        if (isset($data["error"]) && $data["error"]) {
            $payload = array("errorMessage" => $data["error"], "errorStatus" => 1, "statusCode" => 401, "data" => null);

            return $json->withJsonResponse($response, $payload);
        }

        $token = (new KeyManager)->createClaims(json_decode($data["data"], true));

        if (isset($data["users"])) {
            $data["data"]["users"] = $data["users"];
        }

        unset($data["data"][$publicKeyKey]);

        $payload = array("successMessage" => "Login successful", "statusCode" => 200, "data" => $data["data"], "token" => $token);

        return $json->withJsonResponse($response, $payload)->withHeader("token", "bearer " . $token);
    }

    public function getSelfDashboard(Request $request, ResponseInterface $response, $model, $queryOptions = [], $extras = ["hasKey" => true]): ResponseInterface
    {
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);


        $pk = isset($extras["hasKey"]) && $extras["hasKey"] ? $authDetails[$model->primaryKey] : null;

        $data = $model->getDashboard($pk, $queryOptions, $extras);
        if (isset($data["error"]) && $data["error"]) {
            $payload = array("errorMessage" => $data["error"], "errorStatus" => 1, "statusCode" => 400);

            return $json->withJsonResponse($response, $payload);
        }

        $payload = array("successMessage" => "Dashboard request success", "statusCode" => 200, "data" => $data["data"]);

        return $json->withJsonResponse($response, $payload);
    }

    public function getDashboardByPK(Request $request, ResponseInterface $response, $model, $queryOptions = [], $extras = []): ResponseInterface
    {
        $json = new JSON();


        [$model->primaryKey => $pk, "error" => $error] = $this->getRouteParams($request, [$model->primaryKey]);
        if ($error) {
            return $json->withJsonResponse($response, $error);
        }

        $data = $model->getDashboard($pk, $queryOptions, $extras);

        if (isset($data["error"]) && $data["error"]) {
            $payload = array("errorMessage" => $data["error"], "errorStatus" => 1, "statusCode" => 400);

            return $json->withJsonResponse($response, $payload);
        }

        $payload = array("successMessage" => "Dashboard request success", "statusCode" => 200, "data" => $data["data"]);

        return $json->withJsonResponse($response, $payload);
    }

    public function getByPage(Request $request, ResponseInterface $response, $model, $return = null, $conditions = null, $relationships = null, $queryOptions = []): ResponseInterface
    {
        $json = new JSON();

        ["page" => $page, "error" => $error] = $this->getPageNumOrError($request);
        ["limit" => $limit, "error" => $error] = $this->getPageLimit($request);
        if ($error) {
            return $json->withJsonResponse($response, $error);
        }

        $data = $model->getByPage($page, $limit, $return, $conditions, $relationships, $queryOptions);
        if (isset($data["error"]) && $data["error"]) {
            $payload = array("errorMessage" => $data["error"], "errorStatus" => "1", "statusCode" => 400);

            return $json->withJsonResponse($response, $payload);
        }

        $payload = array("successMessage" => "Request success", "statusCode" => 200, "data" => $data["data"]);

        return $json->withJsonResponse($response, $payload);
    }

    public function getByDate(Request $request, ResponseInterface $response, $model, $return = null, $conditions = null, $relationships = null, $queryOptions = ["dateCreatedColumn" => "dateCreated", "distinct" => false]): ResponseInterface
    {
        $json = new JSON();

        $routeParams = $this->getRouteParams($request, ["from", "to"]);
        $from = null;
        $to = null;

        // if ($routeParams["error"]) {
        $from = (isset($routeParams["from"]) && $routeParams["from"]) ? $routeParams["from"] : "-";

        $to = (isset($routeParams["to"]) && $routeParams["to"]) ? $routeParams["to"] : "-";
        // }

        if ($from == "-") {
            $from = date("U") - 86400;
        }

        if ($to == "-") {
            $to = date("U") + 86400;
        }

        $data = $model->getByDate($from, $to, $return, $conditions, $relationships, $queryOptions);
        if (isset($data["error"]) && $data["error"]) {
            $payload = array("errorMessage" => $data["error"], "errorStatus" => "1", "statusCode" => 400);

            return $json->withJsonResponse($response, $payload);
        }

        $payload = array("successMessage" => "Request success", "statusCode" => 200, "data" => $data["data"]);

        return $json->withJsonResponse($response, $payload);
    }

    public function getSelf(Request $request, ResponseInterface $response, $model,  $return = null, $relationships = null,  $queryOptions = []): ResponseInterface
    {
        $json = new JSON();


        $authDetails = static::getTokenInputsFromRequest($request);

        [$model->primaryKey => $pk] = $authDetails;

        $data = $model->getByPK($pk, $return, $relationships, $queryOptions);

        if (isset($data["error"]) && $data["error"]) {
            $payload = array("errorMessage" => $data["error"], "errorStatus" => 1, "statusCode" => 400);

            return $json->withJsonResponse($response, $payload);
        }

        $payload = array("successMessage" => "Request success", "statusCode" => 200, "data" => $data["data"]);

        return $json->withJsonResponse($response, $payload);
    }

    public function getByPK(Request $request, ResponseInterface $response, $model, $return = null, $relationships = null,  $queryOptions = []): ResponseInterface
    {
        $json = new JSON();

        [$model->primaryKey => $pk, "error" => $error] = $this->getRouteParams($request, [$model->primaryKey]);
        if ($error) {
            return $json->withJsonResponse($response, $error);
        }

        $data = $model->getByPK($pk, $return, $relationships, $queryOptions);
        if (isset($data["error"]) && $data["error"]) {
            $payload = array("errorMessage" => $data["error"], "errorStatus" => 1, "statusCode" => 400);

            return $json->withJsonResponse($response, $payload);
        }

        $payload = array("successMessage" => "Requst success", "statusCode" => 200, "data" => $data["data"]);

        return $json->withJsonResponse($response, $payload);
    }

    public function getByConditions(Request $request, ResponseInterface $response, $model, $conditions, $return = null, $relationships = null): ResponseInterface
    {
        $json = new JSON();

        $data = $model->getByConditions($conditions, $return, $relationships);

        if (isset($data["error"]) && $data["error"]) {
            $payload = array("errorMessage" => $data["error"], "errorStatus" => 1, "statusCode" => 400);

            return $json->withJsonResponse($response, $payload);
        }

        $payload = array("successMessage" => "Requst success", "statusCode" => 200, "data" => $data["data"]);

        return $json->withJsonResponse($response, $payload);
    }

    public function updateSelf(Request $request, ResponseInterface $response, $model, array $inputs = ["required" => [], "expected" => []], array $accountOptions = [], $override = [], $checks = []): ResponseInterface
    {
        $json = new JSON();

        ["data" => $data, "error" => $error] = $this->getValidJsonOrError($request);
        if ($error) {
            return $json->withJsonResponse($response, $error);
        }

        $authDetails = static::getTokenInputsFromRequest($request);
        $pk = $authDetails[$model->primaryKey];

        $allInputs = $this->valuesExistsOrError($data, isset($inputs["required"]) ? $inputs["required"] : $inputs);
        if ($allInputs["error"]) {
            return $json->withJsonResponse($response, $allInputs["error"]);
        }

        $allInputs = $this->parseImage($allInputs, $accountOptions);
        if ($allInputs["error"]) {
            return $json->withJsonResponse($response, $allInputs["error"]);
        }
        $allInputs = $this->modifyInputKeys($allInputs, $accountOptions);
        if ($allInputs["error"]) {
            return $json->withJsonResponse($response, $allInputs["error"]);
        }

        $newAllInputs = [];
        if (isset($inputs["expected"])) {
            foreach ($inputs["expected"] as $key) {
                $newAllInputs[$key] = $allInputs[$key] ?? null;
            }
        } else {
            $newAllInputs = $allInputs;
        }

        foreach ($override as $key => $value) {
            $newAllInputs[$key] = $value;
        }

        $newAllInputs[$model->primaryKey] = $authDetails[$model->primaryKey];

        $data = $model->updateByPK($pk, $newAllInputs, $checks);
        if (isset($data["error"]) && $data["error"]) {
            $error = ["errorMessage" => $data["error"], "errorStatus" => 1, "statusCode" => 406];

            return $json->withJsonResponse($response, $error);
        }

        $payload = ["successMessage" => "Update success", "statusCode" => 201, "data" => $data["data"]];

        return $json->withJsonResponse($response, $payload);
    }

    public function updateByPK(Request $request, ResponseInterface $response, $model, array $inputs = ["required" => [], "expected" => []], $accountOptions = [], $override = [], $checks = []): ResponseInterface
    {
        $json = new JSON();

        ["data" => $data, "error" => $error] = $this->getValidJsonOrError($request);
        if ($error) {
            return $json->withJsonResponse($response, $error);
        }

        $authDetails = static::getTokenInputsFromRequest($request);
        $routeParams = $this->getRouteParams($request, [$model->primaryKey]);
        $pk = $routeParams[$model->primaryKey];

        $allInputs = $this->valuesExistsOrError($data, isset($inputs["required"]) ? $inputs["required"] : $inputs);
        if ($allInputs["error"]) {
            return $json->withJsonResponse($response, $allInputs["error"]);
        }
        $allInputs = $this->parseImage($allInputs, $accountOptions);
        if ($allInputs["error"]) {
            return $json->withJsonResponse($response, $allInputs["error"]);
        }
        $allInputs = $this->modifyInputKeys($allInputs, $accountOptions);
        if ($allInputs["error"]) {
            return $json->withJsonResponse($response, $allInputs["error"]);
        }

        $allInputs[$model->primaryKey] = $pk;

        $newAllInputs = [];
        if (isset($inputs["expected"])) {
            foreach ($inputs["expected"] as $key) {
                $newAllInputs[$key] = $allInputs[$key] ?? null;
            }
        } else {
            $newAllInputs = $allInputs;
        }

        foreach ($override as $key => $value) {
            $newAllInputs[$key] = $value;
        }

        $data = $model->updateByPK($pk, $newAllInputs, $checks);
        if (isset($data["error"]) && $data["error"]) {
            $error = ["errorMessage" => $data["error"], "errorStatus" => 1, "statusCode" => 406];

            return $json->withJsonResponse($response, $error);
        }

        $payload = ["successMessage" => "Update success", "statusCode" => 201, "data" => $data["data"]];

        return $json->withJsonResponse($response, $payload);
    }

    public function updateManySelfsByPK(Request $request, ResponseInterface $response, $model, array $inputs, $accountOptions = []): ResponseInterface
    {
        $json = new JSON();

        ["data" => $data, "error" => $error] = $this->getValidJsonOrError($request);

        if ($error) {
            return $json->withJsonResponse($response, $error);
        }

        $authDetails = static::getTokenInputsFromRequest($request);

        $returnData = [];
        foreach ($data as $key => $eachData) {

            $allInputs = $this->valuesExistsOrError($eachData, $inputs);

            if ($allInputs["error"]) {
                $returnData[$key] = $allInputs["error"];

                continue;
            }

            $allInputs = $this->parseImage($allInputs, $accountOptions);
            if ($allInputs["error"]) {
                $returnData[$key] = $allInputs["error"];

                continue;
            }
            $allInputs = $this->modifyInputKeys($allInputs, $accountOptions);
            if ($allInputs["error"]) {
                $returnData[$key] = $allInputs["error"];

                continue;
            }

            $modelData = $model->updateByPK($allInputs);
            if ($modelData["error"]) {
                $error = ["errorMessage" => $modelData["error"], "errorStatus" => 1, "statusCode" => 406];

                $returnData[$key] = $modelData["error"];
                continue;
            }

            $returnData[$key] = $modelData["data"];
        }

        $payload = ["successMessage" => "Update success", "statusCode" => 201, "data" => $returnData];

        return $json->withJsonResponse($response, $payload);
    }

    public function updateByColumnNames(Request $request, ResponseInterface $response, $model, array $inputs = ["required" => [], "expected" => []], $columnsNames, $checks = [], $override = [], $accountOptions = [], $queryOptions = []): ResponseInterface
    {
        $json = new JSON();

        ["data" => $data, "error" => $error] = $this->getValidJsonOrError($request);
        if ($error) {
            return $json->withJsonResponse($response, $error);
        }

        $authDetails = static::getTokenInputsFromRequest($request);

        $allInputs = $this->valuesExistsOrError($data, isset($inputs["required"]) ? $inputs["required"] : $inputs);
        if ($allInputs["error"]) {
            return $json->withJsonResponse($response, $allInputs["error"]);
        }

        $allInputs = $this->parseImage($allInputs, $accountOptions);
        if ($allInputs["error"]) {
            return $json->withJsonResponse($response, $allInputs["error"]);
        }
        $allInputs = $this->modifyInputKeys($allInputs, $accountOptions);
        if ($allInputs["error"]) {
            return $json->withJsonResponse($response, $allInputs["error"]);
        }

        $newAllInputs = [];
        if (isset($inputs["expected"])) {
            foreach ($inputs["expected"] as $key) {
                $newAllInputs[$key] = $allInputs[$key] ?? null;
            }
        } else {
            $newAllInputs = $allInputs;
        }

        foreach ($override as $key => $value) {
            $newAllInputs[$key] = $value;
        }

        $data = $model->updateByColumnNames($columnsNames, $newAllInputs, $checks, $queryOptions);
        if (isset($data["error"]) && $data["error"]) {
            $error = ["errorMessage" => $data["error"], "errorStatus" => 1, "statusCode" => 406];

            return $json->withJsonResponse($response, $error);
        }

        $payload = ["successMessage" => "Update success", "statusCode" => 201, "data" => $data["data"]];

        return $json->withJsonResponse($response, $payload);
    }

    public function updateManySelfsByColumnNames(Request $request, ResponseInterface $response, $model, array $inputs, $columnNames, $checks = [], $accountOptions = []): ResponseInterface
    {
        $json = new JSON();

        ["data" => $data, "error" => $error] = $this->getValidJsonOrError($request);

        if ($error) {
            return $json->withJsonResponse($response, $error);
        }

        $authDetails = static::getTokenInputsFromRequest($request);

        $returnData = [];

        foreach ($data as $key => $eachData) {

            $allInputs = $this->valuesExistsOrError($eachData, $inputs);
            if ($allInputs["error"]) {
                $returnData[$key] = $allInputs["error"];
                continue;
            }

            $allInputs = $this->parseImage($allInputs, $accountOptions);
            if ($allInputs["error"]) {
                $returnData[$key] = $allInputs["error"];

                continue;
            }
            $allInputs = $this->modifyInputKeys($allInputs, $accountOptions);
            if ($allInputs["error"]) {
                $returnData[$key] = $allInputs["error"];

                continue;
            }

            $newAllInputs = [];
            foreach ($inputs as $key) {
                $newAllInputs[$key] = $allInputs[$key];
            }

            $modelData = $model->updateByColumnNames($columnNames, $newAllInputs, $checks);
            if ($modelData["error"]) {
                $error = ["errorMessage" => $modelData["error"], "errorStatus" => 1, "statusCode" => 406];

                $returnData[$key] = $modelData["error"];

                continue;
            }

            $returnData[$key] = $modelData["data"];
        }

        $payload = ["successMessage" => "Update success", "statusCode" => 201, "data" => $returnData];

        return $json->withJsonResponse($response, $payload);
    }

    public function updateByConditions(
        Request $request,
        ResponseInterface $response,
        $model,
        array $inputs = ["required" => [], "expected" => []],
        $conditions,
        $checks = [],
        $override = [],
        $accountOptions = [],
        $queryOptions = []
    ): ResponseInterface {
        $json = new JSON();

        ["data" => $data, "error" => $error] = $this->getValidJsonOrError($request);
        if ($error) {
            return $json->withJsonResponse($response, $error);
        }

        $authDetails = static::getTokenInputsFromRequest($request);

        $allInputs = $this->valuesExistsOrError($data, isset($inputs["required"]) ? $inputs["required"] : $inputs);
        if ($allInputs["error"]) {
            return $json->withJsonResponse($response, $allInputs["error"]);
        }

        $allInputs = $this->parseImage($allInputs, $accountOptions);
        if ($allInputs["error"]) {
            return $json->withJsonResponse($response, $allInputs["error"]);
        }
        $allInputs = $this->modifyInputKeys($allInputs, $accountOptions);
        if ($allInputs["error"]) {
            return $json->withJsonResponse($response, $allInputs["error"]);
        }

        $newAllInputs = [];
        if (isset($inputs["expected"])) {
            foreach ($inputs["expected"] as $key) {
                $newAllInputs[$key] = $allInputs[$key] ?? null;
            }
        } else {
            $newAllInputs = $allInputs;
        }

        foreach ($override as $key => $value) {
            $newAllInputs[$key] = $value;
        }

        $data = $model->updateByConditions($conditions, $newAllInputs, $checks, $queryOptions);
        if (isset($data["error"]) && $data["error"]) {
            $error = ["errorMessage" => $data["error"], "errorStatus" => 1, "statusCode" => 406];

            return $json->withJsonResponse($response, $error);
        }

        $payload = ["successMessage" => "Update success", "statusCode" => 201, "data" => $data["data"]];

        return $json->withJsonResponse($response, $payload);
    }

    public function updateManySelfsByConditions(Request $request, ResponseInterface $response, $model, array $inputs, $conditions, $checks = [], $accountOptions = []): ResponseInterface
    {
        $json = new JSON();

        ["data" => $data, "error" => $error] = $this->getValidJsonOrError($request);

        if ($error) {
            return $json->withJsonResponse($response, $error);
        }

        $authDetails = static::getTokenInputsFromRequest($request);

        $returnData = [];

        foreach ($data as $key => $eachData) {

            $allInputs = $this->valuesExistsOrError($eachData, $inputs);
            if ($allInputs["error"]) {
                $returnData[$key] = $allInputs["error"];
                continue;
            }

            $allInputs = $this->parseImage($allInputs, $accountOptions);
            if ($allInputs["error"]) {
                $returnData[$key] = $allInputs["error"];

                continue;
            }
            $allInputs = $this->modifyInputKeys($allInputs, $accountOptions);
            if ($allInputs["error"]) {
                $returnData[$key] = $allInputs["error"];

                continue;
            }

            $newAllInputs = [];
            foreach ($inputs as $key) {
                $newAllInputs[$key] = $allInputs[$key];
            }

            $modelData = $model->updateByConditions($conditions, $newAllInputs, $checks);

            if ($modelData["error"]) {
                $error = ["errorMessage" => $modelData["error"], "errorStatus" => 1, "statusCode" => 406];

                $returnData[$key] = $modelData["error"];
                continue;
            }

            $returnData[$key] = $modelData["data"];
        }

        $payload = ["successMessage" => "Update success", "statusCode" => 201, "data" => $returnData];

        return $json->withJsonResponse($response, $payload);
    }

    public function updatePassword(Request $request, ResponseInterface $response, $model, $queryOptions = []): ResponseInterface
    {
        $json = new JSON();

        ["data" => $data, "error" => $error] = $this->getValidJsonOrError($request);
        if ($error) {
            return $json->withJsonResponse($response, $error);
        }

        $authDetails = static::getTokenInputsFromRequest($request);

        $allInputs = $this->valuesExistsOrError($data, ["new_password", "old_password"]);
        if ($allInputs["error"]) {
            return $json->withJsonResponse($response, $allInputs["error"]);
        }

        ["new_password" => $new_password, "old_password" => $old_password, "error" => $error] = $allInputs;

        $kmg = new KeyManager();

        $new_password = $kmg->getDigest($new_password);
        $old_password = $kmg->getDigest($old_password);

        $pk = $authDetails[$model->primaryKey];

        $data = $model->updatePassword($pk, $new_password, $old_password);
        if (isset($data["error"]) && $data["error"]) {
            $this->logoutSelf($request, $response, $model);

            $error = ["errorMessage" => $data["error"], "errorStatus" => 1, "statusCode" => 400];

            return $json->withJsonResponse($response,  $error);
        }

        $payload = ["successMessage" => "Password update success", "statusCode" => 201, "data" => $data["data"]];

        return $json->withJsonResponse($response,  $payload);
    }

    public function resetPassword(Request $request, ResponseInterface $response, $model, $queryOptions = []): ResponseInterface
    {
        $json = new JSON();


        $authDetails = static::getTokenInputsFromRequest($request);

        ["data" => $data, "error" => $error] = $this->getValidJsonOrError($request);

        if ($error) {
            return $json->withJsonResponse($response, $error);
        }

        $allInputs = $this->valuesExistsOrError($data, [$model->primaryKey]);

        if ($allInputs["error"]) {
            return $json->withJsonResponse($response, $allInputs["error"]);
        }

        [$model->primaryKey => $pk, "error" => $error] = $allInputs;
        $new_password = Constants::DEFAULT_RESET_PASSWORD;

        $kmg = new KeyManager();
        $encryptedPassword = $kmg->getDigest($new_password);

        $password = $encryptedPassword;

        $data = $model->resetPassword($pk, $password);

        if (isset($data["error"]) && $data["error"]) {
            $error = ["errorMessage" => $data["error"], "errorStatus" => 1, "statusCode" => 400];

            return $json->withJsonResponse($response,  $error);
        }

        $payload = ["successMessage" => "Password reset to default: " . Constants::DEFAULT_RESET_PASSWORD, "statusCode" => 201, "data" => $data["data"]];

        return $json->withJsonResponse($response,  $payload);
    }

    public function verifyEmail(Request $request, ResponseInterface $response, $model): ResponseInterface
    {
        $json = new JSON();

        ["data" => $email_verification_token, "error" => $error] = $this->getRouteTokenOrError($request);

        if ($error) {
            return $json->withJsonResponse($response, $error);
        }

        $status = Constants::EMAIL_VERIFIED;

        $data = $model->verifyEmail($email_verification_token, $status);

        if (isset($data["error"]) && $data["error"]) {
            $error = ["errorMessage" => $data["error"], "errorStatus" => 1, "statusCode" => 406, "data" => null];

            return $json->withJsonResponse($response, $error);
        }

        $payload = ["successMessage" => "Email verification success", "statusCode" => 200, "data" => $data["data"]];

        //TODO redirect to login
        return $json->withJsonResponse($response, $payload);
    }

    public function forgotPassword(Request $request, ResponseInterface $response, $model, $inputs = [], $override = []): ResponseInterface
    {
        $json = new JSON();
        $authDetails = static::getTokenInputsFromRequest($request);

        ["data" => $data, "error" => $error] = $this->getValidJsonOrError($request);
        if ($error) {
            return $json->withJsonResponse($response, $error);
        }

        $allInputs = $this->valuesExistsOrError($data, $inputs);
        if ($allInputs["error"]) {
            return $json->withJsonResponse($response, $allInputs["error"]);
        }

        foreach ($override as $key => $value) {
            $allInputs[$key] = $value;
        }

        $mcrypt = new MCrypt();
        $email_verification_token = $mcrypt->mCryptThis(time() * rand(111111111, 999999999));
        $allInputs["name"] = $allInputs["email"];
        $allInputs["email_verification_token"] = $email_verification_token;
        $usertype = $allInputs["usertype"];
        $mailtype = MailHandler::TEMPLATE_FORGOT_PASSWORD;

        $this->sendMail($allInputs, [["emailKey" => "email", "nameKey" => "name", "usertype" => $usertype, "mailtype" => $mailtype]]);

        $data = $model->forgotPassword($allInputs);
        if (isset($data["error"]) && $data["error"]) {
            $error = ["errorMessage" => $data["error"], "errorStatus" => 1, "statusCode" => 406, "data" => null];

            return $json->withJsonResponse($response, $error);
        }

        $payload = ["successMessage" => "Password reset link sent to your email", "statusCode" => 200, "data" => $data["data"]];

        return $json->withJsonResponse($response, $payload);
    }

    public function verifyForgotPassword(Request $request, ResponseInterface $response, $model): ResponseInterface
    {
        $json = new JSON();

        ["data" => $forgotPasswordVerificationToken, "error" => $error] = $this->getRouteTokenOrError($request);
        if ($error) {
            return $json->withJsonResponse($response, $error);
        }
        $allInputs = ["forgotPasswordVerificationToken" => $forgotPasswordVerificationToken];

        $data = $model->verifyForgotPassword($allInputs);
        if (isset($data["error"]) && $data["error"]) {
            $error = ["errorMessage" => $data["error"], "errorStatus" => 1, "statusCode" => 406, "data" => null];

            return $json->withJsonResponse($response, $error);
        }

        $token = (new KeyManager)->createClaims(json_decode($data["data"], true));

        if (isset($data["users"])) {
            $data["data"]["users"] = $data["users"];
        }

        unset($data["data"]["publicKey"]);

        $payload = array("successMessage" => "Login successful", "statusCode" => 200, "data" => $data["data"], "token" => $token);

        return $json->withJsonResponse($response, $payload)->withHeader("token", "bearer " . $token);
    }

    public function updateForgotPassword(Request $request, ResponseInterface $response, $model, $queryOptions = ["passwordKey" => "password", "publicKeyKey" => "publicKey"]): ResponseInterface
    {
        $json = new JSON();

        $passwordKey = $queryOptions["passwordKey"];
        $publicKeyKey = $queryOptions["publicKeyKey"];

        ["data" => $data, "error" => $error] = $this->getValidJsonOrError($request);
        if ($error) {
            return $json->withJsonResponse($response, $error);
        }

        $authDetails = static::getTokenInputsFromRequest($request);

        $allInputs = $this->valuesExistsOrError($data, [$passwordKey]);
        if ($allInputs["error"]) {
            return $json->withJsonResponse($response, $allInputs["error"]);
        }

        [$passwordKey => $password,] = $allInputs;

        $kmg = new KeyManager();

        $password = $kmg->getDigest($password);

        $publicKey =  $authDetails[$publicKeyKey];
        $pk = $authDetails[$model->primaryKey];

        $data = $model->updateForgotPassword($pk, $password);

        if (isset($data["error"]) && $data["error"]) {
            $this->logoutSelf($request, $response, $model);

            $error = ["errorMessage" => $data["error"], "errorStatus" => 1, "statusCode" => 400];

            return $json->withJsonResponse($response,  $error);
        }

        $payload = ["successMessage" => "Password change success", "statusCode" => 201, "data" => $data["data"]];

        return $json->withJsonResponse($response,  $payload);
    }

    public function verifyUser(Request $request, ResponseInterface $response, $model, $queryOptions = []): ResponseInterface
    {
        $json = new JSON();
        $authDetails = static::getTokenInputsFromRequest($request);

        ["data" => $data, "error" => $error] = $this->getValidJsonOrError($request);
        if ($error) {
            return $json->withJsonResponse($response, $error);
        }

        $allInputs = $this->valuesExistsOrError($data, [$model->primaryKey]);
        if ($allInputs["error"]) {
            return $json->withJsonResponse($response, $allInputs["error"]);
        }

        [$model->primaryKey => $pk] = $allInputs;

        $status = Constants::USER_VERIFIED;

        $data = $model->verifyUser($pk, $status);

        if (isset($data["error"]) && $data["error"]) {
            $error = ["errorMessage" => $data["error"], "errorStatus" => 1, "statusCode" => 406, "data" => null];

            return $json->withJsonResponse($response, $error);
        }

        $payload = ["successMessage" => "User verification success", "statusCode" => 200, "data" => $data["data"]];

        return $json->withJsonResponse($response, $payload);
    }

    public function deleteSelf(Request $request, ResponseInterface $response, $model, $queryOptions = []): ResponseInterface
    {
        $json = new JSON();


        $authDetails = static::getTokenInputsFromRequest($request);

        [$model->primaryKey => $pk] = $authDetails;

        $data = $model->deleteByPK($pk);

        if (isset($data["error"]) && $data["error"]) {
            $error = ["errorMessage" => $data["error"], "errorStatus" => 1, "statusCode" => 400];

            return $json->withJsonResponse($response,  $error);
        }

        $payload = array("successMessage" => "Delete success", "statusCode" => 200, "data" => $data["data"]);

        return $json->withJsonResponse($response, $payload);
    }

    public function deleteByPK(Request $request, ResponseInterface $response, $model, $queryOptions = []): ResponseInterface
    {
        $json = new JSON();

        $allInputs = $this->getRouteParams($request, [$model->primaryKey]);
        if ($allInputs["error"]) {
            return $json->withJsonResponse($response, $allInputs["error"]);
        }

        [$model->primaryKey => $pk, "error" => $error] = $allInputs;
        if ($error) {
            return $json->withJsonResponse($response, $error);
        }

        $data = $model->deleteByPK($pk);
        if (isset($data["error"]) && $data["error"]) {
            $error = ["errorMessage" => $data["error"], "errorStatus" => 1, "statusCode" => 400];

            return $json->withJsonResponse($response,  $error);
        }

        $payload = array("successMessage" => "Delete success", "statusCode" => 200, "data" => $data["data"]);

        return $json->withJsonResponse($response, $payload);
    }

    public function deleteManyByPK(Request $request, ResponseInterface $response, $model, $queryOptions = []): ResponseInterface
    {
        $json = new JSON();

        ["data" => $data, "error" => $error] = $this->getValidJsonOrError($request);
        if ($error) {
            return $json->withJsonResponse($response, $error);
        }

        $authDetails = static::getTokenInputsFromRequest($request);

        $allInputs = $this->valuesExistsOrError($data, [$model->primaryKey]);
        if ($allInputs["error"]) {
            return $json->withJsonResponse($response, $allInputs["error"]);
        }

        [$model->primaryKey => $pks] = $allInputs;

        $data = $model->deleteManyByPK($pks);
        if (isset($data["error"]) && $data["error"]) {
            $error = ["errorMessage" => $data["error"], "errorStatus" => 1, "statusCode" => 400];

            return $json->withJsonResponse($response,  $error);
        }

        $payload = array("successMessage" => "Delete success", "statusCode" => 200, "data" => $data["data"]);

        return $json->withJsonResponse($response, $payload);
    }

    public function logoutSelf(Request $request, ResponseInterface $response, $model): ResponseInterface
    {
        $json = new JSON();

        $authDetails = static::getTokenInputsFromRequest($request);
        $pk = isset($authDetails[$model->primaryKey]) ? $authDetails[$model->primaryKey] : null;
        if (!$pk) {
            $error = ["errorMessage" => "Invalid request", "errorStatus" => 1, "statusCode" => 400];

            return $json->withJsonResponse($response,  $error);
        }

        $data = $model->logout($pk);
        if (isset($data["error"]) && $data["error"]) {
            $error = ["errorMessage" => $data["error"], "errorStatus" => 1, "statusCode" => 400];

            return $json->withJsonResponse($response,  $error);
        }

        $payload = array("successMessage" => "Logout success", "statusCode" => 200, "data" => $data["data"]);

        return $json->withJsonResponse($response, $payload);
    }

    public function logoutByPK(Request $request, ResponseInterface $response, $model, $queryOptions = []): ResponseInterface
    {
        $json = new JSON();

        $routeParams = $this->getRouteParams($request, [$model->primaryKey]);
        if ($routeParams["error"]) {
            return $json->withJsonResponse($response, $routeParams["error"]);
        }

        [$model->primaryKey => $pk] = $routeParams;

        $data = $model->logout($pk);
        if (isset($data["error"]) && $data["error"]) {
            $error = ["errorMessage" => $data["error"], "errorStatus" => 1, "statusCode" => 400];

            return $json->withJsonResponse($response,  $error);
        }

        $payload = array("successMessage" => "Logout success", "statusCode" => 200, "data" => $data["data"]);

        return $json->withJsonResponse($response, $payload);
    }

    public function logoutByCondition(Request $request, ResponseInterface $response, $model, $conditions = []): ResponseInterface
    {
        $json = new JSON();

        $data = $model->logoutByCondition($conditions);
        if (isset($data["error"]) && $data["error"]) {
            $error = ["errorMessage" => $data["error"], "errorStatus" => 1, "statusCode" => 400];

            return $json->withJsonResponse($response,  $error);
        }

        $payload = array("successMessage" => "Logout success", "statusCode" => 200, "data" => $data["data"]);

        return $json->withJsonResponse($response, $payload);
    }

    /** Deprecation */

    public function updateSelfColumns(Request $request, ResponseInterface $response, $model, array $columnNames, $queryOptions = []): ResponseInterface
    {
        $json = new JSON();


        ["data" => $data, "error" => $error] = $this->getValidJsonOrError($request);
        if ($error) {
            return $json->withJsonResponse($response, $error);
        }

        $authDetails = static::getTokenInputsFromRequest($request);

        $allInputs = $this->valuesExistsOrError($data, $columnNames);
        if ($allInputs["error"]) {
            return $json->withJsonResponse($response, $allInputs["error"]);
        }

        unset($allInputs["error"]);

        $pk = $allInputs[$model->primaryKey];

        unset($allInputs[$model->primaryKey]);

        $data = $model->updateColumns($pk, $allInputs);

        if (isset($data["error"]) && $data["error"]) {
            $error = ["errorMessage" => $data["error"], "errorStatus" => 1, "statusCode" => 400];

            return $json->withJsonResponse($response,  $error);
        }

        $payload = ["successMessage" => "Update success", "statusCode" => 201, "data" => $data["data"]];

        return $json->withJsonResponse($response,  $payload);
    }

    public function getByDateWithRelationship(Request $request, ResponseInterface $response, $model, $relationships, $return = null): ResponseInterface
    {
        $json = new JSON();

        ["from" => $from, "to" => $to, "error" => $error] = $this->getRouteParams($request, ["from", "to"]);

        if ($error) {
            return $json->withJsonResponse($response, $error);
        }

        $data = $model->getByDateWithRelationship($from, $to, $relationships, $return);

        if (isset($data["error"]) && $data["error"]) {
            $payload = array("errorMessage" => $data["error"], "errorStatus" => "1", "statusCode" => 400);

            return $json->withJsonResponse($response, $payload);
        }

        $payload = array("successMessage" => "Request success", "statusCode" => 200, "data" => $data["data"]);

        return $json->withJsonResponse($response, $payload);
    }

    public function getByDateWithConditions(Request $request, ResponseInterface $response, $model, $conditions, $return = null, $override = [], $queryOptions = ["distinct" => null, "max" => null]): ResponseInterface
    {
        $json = new JSON();

        ["from" => $from, "to" => $to, "error" => $error] = $this->getRouteParams($request, ["from", "to"]);
        if ($error) {
            return $json->withJsonResponse($response, $error);
        }

        $from = isset($override["from"]) ? $override["from"] : $from;
        $to = isset($override["to"]) ? $override["to"] : $to;

        $data = $model->getByDateWithConditions($from, $to, $conditions, $return, $queryOptions);

        if (isset($data["error"]) && $data["error"]) {
            $payload = array("errorMessage" => $data["error"], "errorStatus" => "1", "statusCode" => 400);

            return $json->withJsonResponse($response, $payload);
        }

        $payload = array("successMessage" => "Request success", "statusCode" => 200, "data" => $data["data"]);

        return $json->withJsonResponse($response, $payload);
    }

    public function getByPKWithRelationships(Request $request, ResponseInterface $response, $model, $relationships, $return = null, $queryOptions = []): ResponseInterface
    {
        $json = new JSON();


        [$model->primaryKey => $pk, "error" => $error] = $this->getRouteParams($request, [$model->primaryKey]);
        if ($error) {
            return $json->withJsonResponse($response, $error);
        }

        $data = $model->getWithRelationships($pk, $relationships, $return);
        if (isset($data["error"]) && $data["error"]) {
            $payload = array("errorMessage" => $data["error"], "errorStatus" => 1, "statusCode" => 400);

            return $json->withJsonResponse($response, $payload);
        }

        $payload = array("successMessage" => "Requst success", "statusCode" => 200, "data" => $data["data"]);

        return $json->withJsonResponse($response, $payload);
    }

    public function updateManySelfsSelf(Request $request, ResponseInterface $response, $model, array $inputs): ResponseInterface
    {
        $json = new JSON();

        ["data" => $data, "error" => $error] = $this->getValidJsonOrError($request);
        if ($error) {
            return $json->withJsonResponse($response, $error);
        }

        $authDetails = static::getTokenInputsFromRequest($request);

        $returnData = [];
        foreach ($data as $key => $eachData) {

            $eachData = $this->parseImage($eachData);

            $allInputs = $this->valuesExistsOrError($eachData, $inputs);
            if ($allInputs["error"]) {
                $returnData[$key] = $allInputs["error"];
                continue;
            }

            $allInputs[$model->primaryKey] = $authDetails[$model->primaryKey];

            $modelData = $model->updateSelf($allInputs);
            if ($modelData["error"]) {
                $returnData[$key] = $modelData["error"];
                continue;
            }

            $returnData[$key] = $modelData["data"];
        }

        $payload = ["successMessage" => "Update success", "statusCode" => 201, "data" => $returnData];

        return $json->withJsonResponse($response, $payload);
    }
}
