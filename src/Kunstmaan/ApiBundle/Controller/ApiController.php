<?php

namespace Kunstmaan\ApiBundle\Controller;

use Youshido\GraphQLBundle\Controller\GraphQLController;

final class ApiController extends GraphQLController
{
    public function apiAction()
    {
        $this->get('graphql.schema');
        return $this->defaultAction();
    }


}