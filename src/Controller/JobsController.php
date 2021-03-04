<?php

namespace App\Controller;

use App\Repository\JobsRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class JobsController extends ApiController
{
    /**
     * @Route("/jobs", methods="GET")
     * @param JobsRepository $jobsRepository
     * @return JsonResponse
     */
    public function index(JobsRepository $jobsRepository): JsonResponse
    {
        $jobs = $jobsRepository->findByActive();
        return $this->respond($jobs);
    }

    /**
     * Get job for specified ID
     *
     * @Route("/jobs/{id}", methods="GET")
     *
     * @param $id
     * @param JobsRepository $jobsRepository
     * @return JsonResponse
     */
    public function getJob($id, JobsRepository $jobsRepository): JsonResponse
    {
        $job = $jobsRepository->find($id);

        if (!$job)
        {
            return $this->respondNotFound();
        }

        return $this->respond([
            'title' => $job->getTitle(),
            'slug' => $job->getSlug(),
            'is_active' => $job->getIsActive(),
            'content' => $job->getContent(),
            'location' => $job->getLocation()
        ]);

    }

}
