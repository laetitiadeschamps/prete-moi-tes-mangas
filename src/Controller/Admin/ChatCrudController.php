<?php

namespace App\Controller\Admin;

use App\Entity\Chat;
use App\Entity\Message;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;

class ChatCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Chat::class;
    }


}
