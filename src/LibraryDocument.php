<?php namespace Echosign;

use Echosign\Interfaces\RequestEntityInterface;
use Echosign\Interfaces\TransportInterface;
use Echosign\Responses\DocumentLibraryItems;
use Echosign\Responses\Documents;
use Echosign\Responses\LibraryDocumentInfo;
use Echosign\Responses\Error;

class LibraryDocument implements RequestEntityInterface {

    const END_POINT = '/libraryDocuments';

    protected $headers = [];
    protected $data = [];
    protected $endPoint = '';

    /**
     * @var Token
     */
    protected $token;

    /**
     * @var TransportInterface
     */
    protected $transport;

    /**
     * @param Token $token
     */
    public function __construct( Token $token )
    {
        $this->token = $token;
    }

    /**
     * @param null $userId
     * @param null $userEmail
     * @return DocumentLibraryItems
     */
    public function getAll($userId=null, $userEmail=null)
    {
        $this->headers = array_filter([
            'X-User-Id'    => $userId,
            'X-User-Email' => $userEmail,
            'Access-Token' => $this->getAccessToken()
        ]);

        $this->data = [];

        $request  = $this->getTransport();
        $response = $request->get($this);

        if( $response instanceof Error ) {
            return $response;
        }

        return new DocumentLibraryItems( $response, $this );
    }

    /**
     * @param $libraryDocumentId
     * @return LibraryDocumentInfo
     */
    public function getInfo( $libraryDocumentId )
    {
        $this->endPoint = $libraryDocumentId;

        $this->headers = [
            'Access-Token' => $this->getAccessToken()
        ];

        $this->data = [];

        $request  = $this->getTransport();
        $response = $request->get($this);

        if( $response instanceof Error ) {
            return $response;
        }

        return new LibraryDocumentInfo( $response );
    }

    /**
     * @param $libraryDocumentId
     * @return Documents
     */
    public function documents( $libraryDocumentId )
    {
        $this->endPoint = $libraryDocumentId . '/documents' ;

        $this->headers = [
            'Access-Token' => $this->getAccessToken()
        ];

        $this->data = [];

        $request  = $this->getTransport();
        $response = $request->get($this);

        if( $response instanceof Error ) {
            return $response;
        }

        return new Documents( $response, $this, $libraryDocumentId );
    }

    /**
     * @param $libraryDocumentId
     * @param $documentId
     * @return bool|string
     * @throws \RuntimeException
     */
    public function document( $libraryDocumentId, $documentId )
    {
        $this->endPoint = $libraryDocumentId . '/documents/' .$documentId;

        $savePath = sys_get_temp_dir();

        if( ! is_writable( $savePath ) ) {
            throw new \RuntimeException("$savePath is not writeable by server.");
        }

        $fileName = $savePath . DIRECTORY_SEPARATOR . substr( $documentId, 0, 16);

        $this->headers = [
            'Access-Token' => $this->token->getAccessToken(),
            'libraryDocumentId'  => $libraryDocumentId,
            'documentId'   => $documentId,
            'save_to'      => $fileName,
        ];

        $this->data = [];
        $request  = $this->getTransport();
        $response = $request->get($this);

        if( $response instanceof Error ) {
            return $response;
        }

        if( ! is_readable( $fileName ) ) {
            return false;
        }

        return $fileName;
    }

    /**
     * @param $libraryDocumentId
     * @return bool|string
     * @throws \RuntimeException
     */
    public function auditTrail( $libraryDocumentId )
    {
        $this->endPoint = $libraryDocumentId . '/auditTrail' ;

        $savePath = sys_get_temp_dir();

        if( ! is_writable( $savePath ) ) {
            throw new \RuntimeException("$savePath is not writeable by server.");
        }

        $fileName = $savePath . DIRECTORY_SEPARATOR . substr( $libraryDocumentId, 0, 16). '.pdf';

        $this->headers = [
            'Access-Token' => $this->token->getAccessToken(),
            'libraryDocumentId'  => $libraryDocumentId,
            'save_to'      => $fileName,
        ];

        $this->data = [];
        $request  = $this->getTransport();
        $response = $request->get($this);

        if( $response instanceof Error ) {
            return $response;
        }

        if( ! is_readable( $fileName ) ) {
            return false;
        }

        return $fileName;
    }

    /**
     * @param $libraryDocumentId
     * @param bool $auditReport
     * @return bool|string
     * @throws \RuntimeException
     */
    public function combinedDocument( $libraryDocumentId, $auditReport = false )
    {
        $query = [
            'auditReport'               => $auditReport,
        ];

        $this->endPoint = $libraryDocumentId .'/combinedDocument?'.http_build_query($query) ;

        $savePath = sys_get_temp_dir();

        if( ! is_writable( $savePath ) ) {
            throw new \RuntimeException("$savePath is not writeable by server.");
        }

        $fileName = $savePath . DIRECTORY_SEPARATOR . substr( $libraryDocumentId, 0, 16). '.pdf';

        $this->headers = [
            'Access-Token' => $this->token->getAccessToken(),
            'libraryDocumentId'  => $libraryDocumentId,
            'save_to'      => $fileName,
        ];

        $this->data = [];
        $request  = $this->getTransport();
        $response = $request->get($this);

        if( $response instanceof Error ) {
            return $response;
        }

        if( ! is_readable( $fileName ) ) {
            return false;
        }

        return $fileName;
    }


    /**
     * @return array
     */
    public function toArray()
    {
        return $this->data;
    }

    /**
     * @return string
     */
    public function toJson()
    {
        return json_encode( $this->toArray() );
    }

    /**
     * @return string
     */
    public function getEndPoint()
    {
        return self::END_POINT . '/ ' . $this->endPoint;
    }

    /**
     * @param TransportInterface $transport
     * @return $this
     */
    public function setTransport( TransportInterface $transport )
    {
        $this->transport = $transport;
        return $this;
    }

    /**
     * @return TransportInterface
     */
    public function getTransport()
    {
        if( isset($this->transport) ) {
            return $this->transport;
        }

        // create a default transport just in case.
        $this->transport = new Guzzle();
    }

    /**
     * @return string
     */
    public function getAccessToken()
    {
        return $this->token->getAccessToken();
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->toJson();
    }


}