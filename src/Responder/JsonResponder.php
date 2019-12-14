<?php

namespace App\Responder;

use Exception;
use App\Responder\Links;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;

class JsonResponder
{

    private $serializer;
    private $links;

    public function __construct(
        SerializerInterface $serializer,
        Links $links
    )
    {
        $this->serializer = $serializer;
        $this->links = $links;
    }

    /**
     * @Route("/detailsPerson/{id}", methods={"GET"})
     */
    public function send(Request $request, $datas, Int $status = 200, Array $headers = [])
    {
        if(!is_array($datas) && !is_object($datas)) {
            throw new Exception("\$datas is not an Array or an Object");
        }
    
        $this->links->addLinks($datas);
        $datasJson = $this->serializer->serialize($datas, 'json');

        $response = new JsonResponse($datasJson, $status, $headers, true);
        $response->setEncodingOptions(JSON_UNESCAPED_SLASHES);

        if($request->isMethodCacheable()) {
            $response->setCache([
                'etag' => md5($datasJson),
                'public' => true,
                
            ]);
            if($response->isNotModified($request)){
                $response->headers->addCacheControlDirective('Etag','valid');
                return $response;
            }
            $response->headers->addCacheControlDirective('Etag','invalid');
        }

        return $response;
    }
}