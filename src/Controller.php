<?php 

declare(strict_types=1);

namespace Yeoldebasil\Biscuit;

class Controller
{
    public function __construct(
        public Request  $request,
        public Response $response = new Response
    ) {}

    /**
     * Отображает страницу 404
     */
    function notFound(): Response
    {
        $page = 'Not found';
        $this->response->body = $page;

        return $this->response;
    }

    protected function redirect(string $url): Response
    {
        $this->response->header('Location', $url);
        return $this->response;
    }

    protected function stream(string $body): Response
    {
        $this->response->body = $body;
        return $this->response;
    }
}
