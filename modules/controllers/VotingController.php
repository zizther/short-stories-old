<?php

namespace modules\controllers;

use Craft;
use craft\web\Controller;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

class VotingController extends Controller
{
    // Public Methods
    // =========================================================================

    /**
     * @return Response
     * @throws ForbiddenHttpException
     */
    public function actionAdd(): Response
    {
        $fieldHandle = Craft::$app->getRequest()->getRequiredBodyParam('field');
        $id = Craft::$app->getRequest()->getRequiredBodyParam('id');

        $currentUser = Craft::$app->getUser()->getIdentity();

        if (!$currentUser) {
            throw new ForbiddenHttpException('User must be logged in to perform this action');
        }

        $settings = Craft::$app->getConfig()->getConfigFromFile('voting');

        if (!isset($settings['addActionFieldHandles'][$fieldHandle])) {
            throw new BadRequestHttpException('Invalid field parameter');
        }

        $value = array_merge([$id], $currentUser->{$fieldHandle}->ids());

        $currentUser->setFieldValue($fieldHandle, $value);

        if (!Craft::$app->getElements()->saveElement($currentUser)) {
            return $this->asJson(['success' => false]);
        }

        return $this->asJson(['success' => true]);
    }


    /**
     * @return Response
     * @throws ForbiddenHttpException
     */
    public function actionRemove(): Response
    {
        $fieldHandle = Craft::$app->getRequest()->getRequiredBodyParam('field');
        $id = Craft::$app->getRequest()->getRequiredBodyParam('id');

        $currentUser = Craft::$app->getUser()->getIdentity();

        if (!$currentUser) {
            throw new ForbiddenHttpException('User must be logged in to perform this action');
        }

        if (!isset($settings['removeActionFieldHandles'][$fieldHandle])) {
            throw new BadRequestHttpException('Invalid field parameter');
        }

        $value = $currentUser->{$fieldHandle}->ids();

        if (($key = array_search($id, $value, true)) !== false) {
            array_splice($value, $key, 1);
        } else {
            throw new BadRequestHttpException('No element related with the ID: '.$id);
        }

        $currentUser->setFieldValue($fieldHandle, $value);

        if (!Craft::$app->getElements()->saveElement($currentUser)) {
            return $this->asJson(['success' => false]);
        }

        return $this->asJson(['success' => true]);
    }
}
