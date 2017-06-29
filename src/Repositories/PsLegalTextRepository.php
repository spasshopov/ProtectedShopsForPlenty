<?php

namespace ProtectedShops\Repositories;

use Plenty\Exceptions\ValidationException;
use Plenty\Modules\Plugin\DataBase\Contracts\DataBase;
use ProtectedShops\Contracts\PsLegalTextContract;
use ProtectedShops\Models\PsLegalText;
use ProtectedShops\Validators\PsLegalTextValidator;
use Plenty\Modules\Frontend\Services\AccountService;

class PsLegalTextRepository implements PsLegalTextContract
{
    /**
     * @var AccountService
     */
    private $accountService;

    /**
     * UserSession constructor.
     * @param AccountService $accountService
     */
    public function __construct(AccountService $accountService)
    {
        $this->accountService = $accountService;
    }

    public function createPsLegalText(array $data): PsLegalText
    {
        $database = pluginApp(DataBase::class);

        /**
         * @var PsLegalText $psLegalText
         */
        $psLegalText = pluginApp(PsLegalText::class);
        $psLegalText->legalText = $data['legalText'];
        $psLegalText->success = $data['success'];
        $psLegalText->updated = time();

        $database->save($psLegalText);

        return $psLegalText;
    }

    /**
     * List all items of the To Do list
     *
     * @return ToDo[]
     */
    public function getPsLegalTexts(): array
    {
        $database = pluginApp(DataBase::class);

        $id = $this->getCurrentContactId();
        /**
         * @var PsLegalText[] $toDoList
         */
        return $database->query(PsLegalText::class)->get();
    }

    /**
     * Update the status of the item
     *
     * @param int $id.
     * @param bool $success
     * @return PsLegalText
     */
    public function updateTask($id, $success): PsLegalText
    {
        /**
         * @var DataBase $database
         */
        $database = pluginApp(DataBase::class);

        $psLegalText = $database->query(PsLegalText::class)
            ->where('id', '=', $id)
            ->get();

        $psLegalText = $psLegalText[0];
        $psLegalText->updated = time();
        $psLegalText->success = $success;
        $database->save($psLegalText);

        return $psLegalText;
    }
}