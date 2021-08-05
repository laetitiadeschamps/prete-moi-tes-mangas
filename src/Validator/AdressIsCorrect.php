<?php 

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class AdressIsCorrect extends Constraint
{
    public $message = 'L\'adresse n\'est pas valide';
}