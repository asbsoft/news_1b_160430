<?php

namespace asb\yii2\modules\news_1b_160430\controllers\rest;

use yii\rest\Serializer as RestSerializer;

class Serializer extends RestSerializer
{
    /**
     * @inheritdoc
     */
    protected function getRequestedFields()
    {//echo __METHOD__;
        list ($fields, $expand) = parent::getRequestedFields();
        $expand[] = 'title';//var_dump($expand);
        return [$fields, $expand];
    }

}
