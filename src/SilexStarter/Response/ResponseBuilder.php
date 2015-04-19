<?php

namespace SilexStarter\Response;

use Twig_Environment;
use Illuminate\Support\Contracts\ArrayableInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ResponseBuilder{

    protected $twig;

    public function __construct(Twig_Environment $twig){
        $this->twig = $twig;
    }

    /**
     * Return a new response from the application.
     *
     * @param  string  $content
     * @param  int     $status
     * @param  array   $headers
     * @return Symfony\Component\HttpFoundation\Response
     */
    public function make($content = '', $status = 200, array $headers = [])
    {
        return new Response($content, $status, $headers);
    }

    /**
     *
     *
     */
    public function view($template, array $data = [], $status = 200, array $headers = []){
        return new Response($this->twig->render($template.'.twig', $data), $status, $headers);
    }

    /**
     * Return a new JSON response from the application.
     *
     * @param  string|array  $data
     * @param  int    $status
     * @param  array  $headers
     * @param  int    $options
     * @return Symfony\Component\HttpFoundation\JsonResponse
     */
    public function json($data = [], $status = 200, array $headers = [], array $options = [])
    {
        if ($data instanceof ArrayableInterface)
        {
            $data = $data->toArray();
        }

        return new JsonResponse($data, $status, $headers, $options);
    }

    /**
     * Return a new JSONP response from the application.
     *
     * @param  string  $callback
     * @param  string|array  $data
     * @param  int    $status
     * @param  array  $headers
     * @param  int    $options
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function jsonp($callback, $data = [], $status = 200, array $headers = [], array $options = [])
    {
        return $this->json($data, $status, $headers, $options)->setCallback($callback);
    }

    /**
     * Return a new streamed response from the application.
     *
     * @param  \Closure  $callback
     * @param  int      $status
     * @param  array    $headers
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function stream($callback, $status = 200, array $headers = [])
    {
        return new StreamedResponse($callback, $status, $headers);
    }

    /**
     * Create a new file download response.
     *
     * @param  \SplFileInfo|string  $file
     * @param  string  $name
     * @param  array   $headers
     * @param  null|string  $disposition
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function file($file, $name = null, array $headers = [], $disposition = 'attachment')
    {
        $response = new BinaryFileResponse($file, 200, $headers, true, $disposition);

        if ( ! is_null($name))
        {
            return $response->setContentDisposition($disposition, $name, str_replace('%', '', Str::ascii($name)));
        }

        return $response;
    }

    /**
     * [redirect description]
     * @param  [type] $url    [description]
     * @param  [type] $status [description]
     * @return [type]         [description]
     */
    public function redirect($url, $status = 302){
        return new RedirectResponse($url, $status);
    }
}