<?php

namespace App\UI\Pet;

use App\Model\AttributeManager;
use App\Model\PetManager;
use JetBrains\PhpStorm\NoReturn;
use Nette\Application\Responses\JsonResponse;
use Nette\Application\UI\Form;
use Exception;
use Nette;
use Nette\Application\Attributes\Requires;
use App\Model\PetFactory;

#[Requires(methods: ['GET', 'POST', 'PUT', 'DELETE'])]
final class PetPresenter extends Nette\Application\UI\Presenter
{
//    private PetManager $petManager;
//    private AttributeManager $attributeManager;
    private PetFactory $petFactory;

    public function __construct(/*PetManager $petManager, AttributeManager $attributeManager,*/ PetFactory $petFactory)
    {
        parent::__construct();
//        $this->petManager = $petManager;
//        $this->attributeManager = $attributeManager;
        $this->petFactory = $petFactory;
    }

//    public function renderDefault(): void
//    {
//        $this->template->attributes = $this->attributeManager->getAllAttributes();
//        $this->template->pets = $this->petManager->getAllPets();
//    }

    /**
     * DELETE /api/pet/delete/<id>
     * @param int $id
     * @return void
     */
    public function actionDelete(int $id): void
    {
        try {
            $this->petFactory->delete($id);

            $this->sendJson([
                'success' => true,
                'message' => 'Pet was successfully deleted.',
                'data' => []
            ]);
        } catch (Exception $e) {
            bdump($e);
            $this->sendJson([
                'success' => false,
                'message' => 'An error occurred while deleting the pet: ' . $e->getMessage(),
            ]);
        }
    }

    public function actionFind(?int $id): void
    {
        if ($id) {
            try {
                $pet = $this->petFactory->find($id);

                if ($pet) {
                    $response = new JsonResponse(
                        [
                            'success' => true,
                            'message' => 'Pet was successfully found.',
                            'data' => $pet->toArray()
                        ]
                    );
                } else {
                    $response = new JsonResponse(
                        [
                            'success' => true,
                            'message' => 'No pet was found.',
                            'data' => []
                        ]
                    );
                }
            } catch (Exception $e) {
                bdump($e);
                $response = new JsonResponse(
                    [
                        'success' => false,
                        'message' => 'An error occurred while finding the pet: ' . $e->getMessage(),
                    ]
                );
            }
        } else {
            try {
                $pets = $this->petFactory->getAll();

                if (!empty($pets)) {
                    $response = new JsonResponse(
                        [
                            'success' => true,
                            'message' => 'Pets were successfully retrieved.',
                            'data' => $pets
                        ]
                    );
                } else {
                    $response = new JsonResponse(
                        [
                            'success' => true,
                            'message' => 'No pets were found.',
                            'data' => []
                        ]
                    );
                }
            } catch (Exception $e) {
                bdump($e);
                $response = new JsonResponse(
                    [
                        'success' => false,
                        'message' => 'An error occurred while finding the pet: ' . $e->getMessage(),
                    ]
                );
            }
        }

        $this->sendResponse($response);
    }

    /**
     * POST /api/pet/create
     * @throws Exception
     */
    public function actionCreate(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if ($data === null) {
            $response = new JsonResponse(
                [
                    'success' => false,
                    'message' => 'Invalid JSON input.',
                ]
            );
            $this->sendResponse($response);
        }

        try {
            $pet = $this->petFactory->create($data);

            $response = new JsonResponse(
                [
                    'success' => true,
                    'message' => 'Pet was successfully created.',
                    'data' => $pet->toArray(),
                ]
            );
        } catch (Exception $e) {
            bdump($e);
            $response = new JsonResponse(
                [
                    'success' => false,
                    'message' => 'An error occurred while creating the pet: ' . $e->getMessage(),
                ]
            );
        }
        $this->sendResponse($response);
    }

    public function actionUpdate(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if ($data === null) {
            $response = new JsonResponse(
                [
                    'success' => false,
                    'message' => 'Invalid JSON input.',
                ]
            );
            $this->sendResponse($response);
        }

        try {
            $petId = $data['id'] ?? null;

            if (!$petId) {
                $response = new JsonResponse(
                    [
                        'success' => false,
                        'message' => 'Invalid JSON input.',
                    ]
                );
                $this->sendResponse($response);
            }

            $pet = $this->petFactory->update($petId, $data);

            $response = new JsonResponse(
                [
                    'success' => true,
                    'message' => 'Pet was successfully updated.',
                    'data' => $pet,
                ]
            );
        } catch (Exception $e) {
            bdump($e);
            $response = new JsonResponse(
                [
                    'success' => false,
                    'message' => 'An error occurred while updating the pet: ' . $e->getMessage(),
                ]
            );
        }
        $this->sendResponse($response);
    }
}
