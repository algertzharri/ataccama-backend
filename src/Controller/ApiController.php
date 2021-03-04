<?php


namespace App\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ApiController
{
    protected $statusCode = 200;

    /**
     * Get the value of statusCode
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Sets the value of statusCode.
     *
     * @param $statusCode
     * @return self
     */
    protected function setStatusCode($statusCode): self
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    /**
     * Returns a JSON response
     *
     * @param $data
     * @return JsonResponse
     */
    public function respond($data): JsonResponse
    {
        return new JsonResponse($data, $this->getStatusCode());
    }

    /**
     * Sets an error message and returns a JSON response
     *
     * @param $errors
     * @return JsonResponse
     */
    public function respondWithErrors($errors): JsonResponse
    {
        $data = [
            'errors' => $errors,
        ];

        return new JsonResponse($data, $this->getStatusCode());
    }

    /**
     * Returns a 422 Unprocessable Entity
     *
     * @param string $message
     * @return JsonResponse
     */
    public function respondValidationError($message = 'Validate errors'): JsonResponse
    {
        return $this->setStatusCode(422)->respondWithErrors($message);
    }

    /**
     * Returns a 404 Not Found
     * @param string $message
     * @return JsonResponse
     */
    public function respondNotFound($message = 'Not found!'): JsonResponse
    {
        return $this->setStatusCode(404)->respondWithErrors($message);
    }

    /**
     * Returns a 201 created
     *
     * @param array $data
     * @return JsonResponse
     */
    public function respondCreated($data = []): JsonResponse
    {
        return $this->setStatusCode(201)->respond($data);
    }

    /**
     * Accept Json payloads in POST requests
     *
     * @param Request $request
     * @return Request|null
     */
    protected function transformJsonBody(Request  $request): ?Request
    {
        $data = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE)
        {
            return null;
        }

        if ($data === null)
        {
            return $request;
        }

        $request->request->replace($data);

        return $request;
    }
}
