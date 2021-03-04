<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Repository\JobsRepository;
use App\Services\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Hshn\Base64EncodedFile\HttpFoundation\File\Base64EncodedFile;
use Hshn\Base64EncodedFile\HttpFoundation\File\UploadedBase64EncodedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends ApiController
{
    /**
     * Create a new contact
     *
     * @Route("/job/{id}/contact", methods="POST")
     * @param $id
     * @param Request $requests
     * @param FileUploader $fileUploader
     * @param JobsRepository $jobRepository
     * @param ValidatorInterface $validator
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    public function create($id, Request $requests, FileUploader $fileUploader, JobsRepository $jobRepository, ValidatorInterface $validator, EntityManagerInterface $em): JsonResponse
    {
        $request = $this->getRequest($requests);

        $job = $jobRepository->find($id);

        if (!$job)
        {
            return $this->respondNotFound("Job with id: ". $id ." wasn't found");
        }

        $file = $request->files->get('file');
        $base64File = $request->request->get('file');

        if (($file && count($file) > 5) || ($base64File && count($base64File) > 5)) {
            return $this->respondWithErrors("You can upload max 5 files!");
        }

        try {
            $contact = new Contact();

            $contact->setJobs($job);
            $contact->setFirstName($request->get('first_name'));
            $contact->setLastName($request->get('last_name'));
            $contact->setEmail($request->get('email'));
            $contact->setPhoneNumber($request->get('phone_number'));
            $contact->setLinkedin($request->get('linkedin'));
            $contact->setWhyYou($request->get('why_you'));
            $contact->setLocation($request->get('location'));

            $contact->setFile($this->uploadFiles($file, $base64File, $fileUploader));

            $em->persist($contact);
            $em->flush();

            return $this->respondCreated("success");
        }
        catch (\Exception $e)
        {
            return $this->respondWithErrors($e->getMessage());
        }

    }

    /**
     * @param Request $requests
     * @return Request|null
     */
    public function getRequest(Request $requests): ?Request
    {
        $request = $this->transformJsonBody($requests);

        if (!$request)
        {
            $request = $requests;
        }

        return $request;
    }

    /**
     * @param $file
     * @param $base64File
     * @param FileUploader $fileUploader
     * @return string|null
     */
    public function uploadFiles($file, $base64File, FileUploader $fileUploader): ?string
    {
        if ($file)
        {
            $fileNames = array();
            foreach($file as $f)
            {
                $fileNames[] = $fileUploader->fileUpload($f);
            }
            if ($fileNames != null || $fileNames != "none") {
                return implode(',',$fileNames);
            }
        }
        if ($base64File)
        {
            $fileNames = array();
            foreach($base64File as $f)
            {
                $fileValue = $f['value'];
                $fileName = $f['filename'];
                $fileType = $f['filetype'];
                $fileUploaded = new UploadedBase64EncodedFile(new Base64EncodedFile($fileValue));

                $fileNames[] = $fileUploader->base64FileUpload($fileUploaded, $fileName, $fileType);

            }
            return implode(',', $fileNames);

        }
        return null;
    }
}
